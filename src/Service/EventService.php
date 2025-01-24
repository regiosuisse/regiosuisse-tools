<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Inbox;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\CommunitySubmission;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;

class EventService {

    protected $em;
    private $mailer;
    private $mailerFrom;
    private $notificationService;

    public function __construct(
        EntityManagerInterface $em,
        MailerInterface $mailer,
        string $mailerFrom,
        NotificationService $notificationService
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->mailerFrom = $mailerFrom;
        $this->notificationService = $notificationService;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validateEventPayload($payload)
    {
        // If payload is wrapped in a 'payload' key, extract it
        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'isPromotedDE',
            'isPromotedFR',
            'isPromotedIT',
            'title',
            'description',
            'organizer',
            'location',
            'contact',
            'text',
            'type',
            'color',
            'startDate',
            'endDate',
            'topics',
            'languages',
            'locations',
            'programs',
            'registration',
            'links',
            'videos',
            'images',
            'files',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createEvent($payload)
    {
        error_log('Creating event from payload: ' . json_encode($payload));

        // Extract the payload if it's wrapped in a 'payload' key
        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        // Validate the extracted payload
        $validationResult = $this->validateEventPayload($payload);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $event = new Event();
        $event->setCreatedAt(new \DateTime());
        
        $event = $this->applyEventPayload($payload, $event);
        $this->em->persist($event);
        $this->em->flush();

        // If this event was created from an inbox item that came from a community submission
        if (isset($payload['inboxId'])) {
            error_log('Event created from inbox item - inbox_id: ' . $payload['inboxId'] . ', event_id: ' . $event->getId());

            // Find the inbox item
            $inboxItem = $this->em->getRepository(Inbox::class)->find($payload['inboxId']);
            if ($inboxItem) {
                // Mark the inbox item as merged
                $inboxItem->setIsMerged(true);
                $inboxItem->setMergedAt(new \DateTime());
                $this->em->persist($inboxItem);
                $this->em->flush();
            }

            // Find and update the community submission if it exists
            $submissions = $this->em->getRepository(CommunitySubmission::class)
                ->findBy(['type' => CommunitySubmission::TYPE_EVENT, 'isVerified' => true]);

            error_log('Found submissions for event in createEvent - count: ' . count($submissions));

            foreach ($submissions as $submission) {
                $data = $submission->getSubmissionData();
                error_log('Checking submission in createEvent - submission_id: ' . $submission->getId() . ', data: ' . json_encode($data) . ', inbox_id: ' . $payload['inboxId']);

                if (isset($data['related_id']) && (string)$data['related_id'] === (string)$payload['inboxId']) {
                    error_log('Found matching submission in createEvent, updating event_id - submission_id: ' . $submission->getId() . ', event_id: ' . $event->getId());

                    // Update the submission data with the new event ID
                    $data['event_id'] = $event->getId();
                    $submission->setSubmissionData($data);
                    $this->em->persist($submission);
                    $this->em->flush();
                    break;
                }
            }
        }

        // Send notification if event is created as public
        if ($event->getIsPublic()) {
            $this->sendPublicationNotification($event);
        }

        return $event;
    }

    public function updateEvent($event, $payload)
    {
        $event->setUpdatedAt(new \DateTime());
        $wasPublic = $event->getIsPublic();
        
        $event = $this->applyEventPayload($payload, $event);
        
        $this->em->persist($event);
        $this->em->flush();

        // If event was not public before but is now, send notification
        if (!$wasPublic && $event->getIsPublic()) {
            $submissions = $this->em->getRepository(CommunitySubmission::class)
                ->findBy(['type' => CommunitySubmission::TYPE_EVENT, 'isVerified' => true]);

            foreach ($submissions as $submission) {
                $data = $submission->getSubmissionData();
                if (isset($data['event_id']) && (string)$data['event_id'] === (string)$event->getId()) {
                    $this->sendPublicationNotification($event);
                    break;
                }
            }
        }

        return $event;
    }

    public function deleteEvent($event)
    {
        $this->em->remove($event);
        $this->em->flush();

        return $event;
    }

    public function applyEventPayload($payload, Event $event)
    {
        $event
            ->setIsPublic($payload['isPublic'])
            ->setIsPromotedDE($payload['isPromotedDE'])
            ->setIsPromotedFR($payload['isPromotedFR'])
            ->setIsPromotedIT($payload['isPromotedIT'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setText($payload['text'])
            ->setOrganizer($payload['organizer'])
            ->setLocation($payload['location'])
            ->setContact($payload['contact'])
            ->setType($payload['type'])
            ->setColor($payload['color'])
            ->setStartDate($payload['startDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null)
            ->setRegistration($payload['registration'])
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setPrograms($payload['programs'] ?: [])
            ->setTopics(new ArrayCollection())
            ->setLanguages(new ArrayCollection())
            ->setLocations(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        foreach($payload['topics'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Topic::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Topic::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addTopic($entity);
            }
        }

        foreach($payload['languages'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Language::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Language::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addLanguage($entity);
            }
        }

        foreach($payload['locations'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Location::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Location::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $event->addLocation($entity);
            }
        }

        return $event;
    }

    public function createEventInboxItemFromEmbed($payload)
    {
        // Create corresponding inbox item
        $inbox = new Inbox();
        
        // Prepare normalized data structure
        $normalizedData = [
            'title' => $payload['title'],
            'type' => $payload['type'],
            'color' => $payload['color'] ?? null,
            'location' => $payload['location'],
            'organizer' => $payload['organizer'],
            'description' => $payload['description'],
            'text' => $payload['text'],
            'registration' => $payload['registration'],
            'startDate' => $payload['startDate'],
            'endDate' => $payload['endDate'],
            'contact' => $payload['contact'],
            'topics' => $payload['topics'] ?? [],
            'languages' => $payload['languages'] ?? [],
            'locations' => $payload['locations'] ?? [],
            'links' => $payload['links'] ?? [],
            'files' => $payload['files'] ?? [],
            'translations' => [
                'fr' => [],
                'it' => []
            ]
        ];

        $inbox->setCreatedAt(new \DateTime())
            ->setSource('embed')
            ->setType('event')
            ->setTitle($payload['title'])
            ->setStatus('new')
            ->setIsMerged(false)
            ->setData([
                'event' => $normalizedData
            ])
            ->setNormalizedData($normalizedData);

        $this->em->persist($inbox);
        $this->em->flush();

        return [
            'inbox' => $inbox
        ];
    }

    private function sendPublicationNotification(Event $event): void
    {
        // Find the submission to get the contact email and language
        $submissions = $this->em->getRepository(CommunitySubmission::class)
            ->findBy(['type' => CommunitySubmission::TYPE_EVENT, 'isVerified' => true]);

        foreach ($submissions as $submission) {
            $data = $submission->getSubmissionData();
            if (isset($data['event_id']) && (string)$data['event_id'] === (string)$event->getId()) {
                $contactEmail = $data['contactInfo']['email'] ?? null;
                $language = $data['contactInfo']['languageCode'] ?? 'de';
                
                if ($contactEmail) {
                    $this->notificationService->sendPublicationNotification(
                        CommunitySubmission::TYPE_EVENT,
                        $event->getId(),
                        $event->getTitle(),
                        $contactEmail,
                        $language
                    );
                }
                break;
            }
        }
    }

}
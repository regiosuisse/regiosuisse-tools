<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Stint;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Inbox;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;
use App\Entity\CommunitySubmission;
use App\Service\NotificationService;

class JobService {

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

    public function validateJobPayload($payload)
    {
        // If payload is wrapped in a 'payload' key, extract it
        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'position',
            'name',
            'description',
            'employer',
            'location',
            'contact',
            'applicationDeadline',
            'locations',
            'links',
            'files',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createJob($payload)
    {
        // Extract the payload if it's wrapped in a 'payload' key
        if (isset($payload['payload'])) {
            $payload = $payload['payload'];
        }

        // Validate the extracted payload
        $validationResult = $this->validateJobPayload($payload);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $job = new Job();
        $job->setCreatedAt(new \DateTime());
        
        $job = $this->applyJobPayload($payload, $job);

        $this->em->persist($job);
        $this->em->flush();

        // If this job was created from an inbox item that came from a community submission
        if (isset($payload['inboxId'])) {
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
                ->findBy(['type' => CommunitySubmission::TYPE_JOB, 'isVerified' => true]);

            foreach ($submissions as $submission) {
                $data = $submission->getSubmissionData();
                if (isset($data['related_id']) && (string)$data['related_id'] === (string)$payload['inboxId']) {
                    // Update the submission data with the new job ID
                    $data['job_id'] = $job->getId();
                    $submission->setSubmissionData($data);
                    $this->em->persist($submission);
                    $this->em->flush();
                    break;
                }
            }
        }

        // Send notification if job is created as public
        if ($job->getIsPublic()) {
            $this->sendPublicationNotification($job);
        }

        return $job;
    }

    public function updateJob($job, $payload)
    {
        $job->setUpdatedAt(new \DateTime());
        $wasPublic = $job->getIsPublic();
        
        $job = $this->applyJobPayload($payload, $job);
        
        $this->em->persist($job);
        $this->em->flush();

        // If job was not public before but is now, send notification
        if (!$wasPublic && $job->getIsPublic()) {
            $submissions = $this->em->getRepository(CommunitySubmission::class)
                ->findBy(['type' => CommunitySubmission::TYPE_JOB, 'isVerified' => true]);

            foreach ($submissions as $submission) {
                $data = $submission->getSubmissionData();
                if (isset($data['job_id']) && (string)$data['job_id'] === (string)$job->getId()) {
                    $this->sendPublicationNotification($job);
                    break;
                }
            }
        }

        return $job;
    }

    public function deleteJob($job)
    {
        $this->em->remove($job);
        $this->em->flush();

        return $job;
    }

    public function applyJobPayload($payload, Job $job)
    {
        $job
            ->setIsPublic($payload['isPublic'])
            ->setPosition($payload['position'])
            ->setName($payload['name'])
            ->setDescription($payload['description'])
            ->setEmployer($payload['employer'])
            ->setLocation($payload['location'])
            ->setContact($payload['contact'])
            ->setApplicationDeadline($payload['applicationDeadline'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['applicationDeadline']))) : null)
            ->setLinks($payload['links'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setLocations(new ArrayCollection())
            ->setStints(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

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
                $job->addLocation($entity);
            }
        }

        foreach($payload['stints'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Stint::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Stint::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $job->addStint($entity);
            }
        }

        return $job;
    }

    public function createJobInboxItemFromEmbed($payload)
    {
        // Create corresponding inbox item
        $inbox = new Inbox();
        
        // Prepare normalized data structure
        $normalizedData = [
            'name' => $payload['title'],
            'description' => $payload['description'],
            'employer' => $payload['employer'],
            'locations' => $payload['locations'],
            'location' => $payload['location'],
            'contact' => $payload['contact'],
            'applicationDeadline' => $payload['applicationDeadline'],
            'stints' => $payload['stints'],
            'links' => $payload['links'] ?? [],
            'files' => $payload['files'] ?? [],
            'translations' => $payload['translations'] ?? []
        ];

        $inbox->setCreatedAt(new \DateTime())
            ->setSource('embed')
            ->setType('job')
            ->setTitle($payload['title'])
            ->setStatus('new')
            ->setIsMerged(false)
            ->setData([
                'job' => [
                    'name' => $payload['title'],
                    'description' => $payload['description'],
                    'employer' => $payload['employer'],
                    'locations' => $payload['locations'],
                    'location' => $payload['location'],
                    'contact' => $payload['contact'],
                    'applicationDeadline' => $payload['applicationDeadline'],
                    'stints' => $payload['stints'],
                    'links' => $payload['links'] ?? [],
                    'files' => $payload['files'] ?? [],
                    'translations' => $payload['translations'] ?? []
                ]
            ])
            ->setNormalizedData($normalizedData);

        $this->em->persist($inbox);
        $this->em->flush();

        return [
            'inbox' => $inbox
        ];
    }

    private function sendPublicationNotification(Job $job): void
    {
        // Find the submission to get the contact email and language
        $submissions = $this->em->getRepository(CommunitySubmission::class)
            ->findBy(['type' => CommunitySubmission::TYPE_JOB, 'isVerified' => true]);

        foreach ($submissions as $submission) {
            $data = $submission->getSubmissionData();
            if (isset($data['job_id']) && (string)$data['job_id'] === (string)$job->getId()) {
                $contactEmail = $data['contactInfo']['email'] ?? null;
                $language = $data['contactInfo']['languageCode'] ?? 'de';
                
                if ($contactEmail) {
                    $this->notificationService->sendPublicationNotification(
                        CommunitySubmission::TYPE_JOB,
                        $job->getId(),
                        $job->getName(),
                        $contactEmail,
                        $language
                    );
                }
                break;
            }
        }
    }

}
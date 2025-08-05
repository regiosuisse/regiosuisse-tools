<?php

namespace App\Service;

use App\Entity\Inbox;
use App\Entity\Publication;
use App\Entity\Topic;
use App\Entity\GeographicRegion;
use App\Entity\Language;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PublicationService {

    protected $em;
    protected $userService;

    public function __construct(EntityManagerInterface $em, UserService $userService)
    {
        $this->em = $em;
        $this->userService = $userService;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validatePublicationPayload($payload)
    {
        if (($errors = $this->validateFields($payload, [
                'isPublic',
                'title',
                'description',
                'keywords',
                'type',
                'url',
                'licenseType',
                'licenseName',
                'licenseUrl',
                'licenseAttribution',
                'rightsHolder',
                'authors',
                'organizations',
                'translations',
                'topics',
                'geographicRegions',
                'languages',
                'startDate',
                'endDate',
                'email',
                'files',
            ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createPublication($payload)
    {
        $publication = new Publication();

        $publication->setCreatedAt(new \DateTime());

        $publication = $this->applyPublicationPayload($payload, $publication);

        $this->em->persist($publication);
        $this->em->flush();

        return $publication;
    }

    public function updatePublication($publication, $payload)
    {
        $publication->setUpdatedAt(new \DateTime());

        $publication = $this->applyPublicationPayload($payload, $publication);

        $this->em->persist($publication);
        $this->em->flush();

        return $publication;
    }

    public function deletePublication($publication)
    {
        $this->em->remove($publication);
        $this->em->flush();

        return $publication;
    }

    public function applyPublicationPayload($payload, Publication $publication)
    {
        $publication
            ->setIsPublic($payload['isPublic'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setKeywords($payload['keywords'])
            ->setType($payload['type'])
            ->setUrl($payload['url'])
            ->setLicenseType($payload['licenseType'])
            ->setLicenseName($payload['licenseName'])
            ->setLicenseUrl($payload['licenseUrl'])
            ->setLicenseAttribution($payload['licenseAttribution'])
            ->setRightsHolder($payload['rightsHolder'])
            ->setAuthors($payload['authors'] ?: [])
            ->setOrganizations($payload['organizations'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setTranslations($payload['translations'] ?: [])
            ->setStartDate($payload['startDate'] ? new \DateTime($payload['startDate']) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime($payload['endDate']) : null)
            ->setTopics(new ArrayCollection())
            ->setGeographicRegions(new ArrayCollection())
            ->setLanguages(new ArrayCollection())
            ->setEmail($payload['email']);

        if($publication->getLicenseType() !== 'Other') {
            $publication
                ->setLicenseName(null)
                ->setLicenseUrl(null)
                ->setLicenseAttribution(null)
            ;
        }

        foreach ($payload['topics'] as $item) {
            $topic = null;
            if (array_key_exists('id', $item) && $item['id']) {
                $topic = $this->em->getRepository(Topic::class)->find($item['id']);
            }
            if ($topic) {
                $publication->addTopic($topic);
            }
        }

        foreach ($payload['geographicRegions'] as $item) {
            $region = null;
            if (array_key_exists('id', $item) && $item['id']) {
                $region = $this->em->getRepository(GeographicRegion::class)->find($item['id']);
            }
            if ($region) {
                $publication->addGeographicRegion($region);
            }
        }

        foreach ($payload['languages'] as $item) {
            $language = null;
            if (array_key_exists('id', $item) && $item['id']) {
                $language = $this->em->getRepository(Language::class)->find($item['id']);
            }
            if ($language) {
                $publication->addLanguage($language);
            }
        }

        return $publication;
    }

    public function createPublicationInboxItemFromEmbed($payload)
    {
        $inbox = new Inbox();

        $normalizedData = [
            'isPublic' => $payload['isPublic'],
            'title' => $payload['title'],
            'description' => $payload['description'],
            'keywords' => $payload['keywords'] ?? null,
            'type' => $payload['type'] ?? null,
            'url' => $payload['url'] ?? null,
            'licenseType' => $payload['licenseType'] ?? null,
            'licenseName' => $payload['licenseName'] ?? null,
            'licenseUrl' => $payload['licenseUrl'] ?? null,
            'licenseAttribution' => $payload['licenseAttribution'] ?? null,
            'rightsHolder' => $payload['rightsHolder'] ?? null,
            'authors' => $payload['authors'] ?? [],
            'organizations' => $payload['organizations'] ?? [],
            'files' => $payload['files'] ?? [],
            'translations' => $payload['translations'] ?? [],
            'topics' => $payload['topics'] ?? [],
            'geographicRegions' => $payload['geographicRegions'] ?? [],
            'languages' => $payload['languages'] ?? [],
            'startDate' => $payload['startDate'] ?? null,
            'endDate' => $payload['endDate'] ?? null,
            'email' => $payload['email'] ?? null,
        ];

        $inbox->setCreatedAt(new \DateTime())
            ->setSource('embed')
            ->setType('publication')
            ->setTitle($payload['title'] ?? '[No Title]')
            ->setStatus('new')
            ->setIsMerged(false)
            ->setData(['publication' => $normalizedData])
            ->setNormalizedData($normalizedData);

        $this->em->persist($inbox);
        $this->em->flush();

        $users = $this->em->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            if (!in_array('PUBLICATIONS_INBOX', $user->getNotifications() ?? [])) {
                continue;
            }

            $this->userService->sendNotification(
                $user,
                'Eine neue Publikation wurde über die Website eingereicht',
                sprintf('%s', $payload['title']),
                sprintf(
                    '<p>%s hat eine neue Publikation eingereicht.</p><p>Prüfen Sie den Posteingang um den Eintrag freizuschalten.</p><p>Kontaktdaten für Rückfragen: %s / %s</p>',
                    htmlspecialchars($payload['contactInfo']['name'] ?? ''),
                    htmlspecialchars($payload['contactInfo']['email'] ?? ''),
                    htmlspecialchars($payload['contactInfo']['phone'] ?? '')
                )
            );
        }

        return ['inbox' => $inbox];
    }

    public function createPublicationFromInboxItem($payload)
    {
        $publication = new Publication();

        $publication
            ->setCreatedAt(new \DateTime())
            ->setIsPublic(false)
            ->setTitle($payload['title'] ?? null)
            ->setDescription($payload['description'] ?? null)
            ->setKeywords($payload['keywords'] ?? null)
            ->setType($payload['type'] ?? null)
            ->setUrl($payload['url'] ?? null)
            ->setLicenseType($payload['licenseType'] ?? null)
            ->setLicenseName($payload['licenseName'] ?? null)
            ->setLicenseUrl($payload['licenseUrl'] ?? null)
            ->setLicenseAttribution($payload['licenseAttribution'] ?? null)
            ->setRightsHolder($payload['rightsHolder'] ?? null)
            ->setAuthors($payload['authors'] ?? [])
            ->setOrganizations($payload['organizations'] ?? [])
            ->setFiles($payload['files'] ?: [])
            ->setTranslations($payload['translations'] ?? [])
            ->setStartDate($payload['startDate'] ? new \DateTime($payload['startDate']) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime($payload['endDate']) : null);

        $this->em->persist($publication);
        $this->em->flush();

        return ['publication' => $publication];
    }

}
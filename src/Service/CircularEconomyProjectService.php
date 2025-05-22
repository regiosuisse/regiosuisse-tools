<?php

namespace App\Service;

use App\Entity\BusinessSector;
use App\Entity\Country;
use App\Entity\GeographicRegion;
use App\Entity\Instrument;
use App\Entity\Program;
use App\Entity\State;
use App\Entity\Tag;
use App\Entity\Topic;
use App\Util\PvTrans;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Inbox;
use App\Entity\CircularEconomyProject;
use Doctrine\ORM\EntityManagerInterface;

class CircularEconomyProjectService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    public function validateCircularEconomyProjectPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'title',
            'type',
            'isPublic',
            'keywords',
            'description',
            'topics',
            'geographicRegions',
            'countries',
            'states',
            'instruments',
            'businessSectors',
            'startDate',
            'endDate',
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

    public function createCircularEconomyProject($payload)
    {
        $project = new CircularEconomyProject();

        $project->setCreatedAt(new \DateTime());

        $project = $this->applyCircularEconomyProjectPayload($payload, $project);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function updateCircularEconomyProject($project, $payload)
    {
        $project->setUpdatedAt(new \DateTime());

        $project = $this->applyCircularEconomyProjectPayload($payload, $project);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function deleteCircularEconomyProject($project)
    {
        $this->em->remove($project);
        $this->em->flush();

        return $project;
    }

    public function applyCircularEconomyProjectPayload($payload, CircularEconomyProject $project)
    {
        $project
            ->setIsPublic($payload['isPublic'])
            ->setTitle($payload['title'])
            ->setType($payload['type'])
            ->setKeywords($payload['keywords'])
            ->setDescription($payload['description'])
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setStartDate($payload['startDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['startDate']))) : null)
            ->setEndDate($payload['endDate'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['endDate']))) : null)
            ->setCountries(new ArrayCollection())
            ->setStates(new ArrayCollection())
            ->setGeographicRegions(new ArrayCollection())
            ->setTopics(new ArrayCollection())
            ->setInstruments(new ArrayCollection())
            ->setBusinessSectors(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        if($payload['type'] === 'project') {
            $payload['tags'] = [];
        }

        if($payload['type'] === 'exemplary') {
            $payload['topics'] = [];
            $payload['geographicRegions'] = [];
            $payload['instruments'] = [];
            $payload['businessSectors'] = [];
        }

        foreach($payload['countries'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Country::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Country::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addCountry($entity);
            }
        }

        foreach($payload['states'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(State::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(State::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addState($entity);
            }
        }

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
                $project->addTopic($entity);
            }
        }

        foreach($payload['geographicRegions'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(GeographicRegion::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(GeographicRegion::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addGeographicRegion($entity);
            }
        }

        foreach($payload['instruments'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Instrument::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Instrument::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addInstrument($entity);
            }
        }

        foreach($payload['businessSectors'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(BusinessSector::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(BusinessSector::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addBusinessSector($entity);
            }
        }

        foreach($payload['tags'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Tag::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Tag::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $project->addTag($entity);
            }
        }

        return $project;
    }

}
<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * CircularEconomyProject
 */
#[ORM\Table(name: 'pv_circular_economy_project')]
#[ORM\Entity(repositoryClass: 'App\Repository\CircularEconomyProjectRepository')]
class CircularEconomyProject
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'circular_economy_project'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['circular_economy_project'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['circular_economy_project'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $updatedAt;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $title;

    #[ORM\Column(name: 'type', type: 'string', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $type;

    #[ORM\Column(name: 'keywords', type: 'text', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $keywords;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $description;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Topic')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_topic')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'topic_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $topics;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'GeographicRegion')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_geographic_region')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'geographic_region_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $geographicRegions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Country')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_country')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'country_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $countries;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'State')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_state')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'state_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $states;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Instrument')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_instrument')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'instrument_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $instruments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'BusinessSector')]
    #[ORM\JoinTable(name: 'pv_circular_economy_project_business_sector')]
    #[ORM\JoinColumn(name: 'circular_economy_project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'business_sector_id', referencedColumnName: 'id')]
    #[Groups(['circular_economy_project'])]
    private $businessSectors;

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $startDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    #[Groups(['circular_economy_project'])]
    private $endDate;

    #[ORM\Column(name: 'links', type: 'json')]
    #[Groups(['circular_economy_project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'url', type: 'string'),
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $links = [];

    #[ORM\Column(name: 'videos', type: 'json')]
    #[Groups(['circular_economy_project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'url', type: 'string'),
            new OA\Property(property: 'value', type: 'string'),
            new OA\Property(property: 'label', type: 'string'),
        ],
        type: 'object'
    ))]
    private $videos = [];

    #[ORM\Column(name: 'images', type: 'json')]
    #[Groups(['circular_economy_project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'extension', type: 'string'),
            new OA\Property(property: 'mimeType', type: 'string'),
            new OA\Property(property: 'copyright', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
        ],
        type: 'object'
    ))]
    private $images = [];

    #[ORM\Column(name: 'files', type: 'json')]
    #[Groups(['circular_economy_project'])]
    #[OA\Property(type: 'array', items: new OA\Items(
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'extension', type: 'string'),
            new OA\Property(property: 'mimeType', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
        ],
        type: 'object'
    ))]
    private $files = [];

    #[ORM\Column(name: 'translations', type: 'json')]
    #[Groups(['circular_economy_project'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->geographicRegions = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->instruments = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return CircularEconomyProject
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return CircularEconomyProject
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return CircularEconomyProject
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return CircularEconomyProject
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CircularEconomyProject
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return CircularEconomyProject
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return CircularEconomyProject
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get topics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTopics() {
        return $this->topics;
    }

    /**
     * Set topics
     *
     * @return $this
     */
    public function setTopics($topics) {
        $this->topics = $topics;

        return $this;
    }

    /**
     * Add to topics
     *
     * @param $topic
     * @return CircularEconomyProject
     */
    public function addTopic($topic) {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
        }

        return $this;
    }

    /**
     * Remove topics
     *
     * @param $topics
     */
    public function removeTopic($topics) {
        $this->topics->removeElement($topics);
    }

    /**
     * Get geographicRegions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGeographicRegions() {
        return $this->geographicRegions;
    }

    /**
     * Set geographicRegions
     *
     * @return $this
     */
    public function setGeographicRegions($geographicRegions) {
        $this->geographicRegions = $geographicRegions;

        return $this;
    }

    /**
     * Add to geographicRegions
     *
     * @param $geographicRegion
     * @return CircularEconomyProject
     */
    public function addGeographicRegion($geographicRegion) {
        if (!$this->geographicRegions->contains($geographicRegion)) {
            $this->geographicRegions->add($geographicRegion);
        }

        return $this;
    }

    /**
     * Remove geographicRegion
     *
     * @param $geographicRegion
     */
    public function removeGeographicRegion($geographicRegion) {
        $this->geographicRegions->removeElement($geographicRegion);
    }

    /**
     * Get countries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCountries() {
        return $this->countries;
    }

    /**
     * Set countries
     *
     * @return $this
     */
    public function setCountries($countries) {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Add to countries
     *
     * @param $country
     * @return CircularEconomyProject
     */
    public function addCountry($country) {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * Remove countries
     *
     * @param $countries
     */
    public function removeCountry($countries) {
        $this->countries->removeElement($countries);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates() {
        return $this->states;
    }

    /**
     * Set states
     *
     * @return $this
     */
    public function setStates($states) {
        $this->states = $states;

        return $this;
    }

    /**
     * Add to states
     *
     * @param $state
     * @return CircularEconomyProject
     */
    public function addState($state) {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
        }

        return $this;
    }

    /**
     * Remove state
     *
     * @param $state
     */
    public function removeState($state) {
        $this->states->removeElement($state);
    }

    /**
     * Get instruments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInstruments() {
        return $this->instruments;
    }

    /**
     * Set instruments
     *
     * @return $this
     */
    public function setInstruments($instruments) {
        $this->instruments = $instruments;

        return $this;
    }

    /**
     * Add to instruments
     *
     * @param $instrument
     * @return CircularEconomyProject
     */
    public function addInstrument($instrument) {
        if (!$this->instruments->contains($instrument)) {
            $this->instruments->add($instrument);
        }

        return $this;
    }

    /**
     * Remove instrument
     *
     * @param $instrument
     */
    public function removeInstrument($instrument) {
        $this->instruments->removeElement($instrument);
    }

    /**
     * Get businessSectors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusinessSectors() {
        return $this->businessSectors;
    }

    /**
     * Set businessSectors
     *
     * @return $this
     */
    public function setBusinessSectors($businessSectors) {
        $this->businessSectors = $businessSectors;

        return $this;
    }

    /**
     * Add to businessSectors
     *
     * @param $businessSector
     * @return CircularEconomyProject
     */
    public function addBusinessSector($businessSector) {
        if (!$this->businessSectors->contains($businessSector)) {
            $this->businessSectors->add($businessSector);
        }

        return $this;
    }

    /**
     * Remove businessSector
     *
     * @param $businessSector
     */
    public function removeBusinessSector($businessSector) {
        $this->businessSectors->removeElement($businessSector);
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return CircularEconomyProject
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return CircularEconomyProject
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return CircularEconomyProject
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set videos
     *
     * @param array $videos
     *
     * @return CircularEconomyProject
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Get videos
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set images
     *
     * @param array $images
     *
     * @return CircularEconomyProject
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set files
     *
     * @param array $files
     *
     * @return CircularEconomyProject
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set translations
     *
     * @param array $translations
     *
     * @return CircularEconomyProject
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * Get translations
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}


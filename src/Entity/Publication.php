<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * Publication
 */
#[ORM\Table(name: 'pv_publication')]
#[ORM\Entity(repositoryClass: 'App\Repository\PublicationRepository')]
class Publication
{

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['id', 'publication'])]
    private $id;

    #[ORM\Column(name: 'is_public', type: 'boolean')]
    #[Groups(['publication'])]
    private $isPublic;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['publication'])]
    private $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['publication'])]
    private $updatedAt;

    #[ORM\Column(name: 'title', type: 'text', nullable: true)]
    #[Groups(['publication'])]
    private $title;

    #[ORM\Column(name: 'keywords', type: 'text', nullable: true)]
    #[Groups(['publication'])]
    private $keywords;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['publication'])]
    private $description;

    #[ORM\Column(name: 'email', type: 'text', nullable: true)]
    #[Groups(['publication_secure'])]
    private $email;

    #[ORM\Column(name: 'type', type: 'string', length: 128, nullable: true)]
    #[Groups(['publication'])]
    private $type;

    #[ORM\Column(name: 'url', type: 'text', nullable: true)]
    #[Groups(['publication'])]
    private $url;

    #[ORM\Column(name: 'license_type', type: 'string', length: 128, nullable: true)]
    #[Groups(['publication'])]
    private $licenseType;

    #[ORM\Column(name: 'license_name', type: 'string', length: 128, nullable: true)]
    #[Groups(['publication'])]
    private $licenseName;

    #[ORM\Column(name: 'license_url', type: 'string', length: 128, nullable: true)]
    #[Groups(['publication'])]
    private $licenseUrl;

    #[ORM\Column(name: 'license_attribution', type: 'text', nullable: true)]
    #[Groups(['publication'])]
    private $licenseAttribution;

    #[ORM\Column(name: 'rights_holder', type: 'string', length: 128, nullable: true)]
    #[Groups(['publication'])]
    private $rightsHolder;

    #[ORM\Column(name: 'authors', type: 'json')]
    #[Groups(['publication'])]
    #[OA\Property(type: 'array')]
    private $authors = [];

    #[ORM\Column(name: 'organizations', type: 'json')]
    #[Groups(['publication'])]
    #[OA\Property(type: 'array')]
    private $organizations = [];

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Topic')]
    #[ORM\JoinTable(name: 'pv_publication_topic')]
    #[ORM\JoinColumn(name: 'publication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'topic_id', referencedColumnName: 'id')]
    #[Groups(['publication'])]
    private $topics;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'GeographicRegion')]
    #[ORM\JoinTable(name: 'pv_publication_geographic_region')]
    #[ORM\JoinColumn(name: 'publication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'geographic_region_id', referencedColumnName: 'id')]
    #[Groups(['publication'])]
    private $geographicRegions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[ORM\ManyToMany(targetEntity: 'Language')]
    #[ORM\JoinTable(name: 'pv_publication_language')]
    #[ORM\JoinColumn(name: 'publication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'language_id', referencedColumnName: 'id')]
    #[Groups(['publication'])]
    private $languages;

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: true)]
    #[Groups(['publication'])]
    private $startDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    #[Groups(['publication'])]
    private $endDate;

    #[ORM\Column(name: 'files', type: 'json')]
    #[Groups(['publication'])]
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
    #[Groups(['publication'])]
    #[OA\Property(properties: [
        new OA\Property(property: 'fr', type: 'object'),
        new OA\Property(property: 'it', type: 'object'),
    ], type: 'object')]
    private $translations = [];

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->geographicRegions = new ArrayCollection();
        $this->languages = new ArrayCollection();
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
     * @return Publication
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
     * @return Publication
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
     * @return Publication
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
     * @return Publication
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
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Publication
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
     * @return Publication
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
     * Set email
     *
     * @param string|null $email
     *
     * @return Publication
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set type
     *
     * @param string|null $type
     *
     * @return Publication
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param string|null $url
     *
     * @return Publication
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set licenseType
     *
     * @param string|null $licenseType
     *
     * @return Publication
     */
    public function setLicenseType($licenseType)
    {
        $this->licenseType = $licenseType;

        return $this;
    }

    /**
     * Get licenseType
     *
     * @return string|null
     */
    public function getLicenseType()
    {
        return $this->licenseType;
    }

    /**
     * Set licenseName
     *
     * @param string|null $licenseName
     *
     * @return Publication
     */
    public function setLicenseName($licenseName)
    {
        $this->licenseName = $licenseName;

        return $this;
    }

    /**
     * Get licenseName
     *
     * @return string|null
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    /**
     * Set licenseUrl
     *
     * @param string|null $licenseUrl
     *
     * @return Publication
     */
    public function setLicenseUrl($licenseUrl)
    {
        $this->licenseUrl = $licenseUrl;

        return $this;
    }

    /**
     * Get licenseUrl
     *
     * @return string|null
     */
    public function getLicenseUrl()
    {
        return $this->licenseUrl;
    }

    /**
     * Set licenseAttribution
     *
     * @param string|null $licenseAttribution
     *
     * @return Publication
     */
    public function setLicenseAttribution($licenseAttribution)
    {
        $this->licenseAttribution = $licenseAttribution;

        return $this;
    }

    /**
     * Get licenseAttribution
     *
     * @return string|null
     */
    public function getLicenseAttribution()
    {
        return $this->licenseAttribution;
    }

    /**
     * Set rightsHolder
     *
     * @param string|null $rightsHolder
     *
     * @return Publication
     */
    public function setRightsHolder($rightsHolder)
    {
        $this->rightsHolder = $rightsHolder;

        return $this;
    }

    /**
     * Get rightsHolder
     *
     * @return string|null
     */
    public function getRightsHolder()
    {
        return $this->rightsHolder;
    }

    /**
     * Set authors
     *
     * @param array $authors
     *
     * @return Publication
     */
    public function setAuthors(array $authors)
    {
        $this->authors = $authors;

        return $this;
    }

    /**
     * Get authors
     *
     * @return array
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set organizations
     *
     * @param array $organizations
     *
     * @return Publication
     */
    public function setOrganizations(array $organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Get organizations
     *
     * @return array
     */
    public function getOrganizations()
    {
        return $this->organizations;
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
     * @return Publication
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
     * @return Publication
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
     * Get languages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Set languages
     *
     * @return Publication
     */
    public function setLanguages($languages) {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Add to languages
     *
     * @param $language
     * @return Publication
     */
    public function addLanguage($language) {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    /**
     * Remove languages
     *
     * @param $languages
     */
    public function removeLanguage($languages) {
        $this->languages->removeElement($languages);
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Publication
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
     * @return Publication
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
     * Set files
     *
     * @param array $files
     *
     * @return Event
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
     * @return Publication
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


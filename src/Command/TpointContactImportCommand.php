<?php

namespace App\Command;

use App\Entity\Contact;
use App\Entity\ContactGroup;
use App\Entity\Country;
use App\Entity\Employment;
use App\Entity\Language;
use App\Entity\State;
use App\Service\TpointService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:tpoint:contact-import')]
class TpointContactImportCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected TpointService $tpointService;

    public function __construct(ManagerRegistry $doctrine, TpointService $tpointService)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->tpointService = $tpointService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import tpoint data')
            ->addOption('contacts-json-dir', null, InputOption::VALUE_REQUIRED, 'Path to contacts.json directory')
            ->addOption('contact-groups-json-dir', null, InputOption::VALUE_REQUIRED, 'Path to contact-groups.json directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contactsData = [];
        $contactGroupsData = [];

        $locales = ['de', 'fr', 'it'];

        foreach($locales as $locale) {

            if(!isset($contactsData[$locale])) {
                $contactsData[$locale] = [];
            }

            foreach(glob($input->getOption('contacts-json-dir').'/'.$locale.'/*.json') as $file) {
                $contactsData[$locale] = array_merge($contactsData[$locale], json_decode(file_get_contents($file), true));
            }

            if(!isset($contactGroupsData[$locale])) {
                $contactGroupsData[$locale] = [];
            }

            foreach(glob($input->getOption('contact-groups-json-dir').'/'.$locale.'/*.json') as $file) {
                $contactGroupsData[$locale] = array_merge($contactGroupsData[$locale], json_decode(file_get_contents($file), true));
            }

        }

        $io->info(sprintf('Memory usage after file loading %s', (memory_get_usage() / 1024 / 1024) . 'MB'));

        $createdParentContactGroups = 0;
        $updatedParentContactGroups = 0;
        $deletedParentContactGroups = 0;

        $createdContactGroups = 0;
        $updatedContactGroups = 0;
        $deletedContactGroups = 0;

        $createdContacts = 0;
        $updatedContacts = 0;
        $deletedContacts = 0;

        $createdEmployments = 0;
        $updatedEmployments = 0;
        $deletedEmployments = 0;

        $parentGroupNames = [];
        $parentGroupNameTranslations = [];

        foreach($contactGroupsData[$locales[0]] as $index => $contactGroup) {

            $parentGroupName = $contactGroup['groupingTitle'] ?? null;

            if(!$parentGroupName) {
                break;
            }

            $parentGroupName = explode('_', $parentGroupName)[1];

            if(!in_array($parentGroupName, $parentGroupNames)) {
                $parentGroupNames[] = $parentGroupName;

                foreach(array_slice($locales, 1) as $locale) {
                    if(!isset($parentGroupNameTranslations[$locale])) {
                        $parentGroupNameTranslations[$locale] = [];
                    }

                    $parentGroupNameTranslations[$locale][] = explode('_', $contactGroupsData[$locale][$index]['groupingTitle'])[1];
                }
            }
        }

        foreach($parentGroupNames as $index => $parentGroup) {
            $position = $index;
            $name = $parentGroup;
            $parent = null;

            $contactGroup = $this->doctrine->getManager()->getRepository(ContactGroup::class)->findOneBy([
                'name' => $name,
                'parent' => null,
            ]);

            if(!$contactGroup) {
                $contactGroup = new ContactGroup();
                $contactGroup->setCreatedAt(new \DateTime());
            }

            $translations = [];

            foreach(array_slice($locales, 1) as $locale) {
                if(!isset($translations[$locale])) {
                    $translations[$locale] = [];
                }

                $translations[$locale]['name'] = $parentGroupNameTranslations[$locale][$index];
            }

            $contactGroup
                ->setUpdatedAt(new \DateTime())
                ->setPosition($position)
                ->setIsPublic(false)
                ->setName($name)
                ->setParent($parent)
                ->setTranslations($translations)
            ;

            if($contactGroup->getId()) {
                $updatedParentContactGroups++;
            } else {
                $createdParentContactGroups++;
            }

            $this->doctrine->getManager()->persist($contactGroup);
            $this->doctrine->getManager()->flush();
        }

        $io->success(sprintf('Successfully updated %s parent contact groups :)', $updatedParentContactGroups));
        $io->success(sprintf('Successfully created %s parent contact groups :)', $createdParentContactGroups));

        foreach($contactGroupsData[$locales[0]] as $index => $contactGroup) {
            $position = $index + count($parentGroupNames);
            $isPublic = false;
            $name = $contactGroup['label'] ?? false;
            $parent = null;

            $parentGroupName = $contactGroup['groupingTitle'] ?? null;

            if($parentGroupName) {
                $parentGroupName = explode('_', $parentGroupName)[1];

                $parent = $this->doctrine->getManager()->getRepository(ContactGroup::class)->findOneBy([
                    'name' => $parentGroupName,
                    'parent' => null,
                ]);
            }

            $qb = $this->doctrine->getManager()->createQueryBuilder();
            $qb
                ->select('c')
                ->from(ContactGroup::class, 'c')
                ->andWhere('c.name = :name')
                ->setParameter('name', $name)
                ->andWhere('c.parent != :parent')
                ->setParameter('parent', null)
            ;

            $contactGroup = $qb->getQuery()->getOneOrNullResult();

            if(!$contactGroup) {
                $contactGroup = new ContactGroup();
                $contactGroup->setCreatedAt(new \DateTime());
            }

            $translations = [];

            foreach(array_slice($locales, 1) as $locale) {
                if(!isset($translations[$locale])) {
                    $translations[$locale] = [];
                }

                $translations[$locale]['name'] = $contactGroupsData[$locale][$index]['label'];
            }

            $contactGroup
                ->setUpdatedAt(new \DateTime())
                ->setPosition($position)
                ->setIsPublic($isPublic)
                ->setName($name)
                ->setParent($parent)
                ->setTranslations($translations)
            ;

            if($contactGroup->getId()) {
                $updatedContactGroups++;
            } else {
                $createdContactGroups++;
            }

            $this->doctrine->getManager()->persist($contactGroup);
            $this->doctrine->getManager()->flush();
        }

        $io->info(sprintf('Memory usage after contact group import %s', (memory_get_usage() / 1024 / 1024) . 'MB'));

        $io->success(sprintf('Successfully updated %s contact groups :)', $updatedContactGroups));
        $io->success(sprintf('Successfully created %s contact groups :)', $createdContactGroups));

        foreach($contactsData[$locales[0]] as $index => $contact) {

            $uid = $contact['v_card_uid'];
            $isPublic = $contact['public'];
            $type = $contact['type'];
            $companyName = $contact['company_name'] ?? '';

            if($type === 'person' && count($contact['employment_collection']) > 0) {
                $companyName = $contact['employment_collection'][0]['company_name'];
            }

            $specification = $contact['company_specification'] ?? '';

            $gender = '';

            if(isset($contact['gender'])) {
                $gender = $contact['gender'] === 'm' ? 'male' : 'female';
            }

            $academicTitle = $contact['name_prefix'] ?? '';
            $firstName = $contact['forename'] ?? '';
            $lastName = $contact['surname'] ?? '';
            $street = $contact['address'] ?? '';
            $zipCode = $contact['zip_code'] ?? '';
            $city = $contact['city'] ?? '';
            $country = null;
            $state = null;
            $language = null;
            $phone = '';
            $email = '';
            $website = '';

            if(isset($contact['contact_information_collection'])) {
                foreach($contact['contact_information_collection'] as $info) {

                    if($info['information_group']['type'] === 'phone') {
                        $phone = $info['value'];
                    }

                    if($info['information_group']['type'] === 'email') {
                        $email = $info['value'];
                    }

                    if($info['information_group']['type'] === 'http') {
                        $website = $info['value'];
                    }

                    if($info['information_group']['type'] === 'https') {
                        $website = $info['value'];
                    }
                }
            }

            $description = $contact['priorities_of_research'] ?? '';

            $countryName = $contact['nation']['label'] ?? '';

            if($countryName) {

                $country = $this->doctrine->getManager()->getRepository(Country::class)->findOneBy([
                    'name' => $countryName,
                ]);

                if (!$country) {
                    $io->warning(sprintf('No country found for country label "%s"', $countryName));
                }
            }

            $stateName = $contact['province']['label'] ?? null;

            if($stateName && $countryName === 'Schweiz') {

                $state = $this->doctrine->getManager()->getRepository(State::class)->findOneBy([
                    'name' => $stateName,
                ]);

                if (!$state) {
                    $io->warning(sprintf('No state found for state label "%s"', $stateName));
                }
            }

            $languageName = $contact['language']['label'] ?? null;

            if($languageName) {

                $language = $this->doctrine->getManager()->getRepository(Language::class)->findOneBy([
                    'name' => $languageName,
                ]);

                if (!$language) {
                    $io->warning(sprintf('No language found for language label "%s"', $languageName));
                }
            }

            $contactGroups = [];

            foreach($contactGroupsData[$locales[0]] as $contactGroupData) {

                if(isset($contactGroupData['contactCollection'])) {

                    if(in_array($contact, $contactGroupData['contactCollection'])) {

                        $parent = $contactGroupData['groupingTitle'] ?? null;

                        if($parent) {
                            $parent = explode('_', $parent)[1];
                        }

                        $qb = $this->doctrine->getManager()->createQueryBuilder();
                        $qb
                            ->select('g')
                            ->from(ContactGroup::class, 'g')
                            ->andWhere('g.name = :name')
                            ->setParameter('name', $contactGroupData['label'])
                            ->leftJoin('g.parent', 'p')
                            ->andWhere('p.name = :parent')
                            ->setParameter('parent', $parent)
                        ;

                        $contactGroup = $qb->getQuery()->getOneOrNullResult();

                        if(!$contactGroup) {
                            continue;
                        }

                        $contactGroups[] = $contactGroup;
                    }
                }
            }

            $contact = $this->doctrine->getManager()->getRepository(Contact::class)->findOneBy([
                'tpointUid' => $uid,
            ]);

            if(!$contact) {
                $contact = new Contact();
                $contact->setTpointUid($uid);
                $contact->setCreatedAt(new \DateTime());
            }

            $translations = [];

            foreach(array_slice($locales, 1) as $locale) {
                if(!isset($translations[$locale])) {
                    $translations[$locale] = [];
                }

                $translations[$locale]['companyName'] = $contactsData[$locale][$index]['company_name'] ?? '';
                $translations[$locale]['specification'] = $contactsData[$locale][$index]['company_specification'] ?? '';
                $translations[$locale]['city'] = $contactsData[$locale][$index]['city'] ?? '';
                $translations[$locale]['website'] = $contactsData[$locale][$index]['website'] ?? '';
                $translations[$locale]['description'] = $contactsData[$locale][$index]['priorities_of_research'] ?? '';
            }

            $contact
                ->setUpdatedAt(new \DateTime())
                ->setIsPublic($isPublic)
                ->setType($type)
                ->setCompanyName($companyName)
                ->setSpecification($specification)
                ->setGender($gender)
                ->setAcademicTitle($academicTitle)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setDescription($description)
                ->setStreet($street)
                ->setZipCode($zipCode)
                ->setCity($city)
                ->setCountry($country)
                ->setState($state)
                ->setLanguage($language)
                ->setPhone($phone)
                ->setEmail($email)
                ->setWebsite($website)
                ->setContactGroups($contactGroups)
                ->setParent(null)
                ->setTranslations($translations)
            ;

            if($contact->getId()) {
                $updatedContacts++;
            } else {
                $createdContacts++;
            }

            $this->doctrine->getManager()->persist($contact);
            $this->doctrine->getManager()->flush();
        }

        $io->info(sprintf('Memory usage after contact import %s', (memory_get_usage() / 1024 / 1024) . 'MB'));

        $io->success(sprintf('Successfully updated %s contacts :)', $updatedContacts));
        $io->success(sprintf('Successfully created %s contacts :)', $createdContacts));

        foreach($contactsData[$locales[0]] as $index => $contactData) {

            if(isset($contactData['employment_collection'])) {

                $contact = null;

                $contact = $this->doctrine->getManager()->getRepository(Contact::class)->findOneBy([
                    'tpointUid' => $contactData['v_card_uid'],
                ]);

                if (!$contact) {
                    break;
                }

                $officialEmployment = null;

                foreach($contactData['employment_collection'] as $employmentIndex => $employmentCollection) {
                    $company = null;
                    $employee = null;

                    if ($contactData['type'] === 'person') {

                        $employee = $contact;

                        $company = $this->doctrine->getManager()->getRepository(Contact::class)->findOneBy([
                            'tpointUid' => $employmentCollection['v_card_uid'],
                            'type' => 'company',
                        ]);
                    }

                    if ($contactData['type'] === 'company') {

                        $company = $contact;

                        $employee = $this->doctrine->getManager()->getRepository(Contact::class)->findOneBy([
                            'tpointUid' => $employmentCollection['v_card_uid'],
                            'type' => 'person',
                        ]);

                    }

                    $employment = $this->doctrine->getManager()->getRepository(Employment::class)->findOneBy([
                        'company' => $company,
                        'employee' => $employee,
                    ]);

                    if(!$employment) {
                        $employment = new Employment();
                        $employment->setCreatedAt(new \DateTime());
                    }

                    $translations = [];

                    foreach(array_slice($locales, 1) as $locale) {
                        if(!isset($translations[$locale])) {
                            $translations[$locale] = [];
                        }

                        $translations[$locale]['role'] = $employmentIndex === 0 ? ($contactsData[$locale][$index]['business_function'] ?? '') : '';
                    }

                    $employment
                        ->setUpdatedAt(new \DateTime())
                        ->setCompany($company)
                        ->setEmployee($employee)
                        ->setPosition(10000)
                        ->setRole($employmentIndex === 0 ? ($contactData['business_function'] ?? '') : '')
                        ->setTranslations($translations)
                    ;

                    if($employment->getId()) {
                        $updatedEmployments++;
                    } else {
                        $createdEmployments++;
                    }

                    $this->doctrine->getManager()->persist($employment);
                    $this->doctrine->getManager()->flush();

                    if($employmentIndex === 0) {
                        $officialEmployment = $this->doctrine->getRepository(Employment::class)->findOneBy([
                            'employee' => $employment->getEmployee(),
                            'company' => $employment->getCompany(),
                            'role' => $employment->getRole()
                        ]);
                    }
                }

                if ($contactData['type'] === 'person') {
                    $contact->setOfficialEmployment($officialEmployment);

                    $this->doctrine->getManager()->flush();

                    if ($officialEmployment) {
                        foreach ($contact->getContactGroups() as $contactGroup) {
                            $contactGroup->addEmployment($officialEmployment);
                            $this->doctrine->getManager()->flush();
                        }
                    }
                }
            }
        }

        $io->info(sprintf('Memory usage after employment import %s', (memory_get_usage() / 1024 / 1024) . 'MB'));

        $io->success(sprintf('Successfully updated %s employments :)', $updatedEmployments));
        $io->success(sprintf('Successfully created %s employments :)', $createdEmployments));

        return 0;
    }
}

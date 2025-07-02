<?php

namespace App\Command;

use App\Entity\Contact;
use App\Entity\ContactGroup;
use App\Entity\Country;
use App\Entity\Language;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:contact-group:add-contact')]
class ContactGroupAddContactCommand extends Command
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    protected function configure(): void
    {
        $this->setDescription('Adds all contacts to contact groups regioS print according to set language.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $em = $this->doctrine->getManager();
        $contacts = $em->getRepository(Contact::class)->findAll();
        $languages = $em->getRepository(Language::class)->findAll();
        $countries = $em->getRepository(Country::class)->findAll();

        $contactGroupDE = $em->getRepository(ContactGroup::class)->findOneBy(['name' => "regioS print DE"]);
        $contactGroupFR = $em->getRepository(ContactGroup::class)->findOneBy(['name' => "regioS print FR"]);
        $contactGroupIT = $em->getRepository(ContactGroup::class)->findOneBy(['name' => "regioS print IT"]);

        $countDE = 0;
        $countFR = 0;
        $countIT = 0;

        $io->warning(sprintf('Kontakte werden den Gruppen zugeordnet...'));

        foreach ($contacts as $contact) {

            $langId = $contact->getLanguage() ? $contact->getLanguage()->getId() : null;
            $countryId = $contact->getCountry() ? $contact->getCountry()->getId() : null;
            $lang = "de";

            if($langId) {
                $langEntity = current(array_filter($languages, fn($lang) => $lang->getId() == $langId));
                $lang = $langEntity ? $langEntity->getCode() : null;
            }

            if(!$langId) {
                $countryEntity = current(array_filter($countries, fn($country) => $country->getId() == $countryId));
                $countryName = $countryEntity ? $countryEntity->getName() : null;

                switch ($countryName) {
                    case "Italien":
                        $lang = "it";
                        break;
                    case "Frankreich":case "Belgien":
                        $lang = "fr";
                        break;
                    default:
                        $lang = "de";
                        break;
                }
            }

            if($lang === "fr") {
                $contactGroupFR->addContact($contact);
                $countFR++;

            } else if($lang === "it") {
                $contactGroupIT->addContact($contact);
                $countIT++;

            } else {
                $contactGroupDE->addContact($contact);
                $countDE++;
            }

            $em->persist($contact);
            $em->flush();
        }

        $io->success(sprintf('%s contacts moved to regioS print DE.', $countDE));
        $io->success(sprintf('%s contacts moved to regioS print FR.', $countFR));
        $io->success(sprintf('%s contacts moved to regioS print IT.', $countIT));

        return Command::SUCCESS;
    }
}

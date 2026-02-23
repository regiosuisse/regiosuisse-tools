<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Region;
use App\Entity\State;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:cities:analyze')]
class CitiesAnalyzeCommand extends Command
{
    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate cities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $geojsonCities = file_get_contents(__DIR__.'/../../config/gis/cities.json');
        $geojsonCities = json_decode($geojsonCities, true);



        $cities = $this->doctrine->getRepository(City::class)->findAll();

        $unknownCities = [];

        foreach ($cities as $city) {

            $found = false;

            foreach($geojsonCities['features'] as $feature) {

                if($city->getMunicipalNumber() === $feature['properties']['GMDNR']) {
                    $found = true;
                }

            }

            if(!$found) {
                $unknownCities[] = $city;
            }

        }

        $unknownGeojsonCities = [];

        foreach ($geojsonCities['features'] as $feature) {

            $found = false;

            foreach ($cities as $city) {

                if($city->getMunicipalNumber() === $feature['properties']['GMDNR']) {
                    $found = true;
                }

            }

            if(!$found) {
                $unknownGeojsonCities[] = $feature;
            }

        }

        if(!count($unknownCities) && !count($unknownGeojsonCities)) {
            return 0;
        }

        $regions = $this->doctrine->getRepository(Region::class)->findAll();

        $io->info(sprintf('Found %s unknown city entities:', count($unknownCities)));

        $io->table(['ID', 'GMDNR', 'Bezeichnung', 'Kanton', 'Regionen'], array_map(function ($city) use ($regions) {

            $inRegions = array_map(function ($region) {
                return $region->getName();
            }, array_filter($regions, function ($region) use ($city) {
                foreach($region->getCities() as $c) {
                    if($c->getId() === $city->getId()) {
                        return true;
                    }
                }
                return false;
            }));

            return [$city->getId(), $city->getMunicipalNumber(), $city->getName(), $city->getState() ? $city->getState()->getName() : '-', implode(', ', $inRegions)];

        }, $unknownCities));

        $io->info(sprintf('Found %s unknown geojson features:', count($unknownGeojsonCities)));

        $io->table(['GMDNR', 'Bezeichnung'], array_map(function ($feature) {
            return [$feature['properties']['GMDNR'], $feature['properties']['GMDNAME']];
        }, $unknownGeojsonCities));

        return 0;

    }
}

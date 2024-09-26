<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\ChmosService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:projects:patch-tags')]
class ProjectsPatchTagsCommand extends Command
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
            ->setDescription('Update projects tags to attribute.')
            ->addOption('ids', null, InputOption::VALUE_OPTIONAL, 'Ids of projects to patch.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of the attribute.')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Name of the tag.')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Name of the attribute.')
            ->addOption('remove-after-patch', null, InputOption::VALUE_NONE, 'Remove the tag when patched.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Project[] $projects */
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb
            ->select('DISTINCT p')
            ->from(Project::class, 'p')
            ->leftJoin('p.tags', 't')
            ->andWhere('t.name = :name')
            ->setParameter('name', $input->getOption('from'))
        ;

        $tagName = $input->getOption('from');
        $attributeType = $input->getOption('type');
        $attributeName = $input->getOption('to');

        if(!$tagName || !$attributeType || !$attributeName) {
            $io->error('Please specify the tag name, attribute name and attribute type.');
            return 1;
        }

        if($input->getOption('ids'))  {
            $ids = array_map('trim', explode(',', $input->getOption('ids')));
            $qb
                ->where('p.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;
        }

        $projects = $qb->getQuery()->getResult();

        $count = 0;
        $errorCount = 0;

        foreach($projects as $project) {
            try {

                $tag = $this->doctrine->getManager()->getRepository(Tag::class)->findOneBy([
                    'name' => $tagName,
                ]);

                $entityName = 'App\\Entity\\'.ucfirst($attributeType);
                $attribute = $this->doctrine->getManager()->getRepository($entityName)->findOneBy([
                    'name' => $attributeName,
                ]);

                $project->{'add'.ucfirst($attributeType.'')}($attribute);

                if($input->getOption('remove-after-patch')) {
                    $project->removeTag($tag);
                }

                $project->setUpdatedAt(new \DateTime());

                $this->doctrine->getManager()->persist($project);
                $this->doctrine->getManager()->flush();

                $io->info(sprintf('Patch of project %s succeeded..', $project->getId()));
                $count++;

            } catch (\Throwable $exception) {
                $errorCount++;
                $io->error(sprintf('Patch of project %s failed..', $project->getId()));

            }
        }

        if($errorCount > 0) {
            $io->warning(sprintf('Tag patch partially failed. %s of %s projects were patched, while %s failed to patch successfully.', $count, count($projects), $errorCount));

            return 1;
        }

        $io->success(sprintf('Tag patch completed. %s of %s projects were patched.', $count, count($projects)));

        return 0;
    }
}

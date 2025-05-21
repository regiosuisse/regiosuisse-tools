<?php

namespace App\Controller;

use App\Service\CircularEconomyProjectService;
use App\Util\PvTrans;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Inbox;
use App\Entity\CircularEconomyProject;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(path: '/api/v1/circular-economy-projects', name: 'api_circular_economy_projects_')]
class ApiCircularEconomyProjectsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'ids[]',
        description: 'Set specific ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'startDate[]',
        description: 'Include only projects with given start dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
        style: 'deepObject',
    )]
    #[OA\Parameter(
        name: 'endDate[]',
        description: 'Include only projects with given end dates (+1 year)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date')),
    )]
    #[OA\Parameter(
        name: 'state[]',
        description: 'Include only specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeState[]',
        description: 'Exclude specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeTopic[]',
        description: 'Exclude specific topics (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'instrument[]',
        description: 'Include only specific instruments (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeInstrument[]',
        description: 'Exclude specific instruments (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'geographicRegion[]',
        description: 'Include only specific geographic regions (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeGeographicRegion[]',
        description: 'Exclude specific geographic regions (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'businessSector[]',
        description: 'Include only specific business sectors (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeBusinessSector[]',
        description: 'Exclude specific business sectors (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'status[]',
        description: 'Return projects by status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['public', 'draft']),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Limit returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'offset',
        description: 'Skip returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'orderBy[]',
        description: 'Order items by field',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt']),
    )]
    #[OA\Parameter(
        name: 'orderDirection[]',
        description: 'Set order direction',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns all projects',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CircularEconomyProject::class, groups: ['id', 'circular_economy_project']))
        )
    )]
    #[OA\Tag(name: 'Circular Economy Projects')]
    public function index(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();
        
        $qb
            ->select('DISTINCT p')
            ->from(CircularEconomyProject::class, 'p')
        ;
        
        if(!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('p.isPublic = TRUE');
        }

        if($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('p.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('p.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }
        
        if($request->get('term')) {
            $qb
                ->andWhere('(p.title LIKE :term OR p.description LIKE :term OR p.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }
        
        if($request->get('startDate') && is_array($request->get('startDate')) && count($request->get('startDate'))) {

            $startDateQuery = [];

            foreach($request->get('startDate') as $key => $startDate) {
                $startDateFrom = new \DateTime($startDate);
                $startDateTo = (new \DateTime($startDate))->modify('+1 year');
                $startDateQuery[] = '(p.startDate >= :startDateFrom'.$key.' AND p.startDate < :startDateTo'.$key.')';
                $qb
                    ->setParameter('startDateFrom'.$key, $startDateFrom, Types::DATETIME_MUTABLE)
                    ->setParameter('startDateTo'.$key, $startDateTo, Types::DATETIME_MUTABLE)
                ;
            }

            $qb->andWhere(implode(' OR ', $startDateQuery));

        }
        
        if($request->get('endDate') && is_array($request->get('endDate')) && count($request->get('endDate'))) {

            $endDateQuery = [];

            foreach($request->get('endDate') as $key => $endDate) {
                $endDateFrom = new \DateTime($endDate);
                $endDateTo = (new \DateTime($endDate))->modify('+1 year');
                $endDateQuery[] = '(p.endDate >= :endDateFrom'.$key.' AND p.endDate < :endDateTo'.$key.')';
                $qb
                    ->setParameter('endDateFrom'.$key, $endDateFrom, Types::DATETIME_MUTABLE)
                    ->setParameter('endDateTo'.$key, $endDateTo, Types::DATETIME_MUTABLE)
                ;
            }

            $qb->andWhere(implode(' OR ', $endDateQuery));

        }
        
        if($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {
            foreach($request->get('state') as $key => $state) {
                $qb
                    ->leftJoin('p.states', 'state'.$key)
                    ->andWhere('state'.$key.'.name = :state'.$key.' OR state'.$key.'.id = :stateId'.$key)
                    ->setParameter('state'.$key, $state)
                    ->setParameter('stateId'.$key, $state)
                ;
            }
        }

        if ($request->get('excludeState') && is_array($request->get('excludeState')) && count($request->get('excludeState'))) {
            $excludedStates = $request->get('excludeState');

            $qb
                ->leftJoin('p.states', 'excludeState')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeState.id IN (:excludedStates) OR excludeState.name IN (:excludedStates) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedStates', $excludedStates);
        }

        if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {
            foreach($request->get('topic') as $key => $topic) {
                $qb
                    ->leftJoin('p.topics', 'topic'.$key)
                    ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                    ->setParameter('topic'.$key, $topic)
                    ->setParameter('topicId'.$key, $topic)
                ;
            }
        }

        if ($request->get('excludeTopic') && is_array($request->get('excludeTopic')) && count($request->get('excludeTopic'))) {
            $excludedTopics = $request->get('excludeTopic');

            $qb
                ->leftJoin('p.topics', 'excludeTopic')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeTopic.id IN (:excludedTopics) OR excludeTopic.name IN (:excludedTopics) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedTopics', $excludedTopics);
        }

        if($request->get('instrument') && is_array($request->get('instrument')) && count($request->get('instrument'))) {

            $instrumentQuery = [];

            foreach($request->get('instrument') as $key => $instrument) {

                $instrumentQuery[] = 'instrument'.$key.'.name = :instrument'.$key.' OR instrument'.$key.'.id = :instrumentId'.$key;

                $qb
                    ->leftJoin('p.instruments', 'instrument'.$key)
                    ->setParameter('instrument'.$key, $instrument)
                    ->setParameter('instrumentId'.$key, $instrument)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $instrumentQuery))
            ;
        }

        if ($request->get('excludeInstrument') && is_array($request->get('excludeInstrument')) && count($request->get('excludeInstrument'))) {
            $excludeInstruments = $request->get('excludeInstrument');

            $qb
                ->leftJoin('p.instruments', 'excludeInstrument')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeInstrument.id IN (:excludedInstruments) OR excludeInstrument.name IN (:excludedInstruments) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludeInstruments', $excludeInstruments);
        }

        if($request->get('geographicRegion') && is_array($request->get('geographicRegion')) && count($request->get('geographicRegion'))) {
            foreach($request->get('geographicRegion') as $key => $geographicRegion) {
                $qb
                    ->leftJoin('p.geographicRegions', 'geographicRegion'.$key)
                    ->andWhere('geographicRegion'.$key.'.name = :geographicRegion'.$key.' OR geographicRegion'.$key.'.id = :geographicRegionId'.$key)
                    ->setParameter('geographicRegion'.$key, $geographicRegion)
                    ->setParameter('geographicRegionId'.$key, $geographicRegion)
                ;
            }
        }

        if ($request->get('excludeGeographicRegion') && is_array($request->get('excludeGeographicRegion')) && count($request->get('excludeGeographicRegion'))) {
            $excludedGeographicRegions = $request->get('excludeGeographicRegion');

            $qb
                ->leftJoin('p.geographicRegions', 'excludeGeographicRegion')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeGeographicRegion.id IN (:excludedGeographicRegions) OR excludeGeographicRegion.name IN (:excludedGeographicRegions) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedRegions', $excludedGeographicRegions);
        }

        if($request->get('businessSector') && is_array($request->get('businessSector')) && count($request->get('businessSector'))) {
            foreach($request->get('businessSector') as $key => $businessSector) {
                $qb
                    ->leftJoin('p.businessSectors', 'businessSector'.$key)
                    ->andWhere('businessSector'.$key.'.name = :businessSector'.$key.' OR businessSector'.$key.'.id = :businessSectorId'.$key)
                    ->setParameter('businessSector'.$key, $businessSector)
                    ->setParameter('businessSectorId'.$key, $businessSector)
                ;
            }
        }

        if ($request->get('excludeBusinessSector') && is_array($request->get('excludeBusinessSector')) && count($request->get('excludeBusinessSector'))) {
            $excludedBusinessSectors = $request->get('excludeBusinessSector');

            $qb
                ->leftJoin('p.businessSectors', 'excludeBusinessSector')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeBusinessSector.id IN (:excludedBusinessSectors) OR excludeBusinessSector.name IN (:excludedBusinessSectors) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedBusinessSectors', $excludedBusinessSectors);
        }

        if($request->get('status') && is_array($request->get('status')) && count($request->get('status'))) {

            $statusQuery = [];

            foreach($request->get('status') as $key => $status) {

                $statusQuery[] = 'p.isPublic = :isPublic'.$key;

                $qb
                    ->setParameter('isPublic'.$key, $status === 'public')
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $statusQuery))
            ;

        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'startDate', 'endDate'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                        $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('p.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('p.id', 'DESC')
            ;
        }
        
        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }
        
        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }
        
        $projects = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($projects, null, [
            'groups' => ['id', 'circular_economy_project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single project',
        content: new OA\JsonContent(
            ref: new Model(type: CircularEconomyProject::class, groups: ['id', 'circular_economy_project'])
        )
    )]
    #[OA\Tag(name: 'Circular Economy Projects')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $project = $em->getRepository(CircularEconomyProject::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'circular_economy_project', 'topic', 'program', 'instrument', 'state', 'country', 'geographic_region', 'business_sector'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a project',
        content: new OA\JsonContent(
            ref: new Model(type: CircularEconomyProject::class, groups: ['id', 'circular_economy_project'])
        )
    )]
    #[OA\Tag(name: 'Circular Economy Projects')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, CircularEconomyProjectService $circularEconomyProjectService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $circularEconomyProjectService->validateCircularEconomyProjectPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $project = $circularEconomyProjectService->createCircularEconomyProject($payload);

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'circular_economy_project'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a project',
        content: new OA\JsonContent(
            ref: new Model(type: CircularEconomyProject::class, groups: ['id', 'circular_economy_project'])
        )
    )]
    #[OA\Tag(name: 'Circular Economy Projects')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           CircularEconomyProjectService $circularEconomyProjectService): JsonResponse
    {
        $project = $em->getRepository(CircularEconomyProject::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $circularEconomyProjectService->validateCircularEconomyProjectPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $project = $circularEconomyProjectService->updateCircularEconomyProject($project, $payload);

        $result = $normalizer->normalize($project, null, [
            'groups' => ['id', 'circular_economy_project'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a project',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Circular Economy Projects')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, CircularEconomyProjectService $circularEconomyProjectService): JsonResponse
    {
        $project = $em->getRepository(CircularEconomyProject::class)
            ->find($request->get('id'));

        $circularEconomyProjectService->deleteCircularEconomyProject($project);

        return $this->json([]);
    }
    
}
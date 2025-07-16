<?php

namespace App\Controller;

use App\Service\PublicationService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Publication;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Inbox;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CommunitySubmissionService;
use App\Entity\CommunitySubmission;

#[Route(path: '/api/v1/publications', name: 'api_publications')]
class ApiPublicationsController extends AbstractController
{
    public function __construct(
        private PublicationService $publicationService,
        private CommunitySubmissionService $submissionService
    ) {}

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
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
        name: 'language[]',
        description: 'Include only specific languages (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
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
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt', 'applicationDeadline']),
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
        description: 'Returns all publications',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Publication::class, groups: ['id', 'publication']))
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('DISTINCT e')
            ->from(Publication::class, 'e')
        ;

        if(!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('e.isPublic = TRUE');
        }

        if($request->get('term')) {
            $qb
                ->andWhere('(e.title LIKE :term OR e.description LIKE :term OR e.keywords LIKE :term OR e.authors LIKE :term OR e.organizations LIKE :term OR e.rightsHolder LIKE :term OR e.url LIKE :term OR e.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {
            foreach($request->get('topic') as $key => $topic) {
                $qb
                    ->leftJoin('e.topics', 'topic'.$key)
                    ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                    ->setParameter('topic'.$key, $topic)
                    ->setParameter('topicId'.$key, $topic)
                ;
            }
        }

        if($request->get('geographicRegion') && is_array($request->get('geographicRegion')) && count($request->get('geographicRegion'))) {
            foreach($request->get('geographicRegion') as $key => $geographicRegion) {
                $qb
                    ->leftJoin('e.geographicRegions', 'geographicRegion'.$key)
                    ->andWhere('geographicRegion'.$key.'.name = :geographicRegion'.$key.' OR geographicRegion'.$key.'.id = :geographicRegionId'.$key)
                    ->setParameter('geographicRegion'.$key, $geographicRegion)
                    ->setParameter('geographicRegionId'.$key, $geographicRegion)
                ;
            }
        }

        if($request->get('language') && is_array($request->get('language')) && count($request->get('language'))) {
            foreach($request->get('language') as $key => $language) {
                $qb
                    ->leftJoin('e.languages', 'language'.$key)
                    ->andWhere('language'.$key.'.name = :language'.$key.' OR language'.$key.'.id = :languageId'.$key)
                    ->setParameter('language'.$key, $language)
                    ->setParameter('languageId'.$key, $language)
                ;
            }
        }

        if($request->get('year') && is_array($request->get('year')) && count($request->get('year'))) {

            $years = array_map(fn ($value) => intval($value), $request->get('year'));
            $from = new \DateTime(min($years).'-01-01 00:00:00');
            $to = new \DateTime(max($years).'-12-31 23:59:59');

            $qb
                ->andWhere('
                    (e.startDate IS NULL OR e.startDate <= :dateTo)
                    AND 
                    (e.endDate IS NULL OR e.endDate >= :dateFrom)
                ')
                ->setParameter('dateFrom', $from, Types::DATETIME_MUTABLE)
                ->setParameter('dateTo', $to, Types::DATETIME_MUTABLE);

        }

        if($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach($request->get('type') as $key => $type) {

                $typeQuery[] = 'e.type = :type'.$key;

                $qb
                    ->setParameter('type'.$key, $type)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery))
            ;

        }

        if($request->get('licenseType') && is_array($request->get('licenseType')) && count($request->get('licenseType'))) {

            $licenseTypeQuery = [];

            foreach($request->get('licenseType') as $key => $licenseType) {

                $licenseTypeQuery[] = 'e.licenseType = :licenseType'.$key;

                $qb
                    ->setParameter('licenseType'.$key, $licenseType)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $licenseTypeQuery))
            ;

        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'applicationDeadline'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('e.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('e.title', 'ASC')
            ;
        }

        $publications = $qb->getQuery()->getResult();

        $groups = ['id', 'publication'];

        if($this->isGranted('ROLE_EDITOR')) {
            $groups[] = 'publication_secure';
        }

        $result = $normalizer->normalize($publications, null, [
            'groups' => $groups,
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single publication',
        content: new OA\JsonContent(
            ref: new Model(type: Publication::class, groups: ['id', 'publication'])
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $publication = $em->getRepository(Publication::class)
            ->find($request->get('id'));

        $groups = ['id', 'publication'];

        if($this->isGranted('ROLE_EDITOR')) {
            $groups[] = 'publication_secure';
        }

        $result = $normalizer->normalize($publication, null, [
            'groups' => $groups,
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a publication',
        content: new OA\JsonContent(
            ref: new Model(type: Publication::class, groups: ['id', 'publication'])
        )
    )]
    #[OA\Tag(name: 'Publications')]
    #[Security(name: 'cookieAuth')]
    public function create(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PublicationService $publicationService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $publicationService->validatePublicationPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $publication = $publicationService->createPublication($payload);

        $groups = ['id', 'publication'];

        if($this->isGranted('ROLE_EDITOR')) {
            $groups[] = 'publication_secure';
        }

        $result = $normalizer->normalize($publication, null, [
            'groups' => $groups,
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a publication',
        content: new OA\JsonContent(
            ref: new Model(type: Publication::class, groups: ['id', 'publication'])
        )
    )]
    #[OA\Tag(name: 'Publications')]
    #[Security(name: 'cookieAuth')]
    public function update(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PublicationService $publicationService): JsonResponse
    {
        $publication = $em->getRepository(Publication::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $publicationService->validatePublicationPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $publication = $publicationService->updatePublication($publication, $payload);

        $groups = ['id', 'publication'];

        if($this->isGranted('ROLE_EDITOR')) {
            $groups[] = 'publication_secure';
        }

        $result = $normalizer->normalize($publication, null, [
            'groups' => $groups,
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a publication',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Publications')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request             $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, PublicationService $publicationService): JsonResponse
    {
        $publication = $em->getRepository(Publication::class)
            ->find($request->get('id'));

        $publicationService->deletePublication($publication);

        return $this->json([]);
    }

    #[Route('/embed/{_locale}', name: 'api_publications_create_from_embed', methods: ['POST'])]
    public function createFromEmbed(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Create pending submission and send verification email
            $submission = $this->submissionService->createPendingSubmission(
                $data, 
                CommunitySubmission::TYPE_PUBLICATION
            );
            
            // Return URL for confirmation page
            return $this->json([
                'redirectUrl' => $this->generateUrl('community_submission_confirmation', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
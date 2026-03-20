<?php

namespace App\Controller;

use App\Entity\ContactGroup;
use App\Entity\Employment;
use App\Service\ContactService;
use App\Util\PvTrans;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route(path: '/api/v1/contacts-data', name: 'api_contacts_data_')]
class ApiContactsDataController extends AbstractController
{
    
    #[Route(path: '/{contactType}', name: 'index', methods: ['GET'])]
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
        name: 'status[]',
        description: 'Return contacts by status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['public', 'draft']),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific types',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'contactGroup[]',
        description: 'Include only specific contact groups (both name or id are valid values)',
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
        name: 'state[]',
        description: 'Include only specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'country[]',
        description: 'Include only specific countries (both name or id are valid values)',
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
        description: 'Returns all contacts and their data',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class, groups: ['id', 'contact']))
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function index(string $contactType, Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(Contact::class, 'c')
            ->distinct()
        ;

        if($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }

        if($request->get('term')) {
            foreach(explode(' ', (string)$request->get('term')) as $key => $term) {
                $qb
                    ->andWhere('(c.id LIKE :term'.$key.' OR c.companyName LIKE :term'.$key.' OR c.firstName LIKE :term'.$key.' OR c.lastName LIKE :term'.$key.' OR c.translations LIKE :term'.$key.' OR c.gender LIKE :term'.$key.' OR c.street LIKE :term'.$key.' OR c.zipCode LIKE :term'.$key.' OR c.city LIKE :term'.$key.' OR c.email LIKE :term'.$key.' OR c.phone LIKE :term'.$key.' OR c.linkedIn LIKE :term'.$key.' OR c.website LIKE :term'.$key.' OR c.description LIKE :term'.$key.')')
                    ->setParameter('term'.$key, '%'.$term.'%');
            }
        }

        if($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach($request->get('type') as $key => $type) {

                $typeQuery[] = 'c.type = :type'.$key;

                $qb
                    ->setParameter('type'.$key, $type)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery))
            ;

        }

        if($request->get('status') && is_array($request->get('status')) && count($request->get('status'))) {

            $statusQuery = [];

            foreach($request->get('status') as $key => $status) {

                $statusQuery[] = 'c.isPublic = :isPublic'.$key;

                $qb
                    ->setParameter('isPublic'.$key, $status === 'public')
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $statusQuery))
            ;

        }

        if(!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('c.isPublic = TRUE');
        }

        $filters = [
            'contactGroup' => ['path' => 'c.contactGroups', 'alias' => 'contactGroup'],
            'state'        => ['path' => 'c.state',         'alias' => 'state'],
            'country'      => ['path' => 'c.country',       'alias' => 'country'],
            'language'     => ['path' => 'c.language',      'alias' => 'language'],
        ];

        foreach ($filters as $requestKey => $config) {
            $values = $request->get($requestKey);

            if (!is_array($values)) {
                continue;
            }

            $values = array_values(array_unique(array_filter(
                $values,
                static fn ($v) => $v !== null && $v !== ''
            )));

            if (!$values) {
                continue;
            }

            $alias = $config['alias'];

            $qb->leftJoin($config['path'], $alias);

            $orX = $qb->expr()->orX();

            foreach ($values as $key => $value) {
                $nameParam = sprintf('%sName%d', $requestKey, $key);
                $idParam   = sprintf('%sId%d', $requestKey, $key);

                $orX->add(
                    $qb->expr()->orX(
                        $qb->expr()->eq($alias . '.name', ':' . $nameParam),
                        $qb->expr()->eq($alias . '.id', ':' . $idParam)
                    )
                );

                $qb->setParameter($nameParam, $value);
                $qb->setParameter($idParam, $value);
            }

            $qb->andWhere($orX);
        }

        $topics = $request->get('topic');

        if (is_array($topics)) {
            $topics = array_values(array_unique(array_filter(
                $topics,
                static fn ($v) => $v !== null && $v !== ''
            )));

            if ($topics) {
                $qb->innerJoin('c.topics', 'topic');

                $orX = $qb->expr()->orX();

                foreach ($topics as $key => $topic) {
                    $nameParam = 'topicName' . $key;
                    $idParam   = 'topicId' . $key;

                    $orX->add(
                        $qb->expr()->orX(
                            $qb->expr()->eq('topic.name', ':' . $nameParam),
                            $qb->expr()->eq('topic.id', ':' . $idParam)
                        )
                    );

                    $qb->setParameter($nameParam, $topic);
                    $qb->setParameter($idParam, $topic);
                }

                $qb
                    ->andWhere($orX)
                    ->groupBy('c.id')
                    ->having('COUNT(DISTINCT topic.id) = :topicCount')
                    ->setParameter('topicCount', count($topics));
            }
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'firstName', 'lastName', 'companyName', 'type'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('c.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('c.id', 'ASC')
            ;
        }

        $contacts = $qb->getQuery()->getResult();

        $contactsResult = $normalizer->normalize($contacts, null, [
            'groups' => ['id', 'contact', 'country', 'state', 'language']
        ]);

        $qb = $em->createQueryBuilder();

        $qb
            ->select('e, co')
            ->from(Employment::class, 'e')
        ;

        $type = $contactType ?: 'employee';
        $typeOther = $contactType === 'company' ? 'employee' : 'company';

        if($contactType === 'company') {
            $qb
                ->leftJoin('e.employee', 'co')
                ->andWhere('e.company IN (:contacts)')
            ;

        } else {
            $qb
                ->leftJoin('e.company', 'co')
                ->andWhere('e.employee IN (:contacts)')
            ;
        }

        $qb
            ->setParameter('contacts', $contacts)
        ;

        $employments = $qb->getQuery()->getResult();

        $employmentsResult = $normalizer->normalize($employments, null, [
            'groups' => ['id', 'employment', 'contact'],
            'attributes' => [
                'id',
                'role' => [
                    'name',
                    'translations',
                ],
                $typeOther => [
                    'id',
                    'name',
                    'companyName',
                    'specification',
                    'street',
                    'zipCode',
                    'city',
                    'state',
                    'phone',
                    'email',
                    'linkedIn',
                    'website',
                    'translations',
                ],
                $type => [
                    'id',
                ],
                'contactGroups' => [
                    'id',
                ],
                'position',
                'translations',
            ],
        ]);

        $resultData = [
            'contacts' => $contactsResult,
            'employments' => $employmentsResult,
        ];

        return $this->json($resultData);
    }
    
}
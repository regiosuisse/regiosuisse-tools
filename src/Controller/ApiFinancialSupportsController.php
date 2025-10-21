<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\FinancialSupport;
use App\Service\FinancialSupportService;
use App\Util\PvTrans;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/api/v1/financial-supports', name: 'api_financial_supports_')]
class ApiFinancialSupportsController extends AbstractController
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
        name: 'projectType[]',
        description: 'Include only specific project types (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeProjectType[]',
        description: 'Exclude specific project types (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'beneficiary[]',
        description: 'Include only specific beneficiaries (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'excludeBeneficiary[]',
        description: 'Exclude specific beneficiaries (both name or id are valid values)',
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
        description: 'Returns all financial supports',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support']))
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function index(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('DISTINCT p')
            ->from(FinancialSupport::class, 'p')
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
                ->andWhere('(p.name LIKE :term OR p.description LIKE :term OR p.additionalInformation LIKE :term OR p.inclusionCriteria LIKE :term OR p.financingRatio LIKE :term OR p.application LIKE :term OR p.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('projectType') && is_array($request->get('projectType')) && count($request->get('projectType'))) {
            foreach($request->get('projectType') as $key => $projectType) {
                $qb
                    ->leftJoin('p.projectTypes', 'projectType'.$key)
                    ->andWhere('projectType'.$key.'.name = :projectType'.$key.' OR projectType'.$key.'.id = :projectTypeId'.$key)
                    ->setParameter('projectType'.$key, $projectType)
                    ->setParameter('projectTypeId'.$key, $projectType)
                ;
            }
        }

        if ($request->get('excludeProjectType') && is_array($request->get('excludeProjectType')) && count($request->get('excludeProjectType'))) {
            $excludedProjectTypes = $request->get('excludeProjectType');

            $qb
                ->leftJoin('p.projectTypes', 'excludeProjectType')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeProjectType.id IN (:excludedProjectTypes) OR excludeProjectType.name IN (:excludedProjectTypes) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedRegions', $excludedProjectTypes);
        }

        if($request->get('beneficiary') && is_array($request->get('beneficiary')) && count($request->get('beneficiary'))) {
            foreach($request->get('beneficiary') as $key => $beneficiary) {
                $qb
                    ->leftJoin('p.beneficiaries', 'beneficiary'.$key)
                    ->andWhere('beneficiary'.$key.'.name = :beneficiary'.$key.' OR beneficiary'.$key.'.id = :beneficiaryId'.$key)
                    ->setParameter('beneficiary'.$key, $beneficiary)
                    ->setParameter('beneficiaryId'.$key, $beneficiary)
                ;
            }
        }

        if ($request->get('excludeBeneficiary') && is_array($request->get('excludeBeneficiary')) && count($request->get('excludeBeneficiary'))) {
            $excludedBeneficiaries = $request->get('excludeBeneficiary');

            $qb
                ->leftJoin('p.beneficiaries', 'excludeBeneficiary')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeBeneficiary.id IN (:excludedBeneficiaries) OR excludeBeneficiary.name IN (:excludedBeneficiaries) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedBeneficiaries', $excludedBeneficiaries);
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
            foreach($request->get('instrument') as $key => $instrument) {
                $qb
                    ->leftJoin('p.instruments', 'instrument'.$key)
                    ->andWhere('instrument'.$key.'.name = :instrument'.$key.' OR instrument'.$key.'.id = :instrumentId'.$key)
                    ->setParameter('instrument'.$key, $instrument)
                    ->setParameter('instrumentId'.$key, $instrument)
                ;
            }
        }

        if ($request->get('excludeInstrument') && is_array($request->get('excludeInstrument')) && count($request->get('excludeInstrument'))) {
            $excludedInstruments = $request->get('excludeInstrument');

            $qb
                ->leftJoin('p.instruments', 'excludeInstrument')
                ->groupBy('p.id')
                ->having('SUM(CASE WHEN excludeInstrument.id IN (:excludedInstruments) OR excludeInstrument.name IN (:excludedInstruments) THEN 1 ELSE 0 END) = 0')
                ->setParameter('excludedInstruments', $excludedInstruments);
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

        if($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {
            foreach($request->get('state') as $key => $state) {
                $qb
                    ->leftJoin('p.states', 'state'.$key)
                    ->andWhere('state'.$key.'.name = :state'.$key.' OR state'.$key.'.id = :stateId'.$key.' OR state'.$key.' IS NULL')
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

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'position'])) {
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
                ->addOrderBy('p.position', 'ASC')
            ;
        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        $financialSupports = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($financialSupports, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, 
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $financialSupportService->validateFinancialSupportPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $financialSupport = $financialSupportService->createFinancialSupport($payload);

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));
        
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $financialSupportService->validateFinancialSupportPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $financialSupport = $financialSupportService->updateFinancialSupport($financialSupport, $payload);

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a financial support',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));
        
        $financialSupportService->deleteFinancialSupport($financialSupport);
        
        return $this->json([]);
    }

    #[Route(path: '/export/{id}-{_locale}.pdf', name: 'export_pdf', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/pdf', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function exportPdf(Request $request, EntityManagerInterface $em,
                               TranslatorInterface $translator): Response
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));

        if(!$financialSupport) {
            throw $this->createNotFoundException();
        }

        if(!$financialSupport->getIsPublic() && !$this->isGranted('ROLE_EDITOR')) {
            throw $this->createNotFoundException();
        }

        $mpdf = new Mpdf([
            'fontDir' => [
                __DIR__.'/../../assets/fonts/',
            ],
            'fontdata' => [
                'helveticaneue' => [
                    'R' => 'helveticaneue.ttf',
                    'B' => 'helveticaneuebold.ttf',
                ]
            ],
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'default_font' => 'helveticaneue',
        ]);

        $mpdf->SetTitle(PvTrans::translate($financialSupport, 'name', $request->getLocale()));
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->shrink_tables_to_fit = 1;

        $logo = PvTrans::translate($financialSupport, 'logo', $request->getLocale());

        if($logo) {
            $file = $em->getRepository(File::class)
                ->find($logo['id']);
            $imagick = new \Imagick();
            $data = stream_get_contents($file->getData());
            $data = count(explode(';base64,', $data)) >= 2 ? explode(';base64,', $data, 2)[1] : $data;
            $imagick->readImageBlob(base64_decode($data));

            $logo = tempnam(sys_get_temp_dir(), 'logo'.$file->getId());
            file_put_contents($logo, $imagick->getImageBlob());
        }

        $mpdf->WriteHTML($this->renderView('pdf/financial-support.html.twig', [
            'financialSupport' => $financialSupport,
            'logo' => $logo,
        ]));

        $extension = 'pdf';
        $fileName = $translator->trans('Finanzhilfen')
            .' - '.PvTrans::translate($financialSupport, 'name', $request->getLocale())
            .'.'.$extension;

        $tmpFile = tempnam(sys_get_temp_dir(), 'fs'.$financialSupport->getId());

        $mpdf->Output($tmpFile, \Mpdf\Output\Destination::FILE);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        $response->headers->set('Content-Type', 'application/pdf');

        $response->deleteFileAfterSend(true);

        return $response;

    }
    
}
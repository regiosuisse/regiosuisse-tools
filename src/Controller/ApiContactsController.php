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
use App\Form\ContactTypeCompany;
use App\Form\ContactTypePerson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Inbox;
use App\Entity\Topic;


#[Route(path: '/api/v1/contacts', name: 'api_contacts_')]
class ApiContactsController extends AbstractController
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
        description: 'Returns all contacts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class, groups: ['id', 'contact']))
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer
    ): JsonResponse {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('DISTINCT c')
            ->from(Contact::class, 'c')
        ;

        if (!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('c.isPublic = TRUE');
        }

        if ($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if ($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }

        if ($request->get('term')) {
            $qb
                ->andWhere('(c.id LIKE :term OR c.companyName LIKE :term OR c.firstName LIKE :term OR c.lastName LIKE :term OR c.translations LIKE :term OR c.gender LIKE :term OR c.street LIKE :term OR c.zipCode LIKE :term OR c.city LIKE :term OR c.email LIKE :term OR c.phone LIKE :term OR c.website LIKE :term OR c.description LIKE :term)')
                ->setParameter('term', '%' . $request->get('term') . '%');
        }

        if ($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach ($request->get('type') as $key => $type) {

                $typeQuery[] = 'c.type = :type' . $key;

                $qb
                    ->setParameter('type' . $key, $type);
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery));
        }

        if ($request->get('status') && is_array($request->get('status')) && count($request->get('status'))) {

            $statusQuery = [];

            foreach ($request->get('status') as $key => $status) {

                $statusQuery[] = 'c.isPublic = :isPublic' . $key;

                $qb
                    ->setParameter('isPublic' . $key, $status === 'public');
            }

            $qb
                ->andWhere(implode(' OR ', $statusQuery));
        }

        if ($request->get('contactGroup') && is_array($request->get('contactGroup')) && count($request->get('contactGroup'))) {

            $contactGroupQuery = [];

            $qb
                ->leftJoin('c.contactGroups', 'contactGroup');

            foreach ($request->get('contactGroup') as $key => $contactGroup) {

                $contactGroupQuery[] = 'contactGroup.name = :contactGroup' . $key . ' OR contactGroup.id = :contactGroupId' . $key;

                $qb
                    ->setParameter('contactGroup' . $key, $contactGroup)
                    ->setParameter('contactGroupId' . $key, $contactGroup)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $contactGroupQuery));
        }

        if ($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {

            $stateQuery = [];

            foreach ($request->get('state') as $key => $state) {

                $stateQuery[] = 'state' . $key . '.name = :state' . $key . ' OR state' . $key . '.id = :stateId' . $key;

                $qb
                    ->leftJoin('c.state', 'state' . $key)
                    ->setParameter('state' . $key, $state)
                    ->setParameter('stateId' . $key, $state)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $stateQuery));
        }

        if ($request->get('country') && is_array($request->get('country')) && count($request->get('country'))) {

            $countryQuery = [];

            foreach ($request->get('country') as $key => $country) {

                $countryQuery[] = 'country' . $key . '.name = :country' . $key . ' OR country' . $key . '.id = :countryId' . $key;

                $qb
                    ->leftJoin('c.country', 'country' . $key)
                    ->setParameter('country' . $key, $country)
                    ->setParameter('countryId' . $key, $country)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $countryQuery));
        }

        if ($request->get('language') && is_array($request->get('language')) && count($request->get('language'))) {

            $languageQuery = [];

            foreach ($request->get('language') as $key => $language) {

                $languageQuery[] = 'language' . $key . '.name = :language' . $key . ' OR language' . $key . '.id = :languageId' . $key;

                $qb
                    ->leftJoin('c.language', 'language' . $key)
                    ->setParameter('language' . $key, $language)
                    ->setParameter('languageId' . $key, $language)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $languageQuery));
        }

        if ($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if ($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if ($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach ($request->get('orderBy') as $key => $orderBy) {

                if (!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'firstName', 'lastName', 'companyName', 'type'])) {
                    continue;
                }

                $direction = 'ASC';

                if (
                    $request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])
                ) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('c.' . $orderBy, $direction);
            }
        } else {
            $qb
                ->addOrderBy('c.id', 'ASC');
        }

        $contacts = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($contacts, null, [
            'groups' => ['id', 'contact', 'country', 'state', 'language'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function find(
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer
    ): JsonResponse {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        if (!$this->isGranted('ROLE_EDITOR') && !$contact->getIsPublic()) {
            throw $this->createNotFoundException();
        }

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        ContactService $contactService
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        $errors = $contactService->validateContactPayload($payload);

        if (is_countable($errors) ? count($errors) : 0) {
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $contact = $contactService->createContact($payload);

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        ContactService $contactService
    ): JsonResponse {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        $errors = $contactService->validateContactPayload($payload);

        if (is_countable($errors) ? count($errors) : 0) {
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $contact = $contactService->updateContact($contact, $payload);

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a contact',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        ContactService $contactService
    ): JsonResponse {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        $success = $contactService->deleteContact($contact);

        return $this->json([
            'success' => $success,
        ], $success ? 200 : 400);
    }

    #[Route(path: '.xlsx', name: 'xlsx', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Parameter(
        name: 'ids[]',
        description: 'Set specific ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific types',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'contactGroups[]',
        description: 'Include only specific contact groups',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function xlsx(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ContactService $contactService): Response
    {

        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(Contact::class, 'c')
        ;

        if ($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if ($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }

        if ($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach ($request->get('type') as $key => $type) {

                $typeQuery[] = 'c.type = :type' . $key;

                $qb
                    ->setParameter('type' . $key, $type);
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery));
        }

        $contacts = $qb->getQuery()->getResult();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (['de', 'fr', 'it'] as $localeIndex => $locale) {

            $sheet = new Worksheet(null, 'Kontakte - ' . strtoupper($locale));
            $spreadsheet->addSheet($sheet, $localeIndex);

            $sheet = $spreadsheet->getSheet($localeIndex);

            $columns = [
                'ID' => 'A',
                'Status' => 'B',
                'Typ' => 'C',
                'Organisation' => 'D',
                'Übergeordnete Organisation' => 'E',
                'Zusatzang. Fa./Inst.' => 'F',
                'Vorname' => 'G',
                'Nachname' => 'H',
                'Funktion' => 'I',
                'Strasse' => 'J',
                'PLZ' => 'K',
                'Ort' => 'L',
                'Land' => 'M',
                'Kanton' => 'N',
                'Sprache' => 'O',
                'E-Mail' => 'P',
                'Telefon' => 'Q',
                'Website' => 'R',
                'Beschreibung' => 'S',
                'Kontaktgruppen' => 'T',
            ];

            $row = 1;

            foreach ($columns as $columnLabel => $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
                $sheet->setCellValue($column . $row, $columnLabel);
            }

            $sheet->getColumnDimension($columns['Land'])->setWidth(60);
            $sheet->getColumnDimension($columns['Kanton'])->setWidth(60);
            $sheet->getColumnDimension($columns['Sprache'])->setWidth(60);
            $sheet->getColumnDimension($columns['Beschreibung'])->setWidth(60);
            $sheet->getColumnDimension($columns['Kontaktgruppen'])->setWidth(100);

            $row++;

            /** @var Contact $contact */
            foreach ($contacts as $contact) {
                $basisEntity = $contact;

                $sheet->setCellValue($columns['ID'] . $row, $contact->getId());
                $sheet->setCellValue($columns['Status'] . $row, $contact->getIsPublic() ? 'Öffentlich' : 'Privat');
                $sheet->setCellValue($columns['Typ'] . $row, $contact->getType() === 'company' ? 'Organisation' : 'Person');

                if ($contact->getType() === 'person') {

                    foreach ((PvTrans::trans($contact, 'employments', $locale) ?: []) as $entity) {

                        if ($contact->getOfficialEmployment()) {

                            if ($entity->getCompany() === $contact->getOfficialEmployment()->getCompany()) {
                                $basisEntity = $entity->getCompany();
                                $sheet->setCellValue($columns['Funktion'] . $row, $entity->getRole());
                            }
                        }
                    }
                }

                $sheet->setCellValue($columns['Organisation'] . $row, PvTrans::trans($basisEntity, 'companyName', $locale));

                if ($contact->getParent()) {
                    $sheet->setCellValue($columns['Übergeordnete Organisation'] . $row, PvTrans::trans($contact->getParent(), 'name', $locale));
                }

                $sheet->setCellValue($columns['Zusatzang. Fa./Inst.'] . $row, PvTrans::trans($contact, 'specification', $locale));
                $sheet->setCellValue($columns['Vorname'] . $row, $contact->getFirstName());
                $sheet->setCellValue($columns['Nachname'] . $row, $contact->getLastName());

                $sheet->setCellValue($columns['Strasse'] . $row, $basisEntity->getStreet());
                $sheet->setCellValue($columns['PLZ'] . $row, $basisEntity->getZipCode());
                $sheet->setCellValue($columns['Ort'] . $row, PvTrans::trans($basisEntity, 'city', $locale));

                $sheet->setCellValue($columns['E-Mail'] . $row, $contact->getEmail());
                $sheet->setCellValue($columns['Telefon'] . $row, $contact->getPhone());
                $sheet->setCellValue($columns['Website'] . $row, PvTrans::trans($contact, 'website', $locale));
                $sheet->setCellValue($columns['Beschreibung'] . $row, PvTrans::trans($contact, 'description', $locale));

                $entity = $basisEntity->getCountry();
                if ($entity) {
                    $entity = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Land'] . $row, $entity);

                $entity = $basisEntity->getState();
                if ($entity) {
                    $entity = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Kanton'] . $row, $entity);

                $entity = $contact->getLanguage();
                if ($entity) {
                    $entity = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Sprache'] . $row, $entity);

                $entities = [];
                foreach ($contact->getContactGroups() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Kontaktgruppen'] . $row, implode(', ', $entities));

                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $extension = 'xlsx';
        $fileName = 'Kontakte-' . date('Y-m-d_H-i-s') . '.' . $extension;

        $tmpFile = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($tmpFile);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route(path: '.xlsx/contact-groups', name: 'xlsx_contact_groups', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Parameter(
        name: 'ids[]',
        description: 'Set specific ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Parameter(
        name: 'contactGroupIds[]',
        description: 'Set specific contact group ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function xlsxContactGroups(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, ContactService $contactService): Response
    {

        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(ContactGroup::class, 'c')
        ;

        if ($request->get('contactGroupIds') && !is_array($request->get('contactGroupIds'))) {
            $qb
                ->andWhere('c.id IN (:contactGroupIds)')
                ->setParameter('contactGroupIds', array_map('trim', explode(',', $request->get('contactGroupIds'))))
            ;
        }

        if ($request->get('contactGroupIds') && is_array($request->get('contactGroupIds'))) {
            $qb
                ->andWhere('c.id IN (:contactGroupIds)')
                ->setParameter('contactGroupIds', $request->get('contactGroupIds'))
            ;
        }

        $contactGroups = $qb->getQuery()->getResult();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (['de', 'fr', 'it'] as $localeIndex => $locale) {

            $sheet = new Worksheet(null, 'Kontakte - ' . strtoupper($locale));
            $spreadsheet->addSheet($sheet, $localeIndex);

            $sheet = $spreadsheet->getSheet($localeIndex);

            $columns = [
                'ID' => 'A',
                'Status' => 'B',
                'Typ' => 'C',
                'Organisation' => 'D',
                'Übergeordnete Organisation' => 'E',
                'Zusatzang. Fa./Inst.' => 'F',
                'Vorname' => 'G',
                'Nachname' => 'H',
                'Funktion' => 'I',
                'Strasse' => 'J',
                'PLZ' => 'K',
                'Ort' => 'L',
                'Land' => 'M',
                'Kanton' => 'N',
                'Sprache' => 'O',
                'E-Mail' => 'P',
                'Telefon' => 'Q',
                'Website' => 'R',
                'Beschreibung' => 'S',
                'Kontaktgruppen' => 'T',
            ];

            $row = 1;

            foreach ($columns as $columnLabel => $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
                $sheet->setCellValue($column . $row, $columnLabel);
            }

            $sheet->getColumnDimension($columns['Land'])->setWidth(60);
            $sheet->getColumnDimension($columns['Kanton'])->setWidth(60);
            $sheet->getColumnDimension($columns['Sprache'])->setWidth(60);
            $sheet->getColumnDimension($columns['Beschreibung'])->setWidth(60);
            $sheet->getColumnDimension($columns['Kontaktgruppen'])->setWidth(100);

            $row++;

            /** @var Contact $contact */
            foreach ($contactGroups as $currentContactGroup) {

                $contacts = $currentContactGroup->getContacts();

                if ($request->get('ids')) {
                    $contacts = array_filter($currentContactGroup->getContacts(), function ($contact) use ($request) {
                        return in_array($contact->getId(), $request->get('ids'));
                    });
                }

                foreach ($contacts as $contact) {

                    $basisEntity = $contact;

                    $sheet->setCellValue($columns['ID'] . $row, $contact->getId());
                    $sheet->setCellValue($columns['Status'] . $row, $contact->getIsPublic() ? 'Öffentlich' : 'Privat');
                    $sheet->setCellValue($columns['Typ'] . $row, $contact->getType() === 'company' ? 'Organisation' : 'Person');

                    if ($contact->getType() === 'person') {

                        foreach ((PvTrans::trans($contact, 'employments', $locale) ?: []) as $entity) {

                            foreach ($entity->getContactGroups() as $employmentContactGroup) {

                                if (intval($employmentContactGroup->getId()) === intval($currentContactGroup->getId())) {
                                    $basisEntity = $entity->getCompany();

                                    $sheet->setCellValue($columns['Funktion'] . $row, $entity->getRole());
                                }
                            }
                        }
                    }

                    $sheet->setCellValue($columns['Organisation'] . $row, PvTrans::trans($basisEntity, 'companyName', $locale));

                    if ($contact->getParent()) {
                        $sheet->setCellValue($columns['Übergeordnete Organisation'] . $row, PvTrans::trans($contact->getParent(), 'name', $locale));
                    }

                    $sheet->setCellValue($columns['Zusatzang. Fa./Inst.'] . $row, PvTrans::trans($contact, 'specification', $locale));
                    $sheet->setCellValue($columns['Vorname'] . $row, $contact->getFirstName());
                    $sheet->setCellValue($columns['Nachname'] . $row, $contact->getLastName());

                    $sheet->setCellValue($columns['Strasse'] . $row, $basisEntity->getStreet());
                    $sheet->setCellValue($columns['PLZ'] . $row, $basisEntity->getZipCode());
                    $sheet->setCellValue($columns['Ort'] . $row, PvTrans::trans($basisEntity, 'city', $locale));

                    $sheet->setCellValue($columns['E-Mail'] . $row, $contact->getEmail());
                    $sheet->setCellValue($columns['Telefon'] . $row, $contact->getPhone());
                    $sheet->setCellValue($columns['Website'] . $row, PvTrans::trans($contact, 'website', $locale));
                    $sheet->setCellValue($columns['Beschreibung'] . $row, PvTrans::trans($contact, 'description', $locale));

                    $entity = $basisEntity->getCountry();
                    if ($entity) {
                        $entity = PvTrans::trans($entity, 'name', $locale);
                    }
                    $sheet->setCellValue($columns['Land'] . $row, $entity);

                    $entity = $basisEntity->getState();
                    if ($entity) {
                        $entity = PvTrans::trans($entity, 'name', $locale);
                    }
                    $sheet->setCellValue($columns['Kanton'] . $row, $entity);

                    $entity = $contact->getLanguage();
                    if ($entity) {
                        $entity = PvTrans::trans($entity, 'name', $locale);
                    }
                    $sheet->setCellValue($columns['Sprache'] . $row, $entity);

                    $entities = [];
                    foreach ($contact->getContactGroups() as $entity) {
                        $entities[] = PvTrans::trans($entity, 'name', $locale);
                    }
                    $sheet->setCellValue($columns['Kontaktgruppen'] . $row, implode(', ', $entities));

                    $row++;
                }
            }
        }

        $writer = new Xlsx($spreadsheet);

        $extension = 'xlsx';
        $fileName = 'Kontakte-' . date('Y-m-d_H-i-s') . '.' . $extension;

        $tmpFile = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($tmpFile);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route(path: '/send-verification-emails', name: 'send_verification_emails')]
    public function sendVerificationEmails(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ) {
        $data = json_decode($request->getContent(), true);
        $emails = $data['emails'] ?? [];

        foreach ($emails as $email) {
            // Find the contact by email
            $contact = $entityManager
                ->getRepository(Contact::class)
                ->findOneBy(['id' => $email]);
                

            if ($contact) {
                // Generate a one-time code
                $oneTimeCode = bin2hex(random_bytes(16));
                $contact->setOneTimeCode($oneTimeCode);
                $contact->setVerificationEmailSentDate(new \DateTime());

                $entityManager->persist($contact);

                $verificationLink = $this->generateUrl('api_contacts_contact_verify', [
                    'code' => $oneTimeCode,
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                $language = $contact->getLanguage() ? $contact->getLanguage()->getContext() : 'de';
                $template = match ($language) {
                    'fr' => 'emails/verify_contact_fr.html.twig',
                    'it' => 'emails/verify_contact_it.html.twig',
                    default => 'emails/verify_contact.html.twig',
                };

                $emailMessage = (new Email())
                    ->from('no-reply@regiosuisse.ch')
                    ->to($contact->getEmail())
                    ->subject($language === 'fr' ? 'Confirmez vos coordonnées' : ($language === 'it' ? 'Conferma i tuoi dati di contatto' : 'Bestätigen Sie Ihre Kontaktdaten'))
                    ->html(
                        $this->renderView(
                            $template,
                            [
                                'name' => $contact->getName(),
                                'verificationLink' => $verificationLink,
                            ]
                        )
                    );

                $mailer->send($emailMessage);
            }
        }

        $entityManager->flush();

        return new Response('Emails sent', 200);
    }

    #[Route(path: '/verify/{code}', name: 'contact_verify', methods: ['GET', 'POST'])]
    #[OA\Parameter(
        name: 'code',
        description: 'Verification code',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Verification form or success message',
    )]
    #[OA\Response(
        response: 404,
        description: 'Invalid verification code',
    )]

    public function contactVerify(
        $code,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $contact = $entityManager
            ->getRepository(Contact::class)
            ->findOneBy(['oneTimeCode' => $code]);

        if (!$contact) {
            $this->addFlash(
                'message',
                'Ihre Anfrage wurde bereits verarbeitet. Sollten Sie weitere Änderungen vornehmen wollen, kontaktieren Sie uns bitte unter web@regiosuiesse.ch.'
            );

            return $this->render('contact/update_success.html.twig');
        }

        $locale = $contact->getLanguage() ? $contact->getLanguage()->getContext() : 'de';

        $topics = $entityManager
            ->getRepository(Topic::class)
            ->findAll();

        $filteredTopics = array_filter($topics, function ($topic) {
            return $topic->getContext() === 'contact';
        });
        $filteredTopics = is_array($filteredTopics) ? $filteredTopics : iterator_to_array($filteredTopics);

        $employments = $entityManager
            ->getRepository(Employment::class)
            ->findBy(['employee' => $contact]);

        foreach ($employments as $employment) {
            $company = $employment->getCompany();
            if ($company) {
                $employment->companyName = $company->getCompanyName();
            }
        }


        $form = $this->createForm(ContactTypePerson::class, $contact, [
            'employments' => $employments,
            'topics' => $filteredTopics,
            'locale' => $locale,
        ]);

        // Create and handle the form
        $originalData = [
            'academicTitle' => $contact->getAcademicTitle(),
            'firstName' => $contact->getFirstName(),
            'lastName' => $contact->getLastName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'street' => $contact->getStreet(),
            'zipCode' => $contact->getZipCode(),
            'country' => $contact->getCountry(),
            'language' => $contact->getLanguage(),
            'city' => $contact->getCity(),
            'website' => $contact->getWebsite(),
            'description' => $contact->getDescription(),
            'translations' => $contact->getTranslations() ?: [],
            'gender' => $contact->getGender(),
            'topics' => $contact->getTopics() ?: [],
            'state' => $contact->getState(),
            'userComment' => $contact->getUserComment(),
        ];


        $originalTopics = $originalData['topics'];
        $originalTopicsArray = [];
        
        
        foreach ($originalTopics as $topic) {
            $originalTopicsArray[] = $topic->getId();
        }

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $contact->setOneTimeCode(null);
            $employmentData = $request->request->all('employments');
            $employmentChanges = [];
            $diffData = [];

            if (is_array($employmentData)) {
                foreach ($employmentData as $employmentId => $employmentFields) {
                    // Find the Employment entity
                    $employment = $entityManager->getRepository(Employment::class)->find($employmentId);
                    if ($employment && $employment->getEmployee()->getId() === $contact->getId()) {
                        // Collect original data
                        $originalRole = $employment->getRole() ?? '';
                        $originalTranslations = $employment->getTranslations() ?? [];

                        // Get new data from form submission
                        $newRole = $employmentFields['role'] ?? '';
                        $newTranslations = $employmentFields['translations'] ?? [];

                        // Compare and collect changes
                        $roleChanged = $newRole !== $originalRole;

                        $translationsChanged = [];

                        foreach (['fr', 'it'] as $locale) {
                            $originalTranslation = $originalTranslations[$locale]['role'] ?? '';
                            $newTranslation = $newTranslations[$locale]['role'] ?? '';

                            if ($newTranslation !== $originalTranslation) {
                                $translationsChanged[$locale]['role'] = $newTranslation;
                            }
                        }

                        // If there are changes, add them to the employmentChanges array
                        if ($roleChanged || !empty($translationsChanged)) {
                            $employmentChange = ['id' => $employmentId];

                            if ($roleChanged) {
                                $employmentChange['role'] = $newRole;
                            }

                            if (!empty($translationsChanged)) {
                                $employmentChange['translations'] = $translationsChanged;
                            }

                            $employmentChanges[] = $employmentChange;
                        }
                    }
                }

                // Include employment changes in the diffData
                if (!empty($employmentChanges)) {
                    $diffData['employments'] = $employmentChanges;
                }
            }



            $form->getData();

            $inbox = new Inbox();
            $inbox->setCreatedAt(new \DateTime());
            $inbox->setSource('contact_verification');
            $inbox->setType('contact_update');
            $inbox->setIsMerged(false);
            $inbox->setStatus('pending');
            $inbox->setInternalId($contact->getId());
            $inbox->setTitle($contact->getName());

            $newTopics = $form->get('topics')->getData();
            $topicsArray = [];
            foreach ($newTopics as $topic) {
                $topicsArray[] = ['id' => $topic->getId()];
            }

            $newData = [
                'academicTitle' => $form->get('academicTitle')->getData(),
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData(),
                'email' => $form->get('email')->getData(),
                'phone' => $form->get('phone')->getData(),
                'translations' => [
                    'de' => [
                        'website' => $form->get('website')->getData(),
                        'city' => $form->get('city')->getData(),
                        'description' => $form->get('description')->getData(),
                    ],
                    'fr' => [
                        'website' => $form->get('translations_fr_website')->getData(),
                        'city' => $form->get('translations_fr_city')->getData(),
                        'description' => $form->get('translations_fr_description')->getData(),
                    ],
                    'it' => [
                        'website' => $form->get('translations_it_website')->getData(),
                        'city' => $form->get('translations_it_city')->getData(),
                        'description' => $form->get('translations_it_description')->getData(),
                    ],
                ],
                'website' => $form->get('website')->getData(),
                'city' => $form->get('city')->getData(),
                'description' => $form->get('description')->getData(),
                'street' => $form->get('street')->getData(),
                'zipCode' => $form->get('zipCode')->getData(),
                'country' => $form->get('country')->getData() ? $form->get('country')->getData()->getId() : null,
                'language' => $form->get('language')->getData() ? $form->get('language')->getData()->getId() : null,
                'gender' => $form->get('gender')->getData(),
                'topics' => $topicsArray,
                'state' => $form->get('state')->getData() ? $form->get('state')->getData()->getId() : null,
                'userComment' => $form->get('userComment')->getData(),
            ];



            foreach (['firstName', 'lastName', 'email', 'phone',  'street', 'zipCode', 'academicTitle', 'gender', 'userComment'] as $field) {
                if ($originalData[$field] != $newData[$field]) {
                    $diffData[$field] = $newData[$field];
                }
            }

            if ($originalTopicsArray != $topicsArray) {
                $diffData['topics'] = $topicsArray;
            }


            if ($newData['language']) {
                if ($originalData['language']->getId() != $newData['language']) {
                    $diffData['language'] = $newData['language'];
                }
            }

            if ($newData['country']) {
                if ($originalData['country']->getId() != $newData['country']) {
                    $diffData['country'] = $newData['country'];
                }
            }

            if ($newData['state']) {

                if ($originalData['state']?->getId() != $newData['state']) {
                    $diffData['state'] = $newData['state'];
                }
            }

            foreach (['de', 'fr', 'it'] as $locale) {
                foreach (['website', 'city', 'description'] as $field) {
                    if ($locale === 'de') {
                        $originalValue = $originalData[$field] ?? '';
                        $newValue = $newData[$field] ?? '';
                        if ($originalValue != $newValue) {
                            $diffData[$field] = $newValue;
                        }
                    } else {
                        $originalValue = $originalData['translations'][$locale][$field] ?? '';
                        $newValue = $newData['translations'][$locale][$field] ?? '';
                        if ($originalValue != $newValue) {
                            $diffData['translations'][$locale][$field] = $newValue;
                        }
                    }
                }
            }

            $inbox->setData([
                'changes' => $diffData,
                'removeEmploymentIds' => $request->request->all('removeEmploymentIds') ?? [],
                'delete' => $request->request->get('delete') ?? 0,
            ]);

            $inbox->setNormalizedData([
                'changes' => $diffData,
                'removeEmploymentIds' => $request->request->all('removeEmploymentIds') ?? [],
                'delete' => $request->request->get('delete') ?? 0,
            ]);

            $inbox->setCreatedAt(new \DateTime());

            $entityManager->persist($inbox);
            $entityManager->flush();

            $this->addFlash(
                'message',
                'Danke! Ihre Kontaktdaten wurden aktualisiert.'
            );

            return $this->render('contact/update_success.html.twig');
        }

        $languageCode = $contact->getLanguage() ? $contact->getLanguage()->getContext() : 'de';

        $contactFormTemplate = match ($languageCode) {
            'de' => 'contact/verify_de.html.twig',
            'fr' => 'contact/verify_fr.html.twig',
            'it' => 'contact/verify_it.html.twig',
            default => 'contact/verify_de.html.twig',
        };

        return $this->render($contactFormTemplate, [
            'form' => $form->createView(),
            'locale' => $languageCode,
            'employments' => $employments,
            'topics' => $filteredTopics,
        ]);
    }


    #[Route('/contact/update-success', name: 'contact_update_success')]
    public function contactUpdateSuccess(): Response
    {
        return $this->render('contact/update_success.html.twig');
    }
}

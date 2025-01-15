<?php

namespace App\Controller;

use App\Entity\ContactGroup;
use App\Service\ContactGroupService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ContactService;
use App\Entity\Contact;

#[Route(path: '/api/v1/contact-groups', name: 'api_contact_groups')]
class ApiContactGroupsController extends AbstractController
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
        description: 'Return contact groups by status',
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
        description: 'Returns all contact groups',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group']))
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(ContactGroup::class, 'c')
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
            $qb
                ->andWhere('(c.id LIKE :term OR c.name LIKE :term OR c.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
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

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'name', 'position'])) {
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
                ->addOrderBy('c.position', 'ASC')
            ;
        }

        $contactGroups = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($contactGroups, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single contact group',
        content: new OA\JsonContent(
            ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group'])
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $contactGroup = $em->getRepository(ContactGroup::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($contactGroup, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a contact group',
        content: new OA\JsonContent(
        ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group'])
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactGroupService $contactGroupService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $errors = $contactGroupService->validateContactGroupPayload($payload);

        if(is_countable($errors) ? count($errors) : 0) {
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $contactGroup = $contactGroupService->createContactGroup($payload);

        $result = $normalizer->normalize($contactGroup, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a contact group',
        content: new OA\JsonContent(
            ref: new Model(type: ContactGroup::class, groups: ['id', 'contact_group'])
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactGroupService $contactGroupService): JsonResponse
    {
        $contactGroup = $em->getRepository(ContactGroup::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        $errors = $contactGroupService->validateContactGroupPayload($payload);

        if(is_countable($errors) ? count($errors) : 0) {
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $contactGroup = $contactGroupService->updateContactGroup($contactGroup, $payload);

        $result = $normalizer->normalize($contactGroup, null, [
            'groups' => ['id', 'contact_group'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a contact group',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'ContactGroups')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactGroupService $contactGroupService): JsonResponse
    {
        $contactGroup = $em->getRepository(ContactGroup::class)
            ->find($request->get('id'));

        $contactGroupService->deleteContactGroup($contactGroup);

        return $this->json([]);
    }

    #[Route(path: '/opt-in', name: 'opt_in', methods: ['POST'])]
    public function optIn(Request $request, EntityManagerInterface $em, ContactService $contactService, MailerInterface $mailer): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $contactData = $payload['contact'] ?? [];
        $contactGroupIds = $payload['contactGroups'] ?? [];

        // Validate input
        // ...

        // Check if all requested groups allow public opt-in
        $contactGroups = $em->getRepository(ContactGroup::class)->findBy(['id' => $contactGroupIds]);
        foreach ($contactGroups as $contactGroup) {
            if (!$contactGroup->getPublicOptIn()) {
                return new JsonResponse(['message' => 'One or more of the requested contact groups do not allow public opt-in'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Create or update the contact
        $existingContact = $em->getRepository(Contact::class)->findOneBy(['email' => $contactData['email']]);

        if ($existingContact) {
            $contact = $contactService->updateContact($existingContact, $contactData);
        } else {
            $contact = $contactService->createContact($contactData);
        }

        // Generate a unique one-time code for this request
        $oneTimeCode = bin2hex(random_bytes(16));

        // Add the one-time code to the contact's oneTimeCodeNewsletters array
        $oneTimeCodeNewsletters = $contact->getOneTimeCodeNewsletters();
        $oneTimeCodeNewsletters[$oneTimeCode] = $contactGroupIds;
        $contact->setOneTimeCodeNewsletters($oneTimeCodeNewsletters);
        $em->flush();

        // Include the contact group IDs in the verification link
        $verificationLink = $this->generateUrl('api_contact_groups_confirm_opt_in', [
            'code' => $oneTimeCode,
            'groups' => implode(',', $contactGroupIds),
        ]);

        // Send verification email
        $this->sendOptInEmail($contact, $contactGroupIds, $verificationLink, $mailer, $em);

        return new JsonResponse(['message' => 'Opt-in email sent'], Response::HTTP_OK);
    }

    #[Route(path: '/confirm-opt-in', name: 'confirm_opt_in', methods: ['GET'])]  
    public function confirmOptIn(Request $request, EntityManagerInterface $em, string $code): Response
    {
        // Look up contact by verification code
        $contact = $em->getRepository(Contact::class)->findOneBy(['oneTimeCodeNewsletters' => [$code => []]]);

        if (!$contact) {
            throw $this->createNotFoundException('Invalid verification code');
        }

        // Get the contact group IDs associated with this one-time code
        $oneTimeCodeNewsletters = $contact->getOneTimeCodeNewsletters();
        $contactGroupIds = $oneTimeCodeNewsletters[$code];

        // Add contact to each group
        foreach ($contactGroupIds as $groupId) {
            $contactGroup = $em->getRepository(ContactGroup::class)->find($groupId);
            $contact->addContactGroup($contactGroup);
        }

        // Remove the one-time code 
        unset($oneTimeCodeNewsletters[$code]);
        $contact->setOneTimeCodeNewsletters($oneTimeCodeNewsletters);

        // Persist changes  
        $em->flush();

        return $this->render('contact_group_opt_in/confirmation.html.twig');
    }

    private function sendOptInEmail(Contact $contact, array $contactGroupIds, string $verificationLink, MailerInterface $mailer, EntityManagerInterface $em): void
    {
        $contactGroups = $em->getRepository(ContactGroup::class)->findBy(['id' => $contactGroupIds]);

        $email = (new Email())
            ->to($contact->getEmail())
            ->subject('Bestätigen Sie Ihre Newsletter-Anmeldung')
            ->html(
                $this->renderView('emails/contact_group_opt_in.html.twig', [
                    'contact' => $contact,
                    'contactGroups' => $contactGroups,
                    'verificationLink' => $verificationLink,
                ])
            )
        ;

        $mailer->send($email);
    }

}
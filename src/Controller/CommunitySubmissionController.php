<?php

namespace App\Controller;

use App\Service\CommunitySubmissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\CommunitySubmission;
use Doctrine\ORM\EntityManagerInterface;

class CommunitySubmissionController extends AbstractController
{
    public function __construct(
        private CommunitySubmissionService $submissionService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/verify/{token}', name: 'verify_community_submission')]
    public function verify(string $token): Response
    {
        $success = $this->submissionService->verifySubmission($token);

        // Get the submission to check its type and language
        $submission = $this->entityManager->getRepository(CommunitySubmission::class)
            ->findOneBy(['verificationToken' => $token]);

        if ($submission && $submission->getType() === CommunitySubmission::TYPE_NEWSLETTER) {
            // Get the language from the submission data
            $data = $submission->getSubmissionData();
            $language = $data['contactInfo']['languageCode'] ?? 'de';

            // Choose template based on language
            $template = match($language) {
                'fr' => 'contact/confirmation_fr.html.twig',
                'it' => 'contact/confirmation_it.html.twig',
                default => 'contact/confirmation.html.twig',
            };

            return $this->render($template, [
                'success' => $success,
            ]);
        }

        // For other submission types (jobs, events), use the default template
        return $this->render('community_submission/verification.html.twig', [
            'success' => $success,
        ]);
    }

    #[Route('api/v1/community/submit/confirmation', name: 'community_submission_confirmation')]
    public function confirmationPage(): Response
    {
        return $this->render('community/confirmation.html.twig');
    }
} 
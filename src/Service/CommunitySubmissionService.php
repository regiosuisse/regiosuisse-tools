<?php

namespace App\Service;

use App\Entity\CommunitySubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommunitySubmissionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private JobService $jobService,
        private EventService $eventService
    ) {}

    public function createPendingSubmission(array $submissionData, string $type = CommunitySubmission::TYPE_JOB): CommunitySubmission
    {
        // Extract email from contact info
        $email = $submissionData['contactInfo']['email'] ?? null;
        if (!$email) {
            throw new \InvalidArgumentException('Email is required for submission');
        }

        $submission = new CommunitySubmission($type);
        $submission->setSubmissionData($submissionData);
        $submission->setEmail($email);

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        $this->sendVerificationEmail($submission);

        return $submission;
    }

    public function verifySubmission(string $token): bool
    {
        $submission = $this->entityManager->getRepository(CommunitySubmission::class)
            ->findOneBy(['verificationToken' => $token, 'isVerified' => false]);

        if (!$submission) {
            return false;
        }

        // Mark as verified
        $submission->setIsVerified(true);
        
        // Process the submission based on type
        $this->processVerifiedSubmission($submission);

        $this->entityManager->flush();

        return true;
    }

    private function processVerifiedSubmission(CommunitySubmission $submission): void
    {
        switch ($submission->getType()) {
            case CommunitySubmission::TYPE_JOB:
                $this->jobService->createJobInboxItemFromEmbed($submission->getSubmissionData());
                break;
            case CommunitySubmission::TYPE_EVENT:
                $this->eventService->createEventInboxItemFromEmbed($submission->getSubmissionData());
                break;
            default:
                throw new \InvalidArgumentException('Unknown submission type: ' . $submission->getType());
        }
    }

    private function sendVerificationEmail(CommunitySubmission $submission): void
    {
        $verificationUrl = $this->urlGenerator->generate('verify_community_submission', 
            ['token' => $submission->getVerificationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = match($submission->getType()) {
            CommunitySubmission::TYPE_JOB => 'Bestätigen Sie Ihre Stellenausschreibung - regiosuisse.ch',
            default => 'Bestätigen Sie Ihre Eingabe - regiosuisse.ch',
        };

        $email = (new Email())
            ->from('web@regiosuisse.ch')
            ->to($submission->getEmail())
            ->subject($subject)
            ->html($this->getEmailTemplate($verificationUrl, $submission->getType()));

        $this->mailer->send($email);
    }

    private function getEmailTemplate(string $verificationUrl, string $type): string
    {
        $title = match($type) {
            CommunitySubmission::TYPE_JOB => 'Bestätigen Sie Ihre Stellenausschreibung',
            CommunitySubmission::TYPE_EVENT => 'Bestätigen Sie Ihre Veranstaltung',
            default => 'Bestätigen Sie Ihre Eingabe',
        };

        $description = match($type) {
            CommunitySubmission::TYPE_JOB => 'Vielen Dank für Ihre Stellenausschreibung auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
            CommunitySubmission::TYPE_EVENT => 'Vielen Dank für das einreichen Ihrer Veranstaltung auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
            default => 'Vielen Dank für Ihre Eingabe auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
        };

        return <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <img src="https://regiosuisse.ch/themes/contrib/regiosuisse_theme/logo.svg" alt="regiosuisse Logo" style="max-width: 200px; margin-bottom: 20px;">
                <h2 style="color: #333;">{$title}</h2>
                <p style="color: #666; line-height: 1.5;">
                    {$description}
                    klicken Sie bitte auf den folgenden Link:
                </p>
                <p style="margin: 30px 0;">
                    <a href="{$verificationUrl}" 
                       style="background: #b3ce46; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
                        Eingabe bestätigen
                    </a>
                </p>
                <p style="color: #666; line-height: 1.5;">
                    Falls der Button nicht funktioniert, kopieren Sie bitte diesen Link in Ihren Browser:<br>
                    <span style="color: #b3ce46;">{$verificationUrl}</span>
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                <p style="color: #999; font-size: 0.9em;">
                    Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht auf diese Nachricht.
                </p>
            </div>
        HTML;
    }
} 
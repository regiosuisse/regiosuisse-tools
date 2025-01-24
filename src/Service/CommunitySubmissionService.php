<?php

namespace App\Service;

use App\Entity\CommunitySubmission;
use App\Entity\ContactGroup;
use App\Entity\Language;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CommunitySubmissionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private JobService $jobService,
        private EventService $eventService,
        private ContactService $contactService,
        private Environment $twig
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
                $result = $this->jobService->createJobInboxItemFromEmbed($submission->getSubmissionData());
                // Update submission data with inbox item ID
                $submissionData = $submission->getSubmissionData();
                $submissionData['related_id'] = $result['inbox']->getId();
                $submission->setSubmissionData($submissionData);
                break;

            case CommunitySubmission::TYPE_EVENT:
                $result = $this->eventService->createEventInboxItemFromEmbed($submission->getSubmissionData());
                // Update submission data with inbox item ID
                $submissionData = $submission->getSubmissionData();
                $submissionData['related_id'] = $result['inbox']->getId();
                $submission->setSubmissionData($submissionData);
                break;

            case CommunitySubmission::TYPE_NEWSLETTER:
                $data = $submission->getSubmissionData();
                $contactInfo = $data['contactInfo'];

                // Map locale to language name
                $languageName = match($contactInfo['languageCode']) {
                    'fr' => 'Französisch',
                    'it' => 'Italienisch',
                    'en' => 'Englisch',
                    default => 'Deutsch'
                };

                // Find the corresponding Language entity
                $language = $this->entityManager->getRepository(Language::class)
                    ->findOneBy(['name' => $languageName]);

                if (!$language) {
                    throw new \RuntimeException('Language not found: ' . $languageName);
                }

                // Add language to contact info
                $contactInfo['language'] = $language;

                // Check if contact already exists
                $existingContact = $this->entityManager->getRepository(Contact::class)
                    ->findOneBy(['email' => $contactInfo['email']]);

                $contact = $existingContact
                    ? $this->contactService->updateNewsletterContact($existingContact, $contactInfo)
                    : $this->contactService->createNewsletterContact($contactInfo);

                // Add contact to the specified groups
                foreach ($data['contactGroups'] as $groupId) {
                    $group = $this->entityManager->getRepository(ContactGroup::class)->find($groupId);
                    if ($group) {
                        $contact->addContactGroup($group);
                    }
                }
                $this->entityManager->flush();
                break;

            default:
                throw new \InvalidArgumentException('Unknown submission type: ' . $submission->getType());
        }
    }

    private function sendVerificationEmail(CommunitySubmission $submission): void
    {
        $verificationUrl = $this->urlGenerator->generate(
            'verify_community_submission',
            ['token' => $submission->getVerificationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Collect newsletter-related data if needed
        $data = $submission->getSubmissionData();
        $contactInfo = [];
        $contactGroups = [];
        $language = 'de';

        if ($submission->getType() === CommunitySubmission::TYPE_NEWSLETTER) {
            $contactInfo = $data['contactInfo'] ?? [];
            $language = $contactInfo['languageCode'] ?? 'de';

            // Check if contact exists to get gender
            $existingContact = $this->entityManager->getRepository(Contact::class)
                ->findOneBy(['email' => $contactInfo['email']]);
            if ($existingContact && $existingContact->getGender()) {
                $contactInfo['gender'] = $existingContact->getGender();
            }

            // Prepare the contact groups
            foreach ($data['contactGroups'] as $groupId) {
                $group = $this->entityManager->getRepository(ContactGroup::class)->find($groupId);
                if ($group) {
                    $contactGroups[] = $group;
                }
            }
        }

        // Decide on the email subject
        $subject = match ($submission->getType()) {
            CommunitySubmission::TYPE_NEWSLETTER => match ($language) {
                'fr' => 'Confirmer l\'inscription à la newsletter - regiosuisse.ch',
                'it' => 'Conferma l\'iscrizione alla newsletter - regiosuisse.ch',
                default => 'Bestätigen Sie Ihre Newsletter-Anmeldung - regiosuisse.ch',
            },
            CommunitySubmission::TYPE_JOB => 'Bestätigen Sie Ihre Stellenausschreibung - regiosuisse.ch',
            CommunitySubmission::TYPE_EVENT => 'Bestätigen Sie Ihre Veranstaltung - regiosuisse.ch',
            default => 'Bestätigen Sie Ihre Eingabe - regiosuisse.ch',
        };

        // Render unified template
        $html = $this->getEmailTemplate(
            verificationUrl: $verificationUrl,
            type: $submission->getType(),
            language: $language,
            contactInfo: $contactInfo,
            contactGroups: $contactGroups
        );

        $email = (new Email())
            ->from('web@regiosuisse.ch')
            ->to($submission->getEmail())
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }

    /**
     * A single template for all submission types.
     * If it's "newsletter", show a bullet list of newsletters and language-based text.
     * Otherwise, show the normal job/event snippet.
     */
    private function getEmailTemplate(
        string $verificationUrl,
        string $type,
        string $language = 'de',
        array $contactInfo = [],
        array $contactGroups = []
    ): string {
        // --------------------------------------------------------------------
        // 1) Determine the language-specific text for the newsletter
        //    (title, description, etc.)
        // 2) If not newsletter, we show job/event text.
        // 3) We maintain the same "look" for all, but only show
        //    the bullet list if newsletter.
        // --------------------------------------------------------------------

        // By default (for job/event) in German:
        $title = match ($type) {
            CommunitySubmission::TYPE_JOB => 'Bestätigen Sie Ihre Stellenausschreibung',
            CommunitySubmission::TYPE_EVENT => 'Bestätigen Sie Ihre Veranstaltung',
            CommunitySubmission::TYPE_NEWSLETTER => '', // filled below if newsletter
            default => 'Bestätigen Sie Ihre Eingabe',
        };

        $description = match ($type) {
            CommunitySubmission::TYPE_JOB =>
                'Vielen Dank für Ihre Stellenausschreibung auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
            CommunitySubmission::TYPE_EVENT =>
                'Vielen Dank für das Einreichen Ihrer Veranstaltung auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
            CommunitySubmission::TYPE_NEWSLETTER => '', // filled below if newsletter
            default =>
                'Vielen Dank für Ihre Eingabe auf regiosuisse.ch. Um die Veröffentlichung abzuschliessen,',
        };

        $newsletterListHtml = '';

        // If it's a newsletter, override text in the chosen language & add bullet list
        if ($type === CommunitySubmission::TYPE_NEWSLETTER) {
            // Build the bullet list first
            $newsletterListHtml = '';
            if (!empty($contactGroups)) {
                $newsletterListHtml .= '<ul style="margin: 10px 0 20px 0; padding-left: 20px; color: #333;">';
                foreach ($contactGroups as $group) {
                    // Escape group name, just in case
                    $safeGroupName = htmlspecialchars($group->getName());
                    $newsletterListHtml .= "<li>{$safeGroupName}</li>";
                }
                $newsletterListHtml .= '</ul>';
            }

            // Basic translations for the "title" & "description" block
            switch ($language) {
                case 'fr':
                    $title = 'Confirmer l\'inscription à la newsletter';
                    $description = 'Nous venons de recevoir votre inscription aux listes suivantes:' . $newsletterListHtml . '<span style="color: #333;">Pour finaliser votre inscription, cliquer sur le lien suivant:</span>';
                    break;
                case 'it':
                    $title = 'Conferma l\'iscrizione alla newsletter';
                    $description = 'Abbiamo appena ricevuto la tua iscrizione alle seguenti liste:' . $newsletterListHtml . '<span style="color: #333;">Per completare la tua iscrizione, cliccare sul seguente link:</span>';
                    break;
                default: // German
                    $title = 'Bestätigen Sie Ihre Newsletter-Anmeldung';
                    $description = 'Soeben haben wir Ihre Anmeldung zu folgenden Listen erhalten:' . $newsletterListHtml . '<span style="color: #333;">Um die Anmeldung abzuschliessen, klicken Sie bitte auf den folgenden Link:</span>';
                    break;
            }

            // Clear the newsletter list HTML since we've included it in the description
            $newsletterListHtml = '';
        }

        // Build final HTML
        // We keep the same "look" for everyone, but optionally insert the bullet list
        
        // Define all translatable strings
        $translations = [
            'de' => [
                'button' => 'Eingabe bestätigen',
                'fallback_text' => 'Falls der Button nicht funktioniert, kopieren Sie bitte diesen Link in Ihren Browser:',
                'auto_message' => 'Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht auf diese Nachricht.'
            ],
            'fr' => [
                'button' => 'Confirmer l\'inscription',
                'fallback_text' => 'Si le bouton ne fonctionne pas, veuillez copier ce lien dans votre navigateur:',
                'auto_message' => 'Ceci est un e-mail généré automatiquement. Merci de ne pas répondre à ce message.'
            ],
            'it' => [
                'button' => 'Confermare l\'iscrizione',
                'fallback_text' => 'Se il pulsante non funziona, copia questo link nel tuo browser:',
                'auto_message' => 'Questa è un\'e-mail generata automaticamente. Si prega di non rispondere a questo messaggio.'
            ]
        ];

        // Use the translations for the current language, fallback to German
        $texts = $translations[$language] ?? $translations['de'];

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <img src="https://regiosuisse.ch/themes/contrib/regiosuisse_theme/logo.svg"
                alt="regiosuisse Logo"
                style="max-width: 200px; margin-bottom: 20px;">
            <h2 style="color: #333;">{$title}</h2>
            <p style="color: #333; line-height: 1.5;">
                {$description}
            </p>
            <p style="margin: 30px 0;">
                <a href="{$verificationUrl}"
                style="background: #b3ce46; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
                    {$texts['button']}
                </a>
            </p>
            <p style="color: #666; line-height: 1.5;">
                {$texts['fallback_text']}<br>
                <span style="color: #b3ce46;">{$verificationUrl}</span>
            </p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
            <p style="color: #999; font-size: 0.9em;">
                {$texts['auto_message']}
            </p>
        </div>
        HTML;
    }
}

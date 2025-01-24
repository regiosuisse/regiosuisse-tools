<?php

namespace App\Service;

use App\Entity\CommunitySubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private string $mailerFrom = 'web@regiosuisse.ch'
    ) {}

    /**
     * Send a publication notification for either an Event or a Job.
     */
    public function sendPublicationNotification(
        string $type,         // CommunitySubmission::TYPE_EVENT or CommunitySubmission::TYPE_JOB
        int    $objectId,     // The primary key (ID) of Event or Job
        string $objectTitle,  // The title or name to be used in the email body
        string $contactEmail, // The email address to send the notification to
        string $language = 'de'
    ): void
    {
        // Choose the subject & URL depending on the type (and language).
        switch ($type) {
            case CommunitySubmission::TYPE_EVENT:
                $subject = match ($language) {
                    'fr' => 'Votre événement a été publié - regiosuisse.ch',
                    'it' => 'Il suo evento è stato pubblicato - regiosuisse.ch',
                    default => 'Ihre Veranstaltung wurde veröffentlicht - regiosuisse.ch',
                };
                // Example: https://regiosuisse.ch/agenda?event-id=123
                $publicationUrl = 'https://regiosuisse.ch/agenda?event-id=' . $objectId;
                break;

            case CommunitySubmission::TYPE_JOB:
            default:
                $subject = match ($language) {
                    'fr' => 'Votre offre d\'emploi a été publiée - regiosuisse.ch',
                    'it' => 'La sua offerta di lavoro è stata pubblicata - regiosuisse.ch',
                    default => 'Ihre Stellenausschreibung wurde veröffentlicht - regiosuisse.ch',
                };
                // Example: https://regiosuisse.ch/stellenmarkt?job-id=456
                $publicationUrl = 'https://regiosuisse.ch/stellenmarkt?job-id=' . $objectId;
                break;
        }

        // We can unify the translations for the email body
        // (aside from the subject, which we set above).
        $translations = [
            'de' => [
                'title_event' => 'Ihre Veranstaltung wurde veröffentlicht',
                'title_job'   => 'Ihre Stellenausschreibung wurde veröffentlicht',
                'description_event' => 'Ihre Veranstaltung wurde erfolgreich geprüft und ist nun auf regiosuisse.ch veröffentlicht. Sie können die Veranstaltung unter folgendem Link aufrufen:',
                'description_job'   => 'Ihre Stellenausschreibung wurde erfolgreich geprüft und ist nun auf regiosuisse.ch veröffentlicht. Sie können die Ausschreibung unter folgendem Link aufrufen:',
                'button_event' => 'Veranstaltung anzeigen',
                'button_job'   => 'Stellenausschreibung anzeigen',
                'contact_text' => 'Falls Sie Änderungen wünschen, kontaktieren Sie uns bitte unter:',
                'auto_message' => 'Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht auf diese Nachricht.'
            ],
            'fr' => [
                'title_event' => 'Votre événement a été publié',
                'title_job'   => 'Votre offre d\'emploi a été publiée',
                'description_event' => 'Votre événement a été vérifié avec succès et est maintenant publié sur regiosuisse.ch. Vous pouvez consulter l\'événement via le lien suivant:',
                'description_job'   => 'Votre offre d\'emploi a été vérifiée avec succès et est maintenant publiée sur regiosuisse.ch. Vous pouvez consulter l\'offre via le lien suivant:',
                'button_event' => 'Afficher l\'événement',
                'button_job'   => 'Afficher l\'offre d\'emploi',
                'contact_text' => 'Si vous souhaitez apporter des modifications, veuillez nous contacter à:',
                'auto_message' => 'Ceci est un e-mail généré automatiquement. Merci de ne pas répondre à ce message.'
            ],
            'it' => [
                'title_event' => 'Il suo evento è stato pubblicato',
                'title_job'   => 'La sua offerta di lavoro è stata pubblicata',
                'description_event' => 'Il suo evento è stato verificato con successo ed è ora pubblicato su regiosuisse.ch. Può visualizzare l\'evento al seguente link:',
                'description_job'   => 'La sua offerta di lavoro è stata verificata con successo ed è ora pubblicata su regiosuisse.ch. Può visualizzare l\'offerta al seguente link:',
                'button_event' => 'Visualizza l\'evento',
                'button_job'   => 'Visualizza l\'offerta di lavoro',
                'contact_text' => 'Se desidera apportare modifiche, la preghiamo di contattarci all\'indirizzo:',
                'auto_message' => 'Questa è un\'e-mail generata automaticamente. Si prega di non rispondere a questo messaggio.'
            ]
        ];

        // Use the translations for the current language, fallback to German
        $texts = $translations[$language] ?? $translations['de'];

        // Choose the text chunk for event or job
        if ($type === CommunitySubmission::TYPE_EVENT) {
            $title       = $texts['title_event'];
            $description = $texts['description_event'];
            $button      = $texts['button_event'];
        } else {
            $title       = $texts['title_job'];
            $description = $texts['description_job'];
            $button      = $texts['button_job'];
        }

        $html = <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <img src="https://regiosuisse.ch/themes/contrib/regiosuisse_theme/logo.svg"
                alt="regiosuisse Logo"
                style="max-width: 200px; margin-bottom: 20px;">
            <h2 style="color: #333;">{$title}</h2>
            <p style="color: #333; line-height: 1.5;">
                {$description}
            </p>
            <p style="margin: 30px 0;">
                <a href="{$publicationUrl}"
                style="background: #b3ce46; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
                    {$button}
                </a>
            </p>
            <p style="color: #666; line-height: 1.5;">
                {$texts['contact_text']}<br>
                <a href="mailto:{$this->mailerFrom}" style="color: #b3ce46;">{$this->mailerFrom}</a>
            </p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
            <p style="color: #999; font-size: 0.9em;">
                {$texts['auto_message']}
            </p>
        </div>
        HTML;

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($contactEmail)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
} 
<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

class UserService {

    protected $em;
    protected $passwordHasher;
    protected $mailer;
    protected $mailerFrom;
    protected $host;
    protected $twig;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, Environment $twig, MailerInterface $mailer, string $mailerFrom, string $host)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailerFrom = $mailerFrom;
        $this->host = $host;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validateUserPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'email',
            'roles',
            'notifications',
        ])) !== true) {
            return $errors;
        }

        if(array_key_exists('password', $payload) && $payload['password']
            && (strlen($payload['password']) < 8 || trim($payload['password']) !== $payload['password'])) {
            return [
                [
                    'field' => 'password',
                ]
            ];
        }

        if(!array_key_exists('id', $payload)) {
            $existingUser = $this->em->getRepository(User::class)->findOneBy([
                'email' => $payload['email'],
            ]);

            if($existingUser) {
                return [
                    [
                        'field' => 'email',
                    ]
                ];
            }
        }

        return true;
    }

    public function createUser($payload)
    {
        $user = new User();

        $user->setCreatedAt(new \DateTime());

        $user = $this->applyUserPayload($payload, $user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function updateUser($user, $payload)
    {
        $user->setUpdatedAt(new \DateTime());

        $user = $this->applyUserPayload($payload, $user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function deleteUser($user)
    {
        $this->em->remove($user);
        $this->em->flush();

        return $user;
    }

    public function applyUserPayload($payload, User $user)
    {
        $user
            ->setEmail($payload['email'])
            ->setRoles($payload['roles'])
            ->setNotifications($payload['notifications'])
        ;

        if(array_key_exists('password', $payload) && $payload['password']) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $payload['password']));
        }

        return $user;
    }

    public function sendNotification (User $user, string $subject, string $title, string $body)
    {
        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject($subject)
            ->html($this->twig->render('emails/notification.html.twig', [
                'title' => $title,
                'body' => $body,
            ]))
        ;

        $this->mailer->send($email);
    }

}
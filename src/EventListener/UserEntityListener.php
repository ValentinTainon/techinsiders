<?php

namespace App\EventListener;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\Events;
use App\Service\PathService;
use App\Service\EmailService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
class UserEntityListener
{
    private bool $isGuestVerified = false;

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailService $emailService,
        private PathService $pathService,
    ) {}

    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        if (!empty($user->getPlainPassword())) {
            $this->processUserPassword($user);
        }
    }

    public function postPersist(User $user, PostPersistEventArgs $event): void
    {
        $this->emailService->sendEmailConfirmationToUser(
            $user,
            $user->getEmail(),
            $user->getUsername(),
            'confirm_email.subject',
            'registration_confirmation.html.twig',
            [],
            ['username' => $user->getUsername()]
        );

        if (!empty($user->getMotivations())) {
            $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'registration_request.subject',
                'new_user_registered.html.twig',
                [],
                [
                    'username' => $user->getUsername(),
                    'user_motivation' => $user->getMotivations()
                ]
            );
        }
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        if (!empty($user->getPlainPassword())) {
            $this->processUserPassword($user);
        }

        if ($user->getRole() === UserRole::GUEST && $user->isVerified()) {
            $this->isGuestVerified = true;
            $user->setRole(UserRole::USER);
        }
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
        if ($this->isGuestVerified) {
            $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'user_is_verified.subject',
                'user_is_verified.html.twig',
                [],
                ['username' => $user->getUsername()]
            );
        }
    }

    private function processUserPassword(User $user): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        )->eraseCredentials();
    }
}

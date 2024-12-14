<?php

namespace App\EventListener;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\Events;
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
    private array $userChangeSet;

    public function __construct(
        private EmailService $emailService,
        private UserPasswordHasherInterface $userPasswordHasher,
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
                'user_registered.html.twig',
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
        $this->userChangeSet = $event->getEntityChangeSet();

        if (!empty($user->getPlainPassword())) {
            $this->processUserPassword($user);
        }
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
        if ($this->isUserRoleChanged()) {
            $this->processEmail($user);
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

    private function isUserRoleChanged(): bool
    {
        return isset($this->userChangeSet) && array_key_exists('role', $this->userChangeSet);
    }

    private function processEmail(User $user): void
    {
        match (true) {
            $user->getRole() === UserRole::USER => $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'user_is_verified.subject',
                'user_is_verified.html.twig',
                ['%username%' => $user->getUsername()],
                ['username' => $user->getUsername()]
            ),
            $user->getRole() === UserRole::EDITOR => $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'assigned_to_editor.subject',
                'user_assigned_to_editor.html.twig',
                [],
                ['username' => $user->getUsername()]
            ),
            $this->userChangeSet['role'][0] === UserRole::ADMIN->value && $user->getRole() === UserRole::EDITOR
            => $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'reassigned_to_editor.subject',
                'user_reassigned_to_editor.html.twig',
                [],
                ['username' => $user->getUsername()]
            ),
            $user->getRole() === UserRole::ADMIN => $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'promoted_to_admin.subject',
                'editor_promoted_to_admin.html.twig',
                [],
                ['username' => $user->getUsername()]
            ),
        };
    }
}

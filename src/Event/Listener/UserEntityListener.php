<?php

namespace App\Event\Listener;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\Events;
use App\Service\EmailService;
use App\Event\EmailSendingFailedEvent;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: User::class)]
class UserEntityListener
{
    private array $userChangeSet;

    public function __construct(
        private EmailService $emailService,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
        if (!empty($user->getPlainPassword())) {
            $this->processUserPassword($user);
        }
    }

    public function postPersist(User $user, PostPersistEventArgs $event): void
    {
        $this->processEmailAfterPersist($user);
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
            $this->processEmailAfterUpdate($user);
        }
    }

    public function postRemove(User $user, PostRemoveEventArgs $event): void
    {
        $this->processEmailAfterDelete($user);
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

    private function processEmailAfterPersist(User $user): void
    {
        try {
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
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after creating a user."
                )
            );
        }
    }

    private function processEmailAfterUpdate(User $user): void
    {
        try {
            switch ($user->getRole()) {
                case UserRole::USER:
                    $this->emailService->sendEmailToAdmin(
                        $user->getEmail(),
                        $user->getUsername(),
                        'user_verified.subject',
                        'user_verified.html.twig',
                        ['%username%' => $user->getUsername()],
                        ['username' => $user->getUsername()]
                    );
                    break;
                case UserRole::EDITOR:
                    if ($this->userChangeSet['role'][0] === UserRole::ADMIN->value) {
                        $this->emailService->sendEmailToUser(
                            $user->getEmail(),
                            $user->getUsername(),
                            'reassigned_to_editor.subject',
                            'admin_reassigned_to_editor.html.twig',
                            [],
                            ['username' => $user->getUsername()]
                        );
                    } else {
                        $this->emailService->sendEmailToUser(
                            $user->getEmail(),
                            $user->getUsername(),
                            'assigned_to_editor.subject',
                            'user_assigned_to_editor.html.twig',
                            [],
                            ['username' => $user->getUsername()]
                        );
                    }
                    break;
                case UserRole::ADMIN:
                    $this->emailService->sendEmailToUser(
                        $user->getEmail(),
                        $user->getUsername(),
                        'promoted_to_admin.subject',
                        'editor_promoted_to_admin.html.twig',
                        [],
                        ['username' => $user->getUsername()]
                    );
                    break;
            }
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after updating a user."
                )
            );
        }
    }

    private function processEmailAfterDelete(User $user): void
    {
        try {
            $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'account_deleted.subject',
                'user_account_deleted.html.twig',
                [],
                ['username' => $user->getUsername()]
            );
            $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'user_account_deleted.subject',
                'user_deleted.html.twig',
                ['%username%' => $user->getUsername()],
                [
                    'username' => $user->getUsername(),
                    'user_deleted_at' => (new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris')))->format('d/m/Y à H:i')
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after deleting a user."
                )
            );
        }
    }
}

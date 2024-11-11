<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'processUserPassword', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'processUserPassword', entity: User::class)]
class UserPasswordProcessorListener
{
    public function processUserPassword(User $user, UserPasswordHasherInterface $userPasswordHasher, PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if (empty($user->getPlainPassword())) {
            return;
        }

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        )->eraseCredentials();
    }
}

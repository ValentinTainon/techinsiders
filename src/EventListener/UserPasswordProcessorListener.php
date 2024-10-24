<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'processPassword', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'processPassword', entity: User::class)]
class UserPasswordProcessorListener
{
    public function processPassword(User $user, UserPasswordHasherInterface $userPasswordHasher, PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if (!empty($user->getPlainPassword())) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            )
            ->eraseCredentials();
        }
    }
}
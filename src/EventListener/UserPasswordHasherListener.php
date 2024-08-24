<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersistOrUpdate', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'prePersistOrUpdate', entity: User::class)]
class UserPasswordHasherListener
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }
    
    public function prePersistOrUpdate(User $user, PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        $this->processPassword($user);
    }

    private function processPassword(User $user): void
    {
        if (!empty($user->getPlainPassword())) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            )
            ->eraseCredentials();
        }
    }
}
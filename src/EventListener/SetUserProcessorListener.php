<?php

namespace App\EventListener;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(Events::prePersist)]
class SetUserProcessorListener
{
    public function __construct(private Security $security) {}

    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$this->isEntitySupported($entity)) {
            return;
        }

        $entity->setUser($this->security->getUser());
    }

    private function isEntitySupported(object $entity): bool
    {
        return $entity instanceof Post || $entity instanceof Comment;
    }
}

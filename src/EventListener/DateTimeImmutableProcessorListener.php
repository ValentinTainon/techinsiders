<?php

namespace App\EventListener;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(Events::prePersist)]
#[AsDoctrineListener(Events::preUpdate)]
class DateTimeImmutableProcessorListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$this->isEntitySupported($entity)) {
            return;
        }

        $entity->setCreatedAt($this->dateTimeImmutableNow());
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$this->isEntitySupported($entity)) {
            return;
        }

        $entity->setUpdatedAt($this->dateTimeImmutableNow());
    }

    private function isEntitySupported(object $entity): bool
    {
        return $entity instanceof Post || $entity instanceof Comment;
    }

    private function dateTimeImmutableNow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris'));
    }
}

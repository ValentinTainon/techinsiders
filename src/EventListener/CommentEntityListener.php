<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Comment;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Comment::class)]
class CommentEntityListener
{
    public function __construct(private Security $security) {}

    public function prePersist(Comment $comment, PrePersistEventArgs $event): void
    {
        $comment->setUser($this->security->getUser())
            ->setCreatedAt(
                new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris'))
            );
    }

    public function preUpdate(Comment $comment, PreUpdateEventArgs $event): void
    {
        $comment->setUpdatedAt(
            new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris'))
        );
    }
}

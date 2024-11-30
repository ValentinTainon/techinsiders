<?php

namespace App\EventSubscriber;

use App\Entity\Post;
use App\Enum\UserRole;
use App\Enum\PostStatus;
use App\Service\EmailService;
use App\Service\PathService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;

class EasyAdminPostSubscriber implements EventSubscriberInterface
{
    private PostStatus $previousPostStatus;

    public function __construct(
        private Security $security,
        private SluggerInterface $slugger,
        private EmailService $emailService,
        private PathService $pathService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['prePersist'],
            AfterEntityPersistedEvent::class => ['postPersist'],
            BeforeEntityUpdatedEvent::class => ['preUpdate'],
            AfterEntityUpdatedEvent::class => ['postUpdate'],
        ];
    }

    public function prePersist(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;

        $post->setUser($this->security->getUser())
            ->setSlug($this->slugger->slug($post->getTitle()))
            ->setCreatedAt(new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris')));
    }

    public function postPersist(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;

        if (!$this->security->isGranted(UserRole::SUPER_ADMIN->value)) {
            $this->processEmail($post);
        }
    }

    public function preUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;
        $this->previousPostStatus = $post->getStatus();
        $postTitleSlug = $this->slugger->slug($post->getTitle());

        if ($post->getSlug() !== $postTitleSlug) {
            $post->setSlug($postTitleSlug);
        }

        $post->setUpdatedAt(new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris')));
    }

    public function postUpdate(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;

        if (!$this->security->isGranted(UserRole::SUPER_ADMIN->value) && $this->isPostStatusChange($post)) {
            $this->processEmail($post);
        }
    }

    private function isPostStatusChange(Post $post): bool
    {
        if (!isset($this->previousPostStatus)) {
            return false;
        }

        return $post->getStatus() !== $this->previousPostStatus;
    }

    private function processEmail(Post $post): void
    {
        $user = $post->getUser();

        match ($post->getStatus()) {
            PostStatus::DRAFTED => $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'post.draft.subject',
                'post_drafted.html.twig',
                [],
                [
                    'username' => $user->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
            PostStatus::READY_FOR_REVIEW => $this->emailService->sendEmailToAdmin(
                $user->getEmail(),
                $user->getUsername(),
                'post.ready_for_review.subject',
                'post_ready_for_review.html.twig',
                [],
                [
                    'username' => $user->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
            PostStatus::PUBLISHED => $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'post.published.subject',
                'post_published.html.twig',
                [],
                [
                    'username' => $user->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
            PostStatus::REJECTED => $this->emailService->sendEmailToUser(
                $user->getEmail(),
                $user->getUsername(),
                'post.rejected.subject',
                'post_rejected.html.twig',
                [],
                [
                    'username' => $user->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
        };
    }
}

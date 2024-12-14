<?php

namespace App\EventSubscriber;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\PostStatus;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class EasyAdminPostSubscriber implements EventSubscriberInterface
{
    private array $postChangeSet;

    public function __construct(
        private Security $security,
        private SluggerInterface $slugger,
        private EmailService $emailService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
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

        // $this->processEmail($post, $post->getUser());
    }

    public function preUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;

        $this->postChangeSet = $this->entityManager->getUnitOfWork()->getOriginalEntityData($post);

        if ($this->isPostTitleChanged()) {
            $post->setSlug($this->slugger->slug($post->getTitle()));
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

        // if ($this->isPostStatusChanged()) {
        //     $this->processEmail($post, $post->getUser());
        // }
    }

    private function isPostTitleChanged(): bool
    {
        return isset($this->postChangeSet) && array_key_exists('title', $this->postChangeSet);
    }

    private function isPostStatusChanged(): bool
    {
        return isset($this->postChangeSet) && array_key_exists('status', $this->postChangeSet);
    }

    private function processEmail(Post $post, User $author): void
    {
        match (true) {
            $author->getRole() !== UserRole::SUPER_ADMIN &&
                ($post->getStatus() === PostStatus::DRAFTED || $post->getStatus() === PostStatus::READY_FOR_REVIEW)
            => $this->emailService->sendEmailToAdmin(
                $author->getEmail(),
                $author->getUsername(),
                'new_post_created.subject',
                'post_created.html.twig',
                ['%author%' => $author->getUsername(), '%status%' => $post->getStatus()->label($this->translator)],
                [
                    'author' => $author->getUsername(),
                    'post_title' => $post->getTitle(),
                    'post_status' => $post->getStatus()->label($this->translator),
                    'post_created_at' => $post->getCreatedAt(),
                ]
            ),
            $author->getRole() !== UserRole::SUPER_ADMIN && $post->getStatus() === PostStatus::PUBLISHED
            => $this->emailService->sendEmailToUser(
                $author->getEmail(),
                $author->getUsername(),
                'post.published.subject',
                'post_published.html.twig',
                [],
                [
                    'username' => $author->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
            $author->getRole() !== UserRole::SUPER_ADMIN && $post->getStatus() === PostStatus::REJECTED
            => $this->emailService->sendEmailToUser(
                $author->getEmail(),
                $author->getUsername(),
                'post.rejected.subject',
                'post_rejected.html.twig',
                [],
                [
                    'username' => $author->getUsername(),
                    'post_title' => $post->getTitle()
                ]
            ),
        };
    }
}

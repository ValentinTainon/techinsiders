<?php

namespace App\Event\Subscriber;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\PostStatus;
use App\Service\EmailService;
use App\Event\EmailSendingFailedEvent;
use App\Event\EmailSendingSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;

class EasyAdminPostSubscriber implements EventSubscriberInterface
{
    private array $originalPostData;

    public function __construct(
        private Security $security,
        private SluggerInterface $slugger,
        private EmailService $emailService,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['prePersist'],
            AfterEntityPersistedEvent::class => ['postPersist'],
            BeforeEntityUpdatedEvent::class => ['preUpdate'],
            AfterEntityUpdatedEvent::class => ['postUpdate'],
            AfterEntityDeletedEvent::class => ['postDelete'],
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
        $author = $post->getUser();

        if ($author !== null) {
            $this->processEmailAfterPersist($post, $author);
        }
    }

    public function preUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;

        $this->originalPostData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($post);

        if ($this->isPostTitleChanged($post->getTitle())) {
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
        $author = $post->getUser();

        if ($author !== null && $this->isPostStatusChanged($post->getStatus())) {
            $this->processEmailAfterUpdate($post, $author);
        }
    }

    public function postDelete(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Post)) {
            return;
        }

        $post = &$entity;
        $author = $post->getUser();

        if ($author !== null) {
            $this->processEmailAfterDelete($post, $author);
        }
    }

    private function isPostTitleChanged(string $postTitle): bool
    {
        return isset($this->originalPostData) &&
            array_key_exists('title', $this->originalPostData) &&
            $this->originalPostData['title'] !== $postTitle;
    }

    private function isPostStatusChanged(PostStatus $postStatus): bool
    {
        return isset($this->originalPostData) &&
            array_key_exists('status', $this->originalPostData) &&
            $this->originalPostData['status'] !== $postStatus;
    }

    private function processEmailAfterPersist(Post $post, User $author): void
    {
        try {
            if ($post->getStatus() === PostStatus::DRAFTED || $post->getStatus() === PostStatus::READY_FOR_REVIEW) {
                if ($author->getRole() !== UserRole::SUPER_ADMIN) {
                    $this->emailService->sendEmailToAdmin(
                        $author->getEmail(),
                        $author->getUsername(),
                        'new_post_created.subject',
                        'post_created.html.twig',
                        ['%post_author%' => $author->getUsername()],
                        [
                            'post_author' => $author->getUsername(),
                            'post_title' => $post->getTitle(),
                            'post_status' => $post->getStatus()->label($this->translator),
                            'post_created_at' => $post->getCreatedAt()->format('d/m/Y à H:i'),
                        ]
                    );
                }

                $this->eventDispatcher->dispatch(
                    new EmailSendingSuccessEvent(
                        $post->getStatus() === PostStatus::DRAFTED ?
                            $this->translator->trans('author_post_drafted', [], 'flashes') :
                            $this->translator->trans('author_post_ready_for_review', [], 'flashes')
                    )
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after creating a post."
                )
            );
        }
    }

    private function processEmailAfterUpdate(Post $post, User $author): void
    {
        try {
            switch ($post->getStatus()) {
                case PostStatus::DRAFTED:
                case PostStatus::READY_FOR_REVIEW:
                    if ($author->getRole() !== UserRole::SUPER_ADMIN) {
                        $this->emailService->sendEmailToAdmin(
                            $author->getEmail(),
                            $author->getUsername(),
                            'post_status_edited.subject',
                            'post_status_edited.html.twig',
                            ['%post_author%' => $author->getUsername()],
                            [
                                'post_author' => $author->getUsername(),
                                'post_title' => $post->getTitle(),
                                'post_status' => $post->getStatus()->label($this->translator),
                                'post_updated_at' => $post->getUpdatedAt()->format('d/m/Y à H:i'),
                            ]
                        );

                        $this->eventDispatcher->dispatch(
                            new EmailSendingSuccessEvent(
                                $post->getStatus() === PostStatus::DRAFTED ?
                                    $this->translator->trans('author_post_drafted', [], 'flashes') :
                                    $this->translator->trans('author_post_ready_for_review', [], 'flashes')
                            )
                        );
                    }
                    break;
                case PostStatus::PUBLISHED:
                    if ($author->getRole() !== UserRole::SUPER_ADMIN) {
                        $this->emailService->sendEmailToUser(
                            $author->getEmail(),
                            $author->getUsername(),
                            'post.published.subject',
                            'post_published.html.twig',
                            [],
                            [
                                'post_author' => $author->getUsername(),
                                'post_title' => $post->getTitle(),
                                'post_updated_at' => $post->getUpdatedAt()->format('d/m/Y à H:i'),
                            ]
                        );
                    }

                    $this->eventDispatcher->dispatch(
                        new EmailSendingSuccessEvent(
                            $this->translator->trans('author_post_published', ['%author%' => $author->getUsername()], 'flashes')
                        )
                    );
                    break;
                case PostStatus::REJECTED:
                    if ($author->getRole() !== UserRole::SUPER_ADMIN) {
                        $this->emailService->sendEmailToUser(
                            $author->getEmail(),
                            $author->getUsername(),
                            'post.rejected.subject',
                            'post_rejected.html.twig',
                            [],
                            [
                                'post_author' => $author->getUsername(),
                                'post_title' => $post->getTitle()
                            ]
                        );
                    }

                    $this->eventDispatcher->dispatch(
                        new EmailSendingSuccessEvent(
                            $this->translator->trans('author_post_rejected', ['%author%' => $author->getUsername()], 'flashes')
                        )
                    );
                    break;
            }
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after updating a post."
                )
            );
        }
    }

    private function processEmailAfterDelete(Post $post, User $author): void
    {
        try {
            if ($author->getRole() !== UserRole::SUPER_ADMIN) {
                $this->emailService->sendEmailToAdmin(
                    $author->getEmail(),
                    $author->getUsername(),
                    'post_deleted.subject',
                    'post_deleted.html.twig',
                    ['%post_author%' => $author->getUsername()],
                    [
                        'post_author' => $author->getUsername(),
                        'post_title' => $post->getTitle(),
                        'post_deleted_at' => (new \DateTimeImmutable(timezone: new \DateTimeZone('Europe/Paris')))->format('d/m/Y à H:i'),
                    ]
                );
            }

            $this->eventDispatcher->dispatch(
                new EmailSendingSuccessEvent(
                    $this->translator->trans('author_post_deleted', [], 'flashes')
                )
            );
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent(
                    "An error occurred while sending an email after deleting a post."
                )
            );
        }
    }
}

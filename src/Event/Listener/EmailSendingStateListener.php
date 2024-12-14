<?php

namespace App\Event\Listener;

use App\Event\EmailSendingFailedEvent;
use App\Event\EmailSendingSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: EmailSendingSuccessEvent::class, method: 'onEmailSendingSuccess')]
#[AsEventListener(event: EmailSendingFailedEvent::class, method: 'onEmailSendingFailed')]
final class EmailSendingStateListener
{
    public function __construct(private RequestStack $requestStack) {}

    public function onEmailSendingSuccess(EmailSendingSuccessEvent $event): void
    {
        if ($event instanceof EmailSendingSuccessEvent) return;

        $request = $this->requestStack->getCurrentRequest();

        if ($request && $request->getSession()) {
            $this->requestStack->getSession()->getFlashBag()->add(
                'success',
                $event->getSuccessMessage()
            );
        }
    }

    public function onEmailSendingFailed(EmailSendingFailedEvent $event): void
    {
        if ($event instanceof EmailSendingFailedEvent) return;

        $request = $this->requestStack->getCurrentRequest();

        if ($request && $request->getSession()) {
            $this->requestStack->getSession()->getFlashBag()->add(
                'danger',
                $event->getErrorMessage()
            );
        }
    }
}

<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use App\Event\EmailSendingFailedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailService
{
    private const string ADMIN_TEMPLATES_DIR = 'emails/admin/';
    private const string USER_TEMPLATES_DIR = 'emails/user/';

    public function __construct(
        private MailerInterface $mailer,
        private EmailVerifier $emailVerifier,
        private ParameterBagInterface $params,
        private TranslatorInterface $translator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function sendEmailToAdmin(
        string $emailFrom,
        string $usernameFrom,
        string $subject,
        string $template,
        array $subjectTransParams = [],
        array $context = []
    ): void {
        try {
            $this->mailer->send(
                (new TemplatedEmail())
                    ->from(new Address($emailFrom, $usernameFrom))
                    ->to(
                        new Address(
                            $this->params->get('app_contact_email'),
                            $this->params->get('app_name')
                        )
                    )
                    ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
                    ->htmlTemplate(self::ADMIN_TEMPLATES_DIR . $template)
                    ->context($context)
            );
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent($e->getMessage())
            );
        }
    }

    public function sendEmailToUser(
        string $emailTo,
        string $usernameTo,
        string $subject,
        string $template,
        array $subjectTransParams = [],
        array $context = []
    ): void {
        try {
            $this->mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($emailTo, $usernameTo))
                    ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
                    ->htmlTemplate(self::USER_TEMPLATES_DIR . $template)
                    ->context($context)
            );
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent($e->getMessage())
            );
        }
    }

    public function sendEmailConfirmationToUser(
        User $user,
        string $emailTo,
        string $usernameTo,
        string $subject,
        string $template,
        array $subjectTransParams = [],
        array $context = []
    ): void {
        try {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->to(new Address($emailTo, $usernameTo))
                    ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
                    ->htmlTemplate(self::USER_TEMPLATES_DIR . $template)
                    ->context($context)
            );
        } catch (TransportExceptionInterface $e) {
            $this->eventDispatcher->dispatch(
                new EmailSendingFailedEvent($e->getMessage())
            );
        }
    }
}

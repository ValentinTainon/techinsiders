<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer, 
        private EmailVerifier $emailVerifier,
        private TranslatorInterface $translator
    )
    {
    }

    public function sendTemplatedEmail(
        string $emailTo,
        string $usernameTo,
        string $subject,
        string $template,
        array $context = [],
        bool $fromSuperAdmin = true,
        string $emailFrom = '',
        string $usernameFrom = ''
    ): void
    {
        $templatedEmail = (new TemplatedEmail())
            ->to(new Address($emailTo, $usernameTo))
            ->subject($this->translator->trans(id: $subject, domain: 'emails'))
            ->htmlTemplate($template)
            ->context($context);

        if (!$fromSuperAdmin) {
            $templatedEmail->from(new Address($emailFrom, $usernameFrom));
        }

        $this->mailer->send($templatedEmail);
    }

    public function sendRegistrationConfirmationEmail(
        User $user,
        string $emailTo,
        string $usernameTo,
        string $subject,
        string $template,
        array $context = []
    ): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email', 
            $user,
            (new TemplatedEmail())
                ->to(new Address($emailTo, $usernameTo))
                ->subject($this->translator->trans(id: $subject, domain: 'emails'))
                ->htmlTemplate($template)
                ->context($context)
        );
    }
}
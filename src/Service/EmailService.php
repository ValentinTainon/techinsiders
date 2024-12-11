<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
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
    ) {}

    public function getAdminTemplatePath(string $template): string
    {
        return self::ADMIN_TEMPLATES_DIR . $template;
    }

    public function getUserTemplatePath(string $template): string
    {
        return self::USER_TEMPLATES_DIR . $template;
    }

    public function sendEmailToAdmin(
        string $emailFrom,
        string $usernameFrom,
        string $subject,
        string $template,
        array $subjectTransParams = [],
        array $context = []
    ): void {
        $templatedEmail = (new TemplatedEmail())
            ->from(new Address($emailFrom, $usernameFrom))
            ->to(
                new Address(
                    $this->params->get('app_contact_email'),
                    $this->params->get('app_name')
                )
            )
            ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
            ->htmlTemplate($this->getAdminTemplatePath($template))
            ->context($context);

        $this->mailer->send($templatedEmail);
    }

    public function sendEmailToUser(
        string $emailTo,
        string $usernameTo,
        string $subject,
        string $template,
        array $subjectTransParams = [],
        array $context = []
    ): void {
        $templatedEmail = (new TemplatedEmail())
            ->to(new Address($emailTo, $usernameTo))
            ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
            ->htmlTemplate($this->getUserTemplatePath($template))
            ->context($context);

        $this->mailer->send($templatedEmail);
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
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->to(new Address($emailTo, $usernameTo))
                ->subject($this->translator->trans($subject, $subjectTransParams, 'emails'))
                ->htmlTemplate($this->getUserTemplatePath($template))
                ->context($context)
        );
    }
}

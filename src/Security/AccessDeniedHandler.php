<?php

namespace App\Security;

use function Symfony\Component\Translation\t;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator,  private Security $security) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $message = match (true) {
            !$user->isVerified() => t('access_denied.user_has_email_not_verified', [], 'flashes'),
            !$this->security->isGranted(UserRole::EDITOR->value) => t('access_denied.user_has_not_permission', [], 'flashes'),
            default => t('access_denied', [], 'flashes')
        };

        $request->getSession()->getFlashBag()->add('danger', $message);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}

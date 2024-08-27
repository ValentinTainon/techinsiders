<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator,  private Security $security)
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $hasEditorRole = $this->security->isGranted('ROLE_EDITOR');
        $isUserVerified = $this->security->getUser()->isVerified();

        $message = match (true) {
            !$hasEditorRole && !$isUserVerified => t('access_denied.user_has_not_permission_and_email_not_verified', [], 'flashes'),
            !$hasEditorRole => t('access_denied.user_has_not_permission', [], 'flashes'),
            !$isUserVerified => t('access_denied.user_has_email_not_verified', [], 'flashes'),
            default => t('access_denied.user_have_to_login', [], 'flashes')
        };

        $request->getSession()->getFlashBag()->add('note', $message);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
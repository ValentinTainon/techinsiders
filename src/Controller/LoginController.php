<?php

namespace App\Controller;

use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,

            // OPTIONAL parameters to customize the login form:

            // the translation_domain to use (define this option only if you are
            // rendering the login template in a regular Symfony controller; when
            // rendering it from an EasyAdmin Dashboard this is automatically set to
            // the same domain as the rest of the Dashboard)
            // 'translation_domain' => 'admin',

            // by default EasyAdmin displays a black square as its default favicon;
            // use this method to display a custom favicon: the given path is passed
            // "as is" to the Twig asset() function:
            // <link rel="shortcut icon" href="{{ asset('...') }}">
            'favicon_path' => '/favicon-admin.svg',

            // the title visible above the login form (define this option only if you are
            // rendering the login template in a regular Symfony controller; when rendering
            // it from an EasyAdmin Dashboard this is automatically set as the Dashboard title)
            'page_title' => $this->getParameter('app.name'),

            // the string used to generate the CSRF token. If you don't define
            // this parameter, the login form won't include a CSRF token
            'csrf_token_intention' => 'authenticate',

            // the URL users are redirected to after the login (default: '/admin')
            'target_path' => $this->generateUrl('admin'),

            // the label displayed for the username form field (the |trans filter is applied to it)
            'username_label' => t('username.label', [], 'forms'),

            // the label displayed for the password form field (the |trans filter is applied to it)
            'password_label' => t('password.label', [], 'forms'),

            // the label displayed for the Sign In form button (the |trans filter is applied to it)
            'sign_in_label' => t('sign_in.label', [], 'forms'),

            // the 'name' HTML attribute of the <input> used for the username field (default: '_username')
            'username_parameter' => '_username',

            // the 'name' HTML attribute of the <input> used for the password field (default: '_password')
            'password_parameter' => '_password',

            // whether to enable or not the "forgot password?" link (default: false)
            'forgot_password_enabled' => true,

            // the path (i.e. a relative or absolute URL) to visit when clicking the "forgot password?" link (default: '#')
            'forgot_password_path' => $this->generateUrl('app_forgot_password_request'),

            // the label displayed for the "forgot password?" link (the |trans filter is applied to it)
            'forgot_password_label' => t('forgot.password.label', [], 'forms'),

            // whether to enable or not the "remember me" checkbox (default: false)
            'remember_me_enabled' => true,

            // remember me name form field (default: '_remember_me')
            'remember_me_parameter' => '_remember_me',

            // whether to check by default the "remember me" checkbox (default: false)
            'remember_me_checked' => false,

            // the label displayed for the remember me checkbox (the |trans filter is applied to it)
            'remember_me_label' => t('remember_me.label', [], 'forms'),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

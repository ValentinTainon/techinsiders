<?php

namespace App\Controller;

use function Symfony\Component\Translation\t;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setMotivations($form->get('motivations')->getData());
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('validate_email_after_registration', [], 'flashes'));

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('bundles/EasyAdminBundle/page/register.html.twig', [
            'registrationForm' => $form,

            // "as is" to the Twig asset() function:
            // <link rel="shortcut icon" href="{{ asset('...') }}">
            'favicon_path' => '/favicon-admin.svg',
            'page_title' => t('login_register_page.registration', [], 'forms'),
            'username_label' => t('login_register_page.username', [], 'forms'),
            'email_label' => t('login_register_page.email', [], 'forms'),
            'motivations_label' => t('login_register_page.motivations', [], 'forms'),
            'sign_in_label' => t('login_register_page.sign_in', [], 'forms'),
            'sign_up_label' => t('login_register_page.sign_up', [], 'forms'),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator, EmailVerifier $emailVerifier): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', $translator->trans('error_during_email_verification', [], 'flashes'));
        }

        $this->addFlash('success', $translator->trans('email_verified_and_editor_membership_request_being_processed', [], 'flashes'));

        return $this->redirectToRoute('app_homepage');
    }
}

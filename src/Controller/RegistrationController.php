<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailService;
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
    public function register(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, EmailService $emailService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

            // Send email to the admin
            $emailService->sendTemplatedEmail(
                $this->getParameter('app_contact_email'),
                $this->getParameter('app_name'),
                'registration_request.subject',
                'registration/admin_email.html.twig',
                [
                    'username' => $user->getUsername(),
                    'user_motivation' => $form->get('userMotivation')->getData()
                ],
                false,
                $user->getEmail(),
                $user->getUsername()
            );

            // Generate a signed url and email it to the user
            $emailService->sendRegistrationConfirmationEmail(
                $user,
                $user->getEmail(),
                $user->getUsername(),
                'confirm_email.subject',
                'registration/confirmation_email.html.twig',
                ['username' => $user->getUsername()]
            );

            $this->addFlash('success', $translator->trans('validate_email_after_registration', [], 'flashes'));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
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

        $this->addFlash('success', $translator->trans('email_verified_and_registration_request_being_processed', [], 'flashes'));

        return $this->redirectToRoute('app_home');
    }
}

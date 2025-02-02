<?php

namespace App\Controller\Front;

use function Symfony\Component\Translation\t;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\Front\RegistrationFormType;
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
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setMotivations(
                $form->get('motivations')->getData()
            );
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('validate_email_after_registration', [], 'flashes'));

            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator, EmailVerifier $emailVerifier): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        if (!$user->isVerified()) {
            $this->addFlash('error', $translator->trans('error_during_email_verification', [], 'flashes'));
        }

        $this->addFlash('success', $translator->trans('email_verified_and_editor_membership_request_being_processed', [], 'flashes'));

        return $this->redirectToRoute('homepage');
    }
}

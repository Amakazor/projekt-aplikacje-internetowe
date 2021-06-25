<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use App\Service\UserCreationMailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TranslatorInterface $translator
     * @param UserCreationMailer $userCreationMailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, TranslatorInterface $translator, UserCreationMailer $userCreationMailer): Response
    {
        if ($this->isGranted('ROLE_USER')) return $this->redirectToRoute('app_user_data');

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $status = FALSE;

            if ($form->isValid()) {
                $user = $form->getData();
                $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
                $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->persist($user->getCompany());

                $entityManager->persist($user);
                $entityManager->flush();

                $userCreationMailer->sendVerificationEmail($user);

                $status = TRUE;
            }

            if ($status) {
                $this->addFlash(
                    'success',
                    'api.message.register.success'
                );
                return $this->redirectToRoute('app_index_index');
            } else {
                $this->addFlash(
                    'error',
                    'api.message.register.error'
                );
            }
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

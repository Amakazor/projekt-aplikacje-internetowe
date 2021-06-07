<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/verify", name="user_mail_confirmation")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param VerifyEmailHelperInterface $helper
     * @return Response
     */
    public function verifyEmail(Request $request, UserRepository $userRepository, VerifyEmailHelperInterface $helper): Response
    {
        $id = $request->get('id');

        if ($id === null) return $this->redirectToRoute('app_index_index');

        $user = $userRepository->find($id);

        if ($user === null) return $this->redirectToRoute('app_index_index');

        try {
            $helper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());

            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your e-mail address has been verified.');

        return $this->redirectToRoute('app_index_index');
    }
}

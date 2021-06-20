<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
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

    /**
     * @Route("/user/profile", name="app_user_data")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function data(Request $request, UserRepository $userRepository, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $userRepository->findOneBy(['username' => $this->getUser()->getUsername()]);

        $builder = $this->createFormBuilder(null, ['attr' => ['class' => 'userPasswordForm adminForm']]);
        $form = $builder->add('old_password', PasswordType::class, [
            'label' => 'user.data.password.old',
        ]) -> add('new_password', PasswordType::class, [
            'label' => 'user.data.password.new',
        ]) -> add('new_password_repeat', PasswordType::class, [
            'label' => 'user.data.password.repeat'
        ]) ->add('save', SubmitType::class, [
            'label' => 'form.save'
        ]) -> getForm();

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($translator, $passwordEncoder, $user) {
            if ($event->getForm()->getData()['old_password'] == $event->getForm()->getData()['new_password'] || $event->getForm()->getData()['old_password'] == $event->getForm()->getData()['new_password_repeat']) {
                $event->getForm()->addError(new FormError($translator->trans('user.data.error.samePassword')));
            }
            if ($event->getForm()->getData()['new_password'] != $event->getForm()->getData()['new_password_repeat']) {
                $event->getForm()->addError(new FormError($translator->trans('user.data.error.passwordNotMatching')));
            }
            if ($user->getPassword() != $passwordEncoder->isPasswordValid($user, $event->getForm()->getData()['old_password'])) {
                var_dump($user->getPassword());
                var_dump($passwordEncoder->encodePassword($user, $event->getForm()->getData()['old_password']));
                $event->getForm()->addError(new FormError($translator->trans('user.data.error.passwordOldWrong')));
            }
        });
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user->setPlainPassword($form->getData()['new_password']);
                $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    $translator->trans('api.message.password.change.success')
                );
            }
        }

        return $this->render('userData.html.twig', ['user' => $user, 'form' => $form->createView()]);
    }
}

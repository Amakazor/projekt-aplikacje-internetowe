<?php
namespace App\Controller;

use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
            'label' => 'form.save',
            'attr' => [
                'class' => 'button button--primary'
            ]
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

    /**
     * @Route("/user/reservations", name="app_user_reservations")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ReservationRepository $reservationRepository
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function userReservations(Request $request, UserRepository $userRepository, ReservationRepository $reservationRepository): Response
    {
        $fields = [
            [
                'name' => 'start',
                'sortable' => TRUE,
                'sort' => 'reservation.start',
                'width' => '10%'
            ],
            [
                'name' => 'end',
                'sortable' => TRUE,
                'sort' => 'reservation.end',
                'width' => '10%'
            ],
            [
                'name' => 'car',
                'sortable' => TRUE,
                'sort' => 'car.brand',
                'width' => '10%'
            ],
            [
                'name' => 'action',
                'sortable' => FALSE,
                'width' => '10%'
            ]
        ];

        $per_page = (int)($request->query->get('per_page') ?? 5);
        $page = (int)($request->query->get('page') ?? 1);
        $order = $request->query->get('order') ?? 'id';
        $order_direction = $request->query->get('direction') ?? 'ASC';

        $user = $userRepository->findOneBy(['username' => $this->getUser()->getUsername()]);
        $company = $user->getCompany();
        $pages = ceil((int)$reservationRepository->getCountWithUserAndCar($company, null, $user) / $per_page);

        $get_name = function($value) {return $value['name'];};
        $get_sort = function($value) {return !empty($value['sort']) ? $value['sort'] : null;};
        if (!in_array($order, array_map($get_name, $fields)) && !in_array($order, array_map($get_sort, $fields))) $order = 'reservation.start';
        if (!in_array($order_direction, ['ASC', 'DESC'])) $order_direction = 'ASC';
        $page = max(1, min($page, $pages));

        $reservations = [];
        foreach ($reservationRepository->reservationPagination($company, $per_page, $page, $order, $order_direction, null, $user) as $reservationData) {
            $reservations[] = [
                'id' => $reservationData->getId(),
                'start' => $reservationData->getStart()->format('Y-m-d H:i'),
                'end' => $reservationData->getEnd()->format('Y-m-d H:i'),
                'car' => $reservationData->getCar()->getBrand() . ' ' . $reservationData->getCar()->getModel(),
            ];
        }

        return $this->render('userReservations.html.twig', ['fields' => $fields, 'reservations' => $reservations, 'current_order' => $order, 'current_direction' => $order_direction, 'page' => $page, 'pages' => $pages, 'per_page' => $per_page]);
    }

    /**
     * @Route("user/reservations/delete/{id}", methods={"DELETE"}, name="app_user_reservations_delete")
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete_reservation(int $id, ReservationRepository $reservationRepository, UserRepository $userRepository, TranslatorInterface $translator) : Response
    {
        $status = FALSE;
        try {
            if ($reservationRepository->doesBelongToCompany($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $id)) {
                $status = $reservationRepository->removeCompanyReservation($id);
            }
        } catch (NoResultException $e) {
            $status = FALSE;
        } catch (NonUniqueResultException $e) {
            $status = FALSE;
        }

        if ($status) {
            $this->addFlash(
                'success',
                $translator->trans('api.message.reservation.delete.success') . $id
            );
        } else {
            $this->addFlash(
                'error',
                $translator->trans('api.message.reservation.delete.error') . $id
            );
        }

        return new Response($status);
    }
}

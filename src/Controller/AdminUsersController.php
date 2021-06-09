<?php
namespace App\Controller;
use App\Entity\User;
use App\Form\UserAdminType;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use App\Service\UserCreationMailer;
use App\Service\UserCreator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AdminUsersController extends AbstractController
{
    /**
     * @Route("/admin/users/", name="app_admin_users")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function users(UserRepository $userRepository, Request $request) : Response
    {
        $fields = [
            [
                'name' => 'id',
                'sortable' => TRUE,
                'width' => '7%'
            ],
            [
                'name' => 'username',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'firstname',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'lastname',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'email',
                'sortable' => TRUE,
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

        $pages = ceil((int)$userRepository->paginationCount($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany()) / $per_page);

        $get_name = function($value) {return $value['name'];};
        if (!in_array($order, array_map($get_name, $fields))) $order = 'id';
        if (!in_array($order_direction, ['ASC', 'DESC'])) $order_direction = 'ASC';
        $page = max(1, min($page, $pages));

        $users = [];
        foreach ($userRepository->Pagination($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $per_page, $page, $order, $order_direction) as $user_data) {
            $users[] = [
                'id' => $user_data->getId(),
                'username' => $user_data->getUsername(),
                'firstname' => $user_data->getFirstname(),
                'lastname' => $user_data->getLastname(),
                'email' => $user_data->getEmail(),
            ];
        }

        return $this->render('adminUsers.html.twig', ['fields' => $fields, 'users' => $users, 'current_order' => $order, 'current_direction' => $order_direction, 'page' => $page, 'pages' => $pages, 'per_page' => $per_page]);
    }

    /**
     * @Route("admin/users/delete/{id}", methods={"DELETE"}, name="app_admin_users_delete")
     * @param int $id
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete_user(int $id, UserRepository $userRepository, TranslatorInterface $translator) : Response
    {
        if ($id !=  $this->getUser()->getId()) {
            $status = $userRepository->removeCompanyUser($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $id);

            if ($status) {
                $this->addFlash(
                    'success',
                    $translator->trans('api.message.users.delete.success') . $id
                );
            } else {
                $this->addFlash(
                    'error',
                    $translator->trans('api.message.users.delete.error') . $id
                );
            }
            return new Response($status);
        }
        else {
            $this->addFlash(
                'error',
                $translator->trans('api.message.users.delete.currentError')
            );
            return new Response(FALSE);
        }
    }

    /**
     * @Route("admin/user/{id}", name="app_admin_user", requirements={"id"="\d+"})
     * @param Request $request
     * @param CarRepository $carRepository
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param UserCreator $userCreator
     * @param UserCreationMailer $userCreationMailer
     * @param int $id
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function user(Request $request, CarRepository $carRepository, UserRepository $userRepository, TranslatorInterface $translator, UserCreator $userCreator, UserCreationMailer $userCreationMailer, int $id = 0) : Response
    {
        $isCreation = $id == 0;

        $user = ($isCreation || !$userRepository->doesUserIdBelongToCompany($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $id))
            ? new User()
            : $carRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $status = FALSE;

            if ($form->isValid()) {
                $user = $form->getData();

                if ($isCreation) $user = $userCreator->finishUserCreation($user);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                if ($isCreation) $userCreationMailer->sendVerificationEmail($user);

                $id = $user->getId();

                $status = TRUE;
            }

            if ($status) {
                $this->addFlash(
                    'success',
                    $isCreation ? $translator->trans('api.message.users.add.success') . $id : $translator->trans('api.message.users.change.success') . $id
                );
                return $this->redirectToRoute('app_admin_users');
            } else {
                $this->addFlash(
                    'error',
                    $isCreation ? $translator->trans('api.message.users.add.error') . $id : $translator->trans('api.message.users.change.error') . $id
                );
            }
        }

        return $this->render('adminUser.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
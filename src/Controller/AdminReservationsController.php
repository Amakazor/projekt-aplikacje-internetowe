<?php
namespace App\Controller;
use App\Entity\Car;
use App\Entity\User;
use App\Form\CarType;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AdminReservationsController extends AbstractController
{
    /**
     * @Route("/admin/reservations/", name="app_admin_reservations")
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UploaderHelper $helper
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function reservations(ReservationRepository $reservationRepository, UserRepository $userRepository, Request $request) : Response
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
                'name' => 'user',
                'sortable' => TRUE,
                'sort' => 'user.firstname',
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
        $pages = ceil((int)$reservationRepository->getCountWithUserAndCar($company, null, null) / $per_page);

        $get_name = function($value) {return $value['name'];};
        $get_sort = function($value) {return !empty($value['sort']) ? $value['sort'] : null;};
        if (!in_array($order, array_map($get_name, $fields)) && !in_array($order, array_map($get_sort, $fields))) $order = 'reservation.start';
        if (!in_array($order_direction, ['ASC', 'DESC'])) $order_direction = 'ASC';
        $page = max(1, min($page, $pages));

        $reservations = [];
        foreach ($reservationRepository->reservationPagination($company, $per_page, $page, $order, $order_direction, null, null) as $reservationData) {
            $reservations[] = [
                'id' => $reservationData->getId(),
                'start' => $reservationData->getStart()->format('Y-m-d H:i'),
                'end' => $reservationData->getEnd()->format('Y-m-d H:i'),
                'car' => $reservationData->getCar()->getBrand() . ' ' . $reservationData->getCar()->getModel(),
                'user' => $reservationData->getUser()->getFullname(),
            ];
        }

        return $this->render('adminReservations.html.twig', ['fields' => $fields, 'reservations' => $reservations, 'current_order' => $order, 'current_direction' => $order_direction, 'page' => $page, 'pages' => $pages, 'per_page' => $per_page]);
    }

    /**
     * @Route("admin/reservations/delete/{id}", methods={"DELETE"}, name="app_admin_reservations_delete")
     * @param int $id
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete_car(int $id, ReservationRepository $reservationRepository, UserRepository $userRepository, TranslatorInterface $translator) : Response
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
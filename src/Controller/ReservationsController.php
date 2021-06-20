<?php


namespace App\Controller;


use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationsController extends AbstractController
{
    /**
     * @Route("/reservations", name="app_reservations")
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @param CarRepository $carRepository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function reservations(Request $request, ReservationRepository $reservationRepository, userRepository $userRepository, carRepository $carRepository, translatorInterface $translator): Response
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
                'width' => '7%'
            ]
        ];

        $per_page = (int)($request->query->get('per_page') ?? 5);
        $page = (int)($request->query->get('page') ?? 1);
        $order = $request->query->get('order') ?? 'id';
        $order_direction = $request->query->get('direction') ?? 'ASC';

        $car = ($request->query->get('car') && $request->query->get('car') != 'all' ? $carRepository->find($request->query->get('car')) : null);
        $user = ($request->query->get('user') && $request->query->get('user') != 'all' ? $userRepository->find($request->query->get('user')) : null);
        $company = $userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany();

        $pages = ceil((int)$reservationRepository->getCountWithUserAndCar($company, $car, $user) / $per_page);

        $get_name = function($value) {return $value['name'];};
        $get_sort = function($value) {return !empty($value['sort']) ? $value['sort'] : null;};
        if (!in_array($order, array_map($get_name, $fields)) && !in_array($order, array_map($get_sort, $fields))) $order = 'start';
        if (!in_array($order_direction, ['ASC', 'DESC'])) $order_direction = 'ASC';
        $page = max(1, min($page, $pages));

        $cars = $carRepository->findBy(['company' => $company]);
        $users = $userRepository->findBy(['company' => $company]);

        $reservations = [];
        foreach ($reservationRepository->reservationPagination($company, $per_page, $page, $order, $order_direction, $car, $user) as $reservationData) {
            $reservations[] = [
                'id' => $reservationData->getId(),
                'start' => $reservationData->getStart()->format('Y-m-d H:i'),
                'end' => $reservationData->getEnd()->format('Y-m-d H:i'),
                'car' => $reservationData->getCar()->getBrand() . ' ' . $reservationData->getCar()->getModel(),
                'user' => $reservationData->getUser()->getFullname(),
            ];
        }

        return $this->render('reservations.html.twig', ['fields' => $fields, 'cars' => $cars, 'currentCar' => $car, 'users' => $users, 'currentUser' => $user, 'reservations' => $reservations, 'current_order' => $order, 'current_direction' => $order_direction, 'page' => $page, 'pages' => $pages, 'per_page' => $per_page]);
    }
}
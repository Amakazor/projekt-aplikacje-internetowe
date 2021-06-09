<?php
namespace App\Controller;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminPanelController extends AbstractController
{
    /**
     * @Route("/admin/")
     * @param UserRepository $userRepository
     * @param CarRepository $carRepository
     * @param ReservationRepository $reservationRepository
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(UserRepository $userRepository, CarRepository $carRepository, ReservationRepository $reservationRepository): Response {
        $company = $userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany();

        $stats = [
            [
                'title' => 'admin.preview.cars.total',
                'number' => $carRepository->carPaginationCount($company)
            ],
            [
                'title' => 'admin.preview.cars.free',
                'number' => $carRepository->getFree($company, $reservationRepository)
            ],
            [
                'title' => 'admin.preview.users.total',
                'number' => $userRepository->paginationCount($company)
            ],
            [
                'title' => 'admin.preview.users.verified',
                'number' => $userRepository->verifiedCount($company)
            ],
            [
                'title' => 'admin.preview.reservations.total',
                'number' => $reservationRepository->getCount($company)
            ],
            [
                'title' => 'admin.preview.reservations.current',
                'number' => $reservationRepository->getCurrentCount($company)
            ],
            [
                'title' => 'admin.preview.reservations.incoming',
                'number' => $reservationRepository->getFutureCount($company)
            ],
        ];

        return $this->render('admin.html.twig', ['stats' => $stats]);
    }
}
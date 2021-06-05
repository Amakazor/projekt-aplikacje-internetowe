<?php
namespace App\Controller;
use App\Entity\Car;
use App\Entity\User;
use App\Form\CarType;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AdminCarsController extends AbstractController
{
    /**
     * @Route("/admin/cars/", name="app_admin_cars")
     * @param CarRepository $carRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UploaderHelper $helper
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function cars(CarRepository $carRepository, UserRepository $userRepository, Request $request, UploaderHelper $helper) : Response
    {
        $fields = [
            [
                'name' => 'id',
                'sortable' => TRUE,
                'width' => '7%'
            ],
            [
                'name' => 'image',
                'sortable' => FALSE,
                'width' => '10%'
            ],
            [
                'name' => 'brand',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'model',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'horsepower',
                'sortable' => TRUE,
                'width' => '7%'
            ],
            [
                'name' => 'engine',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'color',
                'sortable' => TRUE,
                'width' => '10%'
            ],
            [
                'name' => 'description',
                'sortable' => TRUE,
                'width' => '30%'
            ],
            [
                'name' => 'year',
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
        $pages = ceil((int)$carRepository->carPaginationCount($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany()) / $per_page);

        $get_name = function($value) {return $value['name'];};
        if (!in_array($order, array_map($get_name, $fields))) $order = 'id';
        if (!in_array($order_direction, ['ASC', 'DESC'])) $order_direction = 'ASC';
        $page = max(1, min($page, $pages));

        $cars = [];
        foreach ($carRepository->carPagination($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $per_page, $page, $order, $order_direction) as $car_data) {
            $cars[] = [
                'id' => $car_data->getId(),
                'image' => $helper->asset($car_data, 'imageFile'),
                'brand' => $car_data->getBrand(),
                'model' => $car_data->getModel(),
                'horsepower' => $car_data->getHorsepower(),
                'engine' => $car_data->getEngine(),
                'color' => $car_data->getColor(),
                'description' => $car_data->getDescription(),
                'year' => $car_data->getYear()
            ];
        }

        return $this->render('adminCars.html.twig', ['fields' => $fields, 'cars' => $cars, 'current_order' => $order, 'current_direction' => $order_direction, 'page' => $page, 'pages' => $pages, 'per_page' => $per_page]);
    }

    /**
     * @Route("admin/cars/delete/{id}", methods={"DELETE"}, name="app_admin_cars_delete")
     * @param int $id
     * @param CarRepository $carRepository
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete_car(int $id, CarRepository $carRepository, UserRepository $userRepository, TranslatorInterface $translator) : Response
    {
        $status = $carRepository->removeCompanyCar($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $id);

        if ($status) {
            $this->addFlash(
                'success',
                $translator->trans('api.message.cars.delete.success') . $id
            );
        } else {
            $this->addFlash(
                'error',
                $translator->trans('api.message.cars.delete.error') . $id
            );
        }

        return new Response($status);
    }

    /**
     * @Route("admin/car/{id}", name="app_admin_car", requirements={"id"="\d+"})
     * @param Request $request
     * @param CarRepository $carRepository
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param int $id
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function car(Request $request, CarRepository $carRepository, UserRepository $userRepository, TranslatorInterface $translator, int $id = 0) : Response
    {
        $car = ($id == 0 || !$carRepository->doesCarIdBelongToCompany($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $id))
            ? new Car()
            : $carRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(CarType::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $status = FALSE;

            if ($form->isValid()) {
                $car = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($car);
                $entityManager->flush();

                $status = TRUE;
            }

            if ($status) {
                $this->addFlash(
                    'success',
                    $id == 0 ? $translator->trans('api.message.cars.add.success') . $id : $translator->trans('api.message.cars.change.success') . $id
                );
            } else {
                $this->addFlash(
                    'error',
                    $id == 0 ? $translator->trans('api.message.cars.add.error') . $id : $translator->trans('api.message.cars.change.error') . $id
                );
            }

            return $this->redirectToRoute('app_admin_cars');
        }

        return $this->render('adminCar.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
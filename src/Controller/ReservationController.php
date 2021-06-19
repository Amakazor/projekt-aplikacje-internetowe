<?php


namespace App\Controller;


use App\Entity\Reservation;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationController extends AbstractController
{
    /**
     * @Route("/reserve", name="app_reserve")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param CarRepository $carRepository
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function reserve(Request $request, TranslatorInterface $translator, CarRepository $carRepository, ReservationRepository $reservationRepository, UserRepository $userRepository): Response
    {
        $builder = $this->createFormBuilder(['start' => (new DateTime('now'))->format('Y-m-d H:i'), 'end' => (new DateTime('now'))->add(new DateInterval('P1D'))->format('Y-m-d H:i')]);
        $form = $builder
            ->add('start', TextType::class, [
                'label' => 'reservation.form.date.start',
                'attr' => [
                    'class' => 'stylizedDate'
                ]
            ])
            ->add('end', TextType::class, [
                'label' => 'reservation.form.date.end',
                'attr' => [
                    'class' => 'stylizedDate'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.search'
            ])
            ->getForm();

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($translator) {
            if (new DateTime($event->getForm()->getData()['start']) >= new DateTime($event->getForm()->getData()['end'])) {
                $event->getForm()->addError(new FormError($translator->trans('reservation.form.date.error')));
            }
        });
        $form->handleRequest($request);

        $cars = [];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $start = new DateTime($form->getData()['start']);
                $end = new DateTime($form->getData()['end']);

                $cars = $carRepository->getFreeBetweenDates($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $reservationRepository, $start, $end);
            }
        }

        return $this->render('reserve.html.twig', ['form' => $form->createView(), 'formValid' => $form->isSubmitted() && $form->isValid(), 'cars' => $cars, 'start' => $form->isSubmitted() && $form->isValid() ? $form->getData()['start'] : null, 'end' => $form->isSubmitted() && $form->isValid() ? $form->getData()['end'] : null]);
    }

    /**
     * @Route("/reserveid/{id}/{start}/{end}", name="app_reserve_id")
     * @param int $id
     * @param DateTime $start
     * @param DateTime $end
     * @param Request $request
     * @param ReservationRepository $reservationRepository
     * @param UserRepository $userRepository
     * @param CarRepository $carRepository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws Exception
     */
    public function reserveID(int $id, DateTime $start, DateTime $end, Request $request, ReservationRepository $reservationRepository, UserRepository $userRepository, CarRepository $carRepository, TranslatorInterface $translator) : Response {
        $cars = $carRepository->getFreeBetweenDates($userRepository->findOneBy(['username' => $this->getUser()->getUsername()])->getCompany(), $reservationRepository, $start, $end);

        $isStillValid = false;

        foreach ($cars as $car) {
            if ($car->getId() == $id) {
                $isStillValid = true;
                break;
            }
        }

        if ($isStillValid) {
            $reservation = new Reservation();
            $reservation -> setCar($carRepository->findOneBy(['id' => $id]));
            $reservation -> setStart($start);
            $reservation -> setEnd($end);
            $reservation -> setUser($userRepository->findOneBy(['username' => $this->getUser()->getUsername()]));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $translator->trans('api.message.reservation.create.success')
            );
            return $this->redirectToRoute('app_reserve');
            //TODO: Redirect to user reservations
        } else {
            $this->addFlash(
                'error',
                $translator->trans('api.message.reservation.create.error')
            );
            return $this->redirectToRoute('app_reserve');
        }
    }
}
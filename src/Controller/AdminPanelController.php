<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminPanelController extends AbstractController
{
    /**
     * @Route("/admin/")
     * @return Response
     */
    public function index(): Response {
        return $this->render('admin.html.twig', []);
    }
}
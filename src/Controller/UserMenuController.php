<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserMenuController extends AbstractController
{
    public function menu() : Response
    {
        $menu_items = [
            [
                'icon' => 'icon icon-user',
                'location' => 'app_user_data',
                'title' => 'user.menu.data',
            ],
            [
                'icon' => 'icon icon-clock2',
                'location' => 'app_user_reservations',
                'title' => 'user.menu.reservations',
            ],
        ];

        return $this->render('adminMenu.html.twig', ['menu' => $menu_items]);
    }
}
<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminMenuController extends AbstractController
{
    public function menu() : Response
    {
        $menu_items = [
            [
                'icon' => 'icon icon-car',
                'location' => 'app_adminpanel_index',
                'title' => 'admin.menu.cars.list',
            ],
            [
                'icon' => 'icon icon-car_add',
                'location' => 'app_adminpanel_index',
                'title' => 'admin.menu.cars.add',
            ],
            [
                'icon' => 'icon icon-user',
                'location' => 'app_adminpanel_index',
                'title' => 'admin.menu.users.list',
            ],
            [
                'icon' => 'icon icon-user-plus',
                'location' => 'app_adminpanel_index',
                'title' => 'admin.menu.users.add',
            ],
            [
                'icon' => 'icon icon-clock2',
                'location' => 'app_adminpanel_index',
                'title' => 'admin.menu.reservations.list',
            ],
        ];

        return $this->render('adminMenu.html.twig', ['menu' => $menu_items]);
    }
}
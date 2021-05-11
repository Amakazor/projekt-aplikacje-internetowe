<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuController extends AbstractController
{
    public function menu(TranslatorInterface $translator) : Response
    {
        $menu_items = [
            [
                'location' => 'app_index_index',
                'title' => $translator->trans('menu.home')
            ],
            [
                'location' => 'app_index_index',
                'title' => $translator->trans('menu.login')
            ]
        ];

        return $this->render('menu.html.twig', ['menu' => $menu_items]);
    }
}
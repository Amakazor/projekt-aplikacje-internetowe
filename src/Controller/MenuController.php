<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuController extends AbstractController
{
    public function menu(TranslatorInterface $translator) : Response
    {
        $menu_items = [];

        $user = $this->getUser();

        if ($user) {
            $roles = $user->getRoles();

            if (in_array("ROLE_USER", $roles)) {
                $menu_items[] = [
                    'location' => 'app_index_index',
                    'title' => $translator->trans('menu.reservation.make'),
                    'type' => 'underlined',
                ];

                $menu_items[] = [
                    'location' => 'app_index_index',
                    'title' => $translator->trans('menu.reservation.check'),
                    'type' => 'underlined',
                ];
            }

            if (in_array("ROLE_ADMIN", $roles)) {
                $menu_items[] = [
                    'location' => 'app_index_index',
                    'title' => $translator->trans('menu.adminPanel'),
                    'type' => 'underlined',
                ];
            }

            if (in_array("ROLE_USER", $roles)) {
                $menu_items[] = [
                    'location' => 'app_index_index',
                    'title' => $translator->trans('menu.logout'),
                    'type' => 'underlined',
                ];

                $menu_items[] = [
                    'location' => 'app_index_index',
                    'title' => $translator->trans('menu.login'),
                    'type' => 'bordered',
                ];
            }

        } else {
            $menu_items[] = [
                'location' => 'app_index_index',
                'title' => $translator->trans('menu.login'),
                'type' => 'bordered',
            ];
        }

        return $this->render('menu.html.twig', ['menu' => $menu_items]);
    }
}
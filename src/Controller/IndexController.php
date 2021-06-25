<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response {
        $promo = [
            [
                'title' => 'promo.easy',
                'asset' => 'build/images/promo1.png',
                'type' => 'normal'
            ], [
                'title' => 'promo.good',
                'asset' => 'build/images/promo2.png',
                'type' => 'reversed'
            ], [
                'title' => 'promo.user',
                'asset' => 'build/images/promo3.png',
                'type' => 'normal'
            ], [
                'title' => 'promo.fast',
                'asset' => 'build/images/promo4.png',
                'type' => 'reversed'
            ]
        ];

        $services = [
            [
                'title' => 'services.free.title',
                'price' => 'services.free.price',
                'button' => [
                    'text' => 'services.free.button',
                    'type' => 'callToAction',
                    'enabled' => TRUE
                ],
                'list' => [
                    [
                        'title' => 'services.free.list.cars',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.free.list.users',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.free.list.admin',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.free.list.system',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.free.list.fast',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.free.list.tech',
                        'enabled' => FALSE
                    ], [
                        'title' => 'services.free.list.phone',
                        'enabled' => FALSE
                    ],
                ]
            ], [
                'title' => 'services.premium.title',
                'price' => 'services.premium.price',
                'button' => [
                    'text' => 'services.premium.button',
                    'type' => 'disabled',
                    'enabled' => FALSE
                ],
                'list' => [
                    [
                        'title' => 'services.premium.list.cars',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.users',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.admin',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.system',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.fast',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.tech',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.premium.list.phone',
                        'enabled' => FALSE
                    ],
                ]
            ], [
                'title' => 'services.enterprise.title',
                'price' => 'services.enterprise.price',
                'button' => [
                    'text' => 'services.enterprise.button',
                    'type' => 'disabled',
                    'enabled' => FALSE
                ],
                'list' => [
                    [
                        'title' => 'services.enterprise.list.cars',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.users',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.admin',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.system',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.fast',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.tech',
                        'enabled' => TRUE
                    ], [
                        'title' => 'services.enterprise.list.phone',
                        'enabled' => TRUE
                    ],
                ]
            ]
        ];

        return $this->render('base.html.twig', ['promo' => $promo, 'services' => $services]);
    }
}
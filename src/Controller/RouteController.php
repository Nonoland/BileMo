<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RouteController extends AbstractController
{
    public function getObjectDetail(array $data, string $selfLink)
    {
        return [
            'data' => $data,
            'links' => [
                'self' => $selfLink
            ]
        ];
    }
}

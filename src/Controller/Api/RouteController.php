<?php

namespace App\Controller\Api;

use App\Entity\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    protected function verifyAccess(Store $store): void
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return;
        }

        if ($this->getUser()->getStores()->contains($store)) {
            return;
        }

        throw new AccessDeniedHttpException('Access denied !');
    }
}

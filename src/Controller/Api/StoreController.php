<?php

namespace App\Controller\Api;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
class StoreController extends RouteController
{

    private StoreRepository $storeRepository;
    private TagAwareCacheInterface $cache;
    public function __construct(StoreRepository $storeRepository, TagAwareCacheInterface $cache)
    {
        $this->storeRepository = $storeRepository;
        $this->cache = $cache;
    }

    #[Route(
        '/stores',
        name: 'app_stores_list',
        methods: ['GET']
    )]
    public function storeList(): JsonResponse
    {
        return $this->json($this->getStorePageSchema());
    }

    #[Route(
        '/stores/page/{page}',
        name: 'app_stores_list_page',
        requirements: ['page' => '\d+'],
        methods: ['GET']
    )]
    public function storeListPage(int $page): JsonResponse
    {
        return $this->json($this->getStorePageSchema($page));
    }

    #[Route(
        '/stores/detail/{idStore}',
        name: 'app_stores_detail',
        requirements: ['idStore' => '\d+'],
        methods: ['GET']
    )]
    public function storeDetail(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store
    ): JsonResponse {
        $this->verifyAccess($store);

        return $this->json($this->getObjectDetail(
            $this->cache->get('storeDetail_' . $store->getId(), function (ItemInterface $item) use ($store) {
                $item->tag('storesDetails');
                return $store->getData();
            }),
            $this->generateUrl('api_app_stores_detail', ['idStore' => $store->getId()])
        ));
    }

    private function getStorePageSchema(int $page = 1): array
    {
        return $this->cache->get("getStorePageSchema_" . $page . "_" . $this->getUser()->getId(), function (ItemInterface $item) use ($page) {
            $item->tag('storeLists');
            $stores = $this->storeRepository->findByPage($this->getUser(), $page);

            $data['data'] = [];
            foreach ($stores as $store) {
                $data['data'][] = [
                    'name' => $store->getName(),
                    'link' => $this->generateUrl('api_app_stores_detail', ['idStore' => $store->getId()])
                ];
            }

            if ($page != 1) {
                $data['links']['prev'] = $this->generateUrl('api_app_stores_list_page', ['page' => $page - 1]);
            }

            $data['links']['self'] = $this->generateUrl('api_app_stores_list_page', ['page' => $page]);
            $data['links']['next'] = $this->generateUrl('api_app_stores_list_page', ['page' => $page + 1]);

            return $data;
        });
    }
}

<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ProductController extends RouteController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route(
        '/products',
        name: 'app_products_list',
        methods: ['GET']
    )]
    public function productList(): JsonResponse
    {
        return $this->json($this->getProductPageSchema());
    }

    #[Route(
        '/products/page/{page}',
        name: 'app_products_list_page',
        requirements: ['page' => '\d+'],
        methods: ['GET']
    )]
    public function productListPage(int $page): JsonResponse
    {
        return $this->json($this->getProductPageSchema($page));
    }

    #[Route(
        '/products/detail/{id}',
        name: 'app_products_detail',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function productDetail(
        #[MapEntity(mapping: ['id' => 'id'])]
        Product $product
    ): JsonResponse {
        return $this->json($this->getObjectDetail(
            $product->getData(),
            $this->generateUrl('api_app_products_detail', ['id' => $product->getId()])
        ));
    }

    private function getProductPageSchema(int $page = 1): array
    {
        $products = $this->productRepository->findByPage($page);

        $data['data'] = [];
        foreach ($products as $product) {
            $data['data'][] = [
                'name' => $product->getName(),
                'gtin' => $product->getGtin(),
                'link' => $this->generateUrl('api_app_products_detail', ['id' => $product->getId()])
            ];
        }

        if ($page != 1) {
            $data['links']['prev'] = $this->generateUrl('api_app_products_list_page', ['page' => $page - 1]);
        }

        $data['links']['self'] = $this->generateUrl('api_app_products_list_page', ['page' => $page]);
        $data['links']['next'] = $this->generateUrl('api_app_products_list_page', ['page' => $page + 1]);

        return $data;
    }
}

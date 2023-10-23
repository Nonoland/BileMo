<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestMatcher\PortRequestMatcher;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/products', name: 'app_product_list', methods: ['GET'])]
    public function productList(): JsonResponse
    {
        $data = $this->getProductPageSchema();

        return $this->json($data);
    }

    #[Route('/products/page/{page}', name: 'app_product_list_page')]
    public function productListPage(int $page): JsonResponse
    {
        $data = $this->getProductPageSchema($page);

        return $this->json($data);
    }

    #[Route('/products/detail/{id}', name: 'app_product_detail', methods: ['GET'])]
    public function productDetail(ProductRepository $productRepository): JsonResponse
    {
       return $this->json([]);
    }

    private function getProductPageSchema(int $page = 1): array
    {
        $products = $this->productRepository->findByPage($page);

        $data = ['products' => []];
        foreach ($products as $product) {
            $data['products'][] = [
                'name' => $product->getName(),
                'gtin' => $product->getGtin(),
                'link' => $this->generateUrl('app_product_detail', ['id' => $product->getId()])
            ];
        }

        $data['count'] = count($data['products']);
        $data['nextPage'] = $this->generateUrl('app_product_list_page', ['page' => $page + 1]);

        return $data;
    }
}

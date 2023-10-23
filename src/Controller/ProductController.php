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
        return $this->json($this->getProductPageSchema());
    }

    #[Route('/products/page/{page}', name: 'app_product_list_page')]
    public function productListPage(int $page): JsonResponse
    {
        return $this->json($this->getProductPageSchema($page));
    }

    #[Route('/products/detail/{id}', name: 'app_product_detail', methods: ['GET'])]
    public function productDetail(Product $product): JsonResponse
    {
        $data['data'] = $product->getData();
        $data['links']['self'] = $this->generateUrl('app_product_detail', ['id' => $product->getId()]);

       return $this->json($data);
    }

    private function getProductPageSchema(int $page = 1): array
    {
        $products = $this->productRepository->findByPage($page);

        $data['data'] = [];
        foreach ($products as $product) {
            $data['data'][] = [
                'name' => $product->getName(),
                'gtin' => $product->getGtin(),
                'link' => $this->generateUrl('app_product_detail', ['id' => $product->getId()])
            ];
        }

        if ($page != 1) {
            $data['links']['prev'] = $this->generateUrl('app_product_list_page', ['page' => $page - 1]);
        }

        $data['links']['self'] = $this->generateUrl('app_product_list_page', ['page' => $page]);
        $data['links']['next'] = $this->generateUrl('app_product_list_page', ['page' => $page + 1]);

        return $data;
    }
}

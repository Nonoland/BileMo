<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
#[OA\Tag('Product')]
class ProductController extends RouteController
{
    private ProductRepository $productRepository;
    private TagAwareCacheInterface $cache;

    public function __construct(ProductRepository $productRepository, TagAwareCacheInterface $cache)
    {
        $this->productRepository = $productRepository;
        $this->cache = $cache;
    }

    #[Route(
        '/products',
        name: 'app_products_list',
        methods: ['GET']
    )]
    #[OA\Get(summary: 'Get products list')]
    #[OA\Response(
        response: 200,
        description: 'Get products list',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'name',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'gtin',
                                description: 'GTIN Code',
                                type: 'string',
                                pattern: '^\d{8}(\d{5})?$'
                            ),
                            new OA\Property(
                                property: 'link',
                                type: 'string',
                                format: 'uri'
                            ),
                        ],
                        maxItems: 10
                    )
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'self',
                            type: 'string',
                            format: 'uri'
                        ),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            format: 'uri'
                        ),
                        new OA\Property(
                            property: 'prev',
                            type: 'string',
                            format: 'uri'
                        ),
                    ]
                )
            ]
        )
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
    #[OA\Get(summary: 'Get products list with page selector')]
    #[OA\Response(
        response: 200,
        description: 'Get products list with page selector',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'name',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'gtin',
                                description: 'GTIN Code',
                                type: 'string',
                                pattern: '^\d{8}(\d{5})?$'
                            ),
                            new OA\Property(
                                property: 'link',
                                type: 'string',
                                format: 'uri'
                            ),
                        ],
                        maxItems: 10
                    )
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'self',
                            type: 'string',
                            format: 'uri'
                        ),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            format: 'uri'
                        ),
                        new OA\Property(
                            property: 'prev',
                            type: 'string',
                            format: 'uri'
                        ),
                    ]
                )
            ]
        )
    )]
    public function productListPage(int $page): JsonResponse
    {
        return $this->json($this->getProductPageSchema($page));
    }

    #[Route(
        '/products/detail/{idProduct}',
        name: 'app_products_detail',
        requirements: ['idProduct' => '\d+'],
        methods: ['GET']
    )]
    #[OA\Get(summary: 'Get product detail')]
    #[OA\Response(
        response: 200,
        description: 'Get product detail',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer'
                            ),
                            new OA\Property(
                                property: 'name',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'gtin',
                                description: 'GTIN Code',
                                type: 'string',
                                pattern: '^\d{8}(\d{5})?$'
                            ),
                            new OA\Property(
                                property: 'brand',
                                type: 'string'
                            )
                            ,
                            new OA\Property(
                                property: 'image',
                                type: 'string',
                                format: 'uri'
                            ),
                            new OA\Property(
                                property: 'supplier_price',
                                type: 'float'
                            ),
                            new OA\Property(
                                property: 'suggested_price',
                                type: 'float'
                            ),
                            new OA\Property(
                                property: 'short_description',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'description',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'features',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'string'
                                )
                            )
                        ]
                    )
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'self',
                            type: 'string',
                            format: 'uri'
                        )
                    ]
                )
            ]
        )
    )]
    public function productDetail(
        #[MapEntity(mapping: ['idProduct' => 'id'])]
        Product $product
    ): JsonResponse {
        return $this->json($this->getObjectDetail(
            $this->cache->get("productDetail_" . $product->getId(), function (ItemInterface $item) use ($product) {
                $item->tag('productsDetails');
                return $product->getData();
            }),
            $this->generateUrl('api_app_products_detail', ['idProduct' => $product->getId()])
        ));
    }

    private function getProductPageSchema(int $page = 1): array
    {
        return $this->cache->get("getProductPageSchema_$page", function (ItemInterface $item) use ($page) {
            $item->tag('productsLists');

            $products = $this->productRepository->findByPage($page);

            $data['data'] = [];
            foreach ($products as $product) {
                $data['data'][] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'gtin' => $product->getGtin(),
                    'link' => $this->generateUrl('api_app_products_detail', ['idProduct' => $product->getId()])
                ];
            }

            if ($page != 1) {
                $data['links']['prev'] = $this->generateUrl('api_app_products_list_page', ['page' => $page - 1]);
            }

            $data['links']['self'] = $this->generateUrl('api_app_products_list_page', ['page' => $page]);
            $data['links']['next'] = $this->generateUrl('api_app_products_list_page', ['page' => $page + 1]);

            return $data;
        });
    }
}

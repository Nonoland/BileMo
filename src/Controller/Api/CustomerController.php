<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Store;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api', name: 'api_')]
#[OA\Tag('Customer')]
class CustomerController extends RouteController
{
    private CustomerRepository $customerRepository;
    private EntityManagerInterface $entityManager;
    private TagAwareCacheInterface $cache;

    public function __construct(
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) {
        $this->customerRepository = $customerRepository;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
    }

    #[Route(
        '/store/{idStore}/customers',
        name: 'app_customers_list',
        requirements: ['idStore' => '\d+'],
        methods: ['GET']
    )]
    #[OA\Get(summary: 'Get customers list')]
    #[OA\Response(
        response: 200,
        description: 'Get customers list',
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
                                property: 'lastname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'firstname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email'
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
    public function customersList(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store
    ): JsonResponse {
        $this->verifyAccess($store);

        return $this->json($this->getCustomerPageSchema($store));
    }

    #[Route(
        '/store/{idStore}/customers/page/{page}',
        name: 'app_customers_list_page',
        requirements: ['idStore' => '\d+', 'page' => '\d+'],
        methods: ['GET']
    )]
    #[OA\Get(summary: 'Get customers list with page selector')]
    #[OA\Response(
        response: 200,
        description: 'Get customers list with page selector',
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
                                property: 'lastname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'firstname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email'
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
    public function customerListPage(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store,
        int $page
    ): JsonResponse {
        $this->verifyAccess($store);

        return $this->json($this->getCustomerPageSchema($store, $page));
    }

    #[Route(
        '/store/{idStore}/customers/detail/{idCustomer}',
        name: 'app_customers_detail',
        requirements: ['idStore' => '\d+', 'idCustomer' => '\d+'],
        methods: ['GET']
    )]
    #[OA\Get(summary: 'Get customer details')]
    #[OA\Response(
        response: 200,
        description: 'Get customer details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'lastname',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'firstname',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'email',
                            type: 'string',
                            format: 'email'
                        ),
                        new OA\Property(
                            property: 'store_id',
                            type: 'integer'
                        ),
                        new OA\Property(
                            property: 'store_name',
                            type: 'string'
                        ),
                    ],
                    type: 'object'
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
    public function customerDetail(
        #[MapEntity(mapping: ['idStore' => 'store', 'idCustomer' => 'id'])]
        Customer $customer,
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store
    ): JsonResponse {
        $this->verifyAccess($store);

        return $this->json($this->getObjectDetail(
            $this->cache->get('customerDetail_' . $customer->getId(), function (ItemInterface $item) use ($customer) {
                $item->tag('customersDetails');
                return $customer->getData();
            }),
            $this->generateUrl('api_app_customers_detail', ['idStore' => $customer->getStore()->getId(), 'idCustomer' => $customer->getId()])
        ));
    }

    #[Route(
        '/store/{idStore}/customers/add',
        name: 'app_customers_add',
        requirements: ['idStore' => '\d+'],
        methods: ['POST']
    )]
    #[OA\Post(summary: 'Add new customer to the store')]
    #[OA\Response(
        response: 200,
        description: 'Add new customer to the store',
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
                                property: 'lastname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'firstname',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'email',
                                type: 'string',
                                format: 'email'
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
                    type: 'string',
                    format: 'uri'
                )
            ]
        )
    )]
    public function createCustomer(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store,
        Request $request
    ): JsonResponse {
        $this->verifyAccess($store);

        $params = ['lastname', 'firstname', 'email'];
        foreach ($params as $param) {
            if (!array_key_exists($param, $request->request->all())) {
                return $this->json([
                    'status' => '400',
                    'error' => 'Bad request',
                    'message' => "Missing required paramters : $param"
                ], 400);
            }
        }

        $email = $request->request->get('email');
        $emailFind = $this->customerRepository->findBy(['email' => $email]);

        if (!empty($emailFind)) {
            return $this->json([
                'status' => '409',
                'error' => 'Conflict',
                'message' => "A customer with this email already exists."
            ], 409);
        }

        $customer = new Customer();
        $customer->setStore($store);
        $customer->setFirstname($request->request->get('firstname'));
        $customer->setLastname($request->request->get('lastname'));
        $customer->setEmail($email);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $this->json([
            'status' => '201',
            'message' => 'Customer successfully created',
            'data' => $customer->getData(),
            'link' => $this->generateUrl('api_app_customers_detail', ['idStore' => $store->getId(), 'idCustomer' => $customer->getId()])
        ], 201);
    }

    #[Route(
        '/store/{idStore}/customers/{idCustomer}/delete',
        name: "app_customers_delete",
        requirements: ['idStore' => '\d+', 'idCustomer' => '\d+'],
        methods: ['DELETE']
    )]
    #[OA\Delete(summary: 'Remove a customer from the store')]
    #[OA\Response(
        response: 200,
        description: 'Remove a customer from the store',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'integer',
                    enum: [200, 404, 500]
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string'
                )
            ]
        )
    )]
    public function deleteCustomer(
        #[MapEntity(mapping: ['idCustomer' => 'id', 'idStore' => 'store'])]
        Customer $customer,
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store
    ): JsonResponse {
        $this->verifyAccess($store);

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Customer successfully removed'
        ]);
    }

    private function getCustomerPageSchema(Store $store, int $page = 1): array
    {
        return $this->cache->get('getCustomerPageSchema_' . $store->getName() . "_$page", function (ItemInterface $item) use ($store, $page) {
            $item->tag('customersLists');

            $customers = $this->customerRepository->findByPage($store, $page);

            $data['data'] = [];
            /** @var Customer $customer */
            foreach ($customers as $customer) {
                $data['data'][] = [
                    'id' => $customer->getId(),
                    'lastname' => $customer->getLastname(),
                    'firstname' => $customer->getFirstname(),
                    'email' => $customer->getEmail(),
                    'link' => $this->generateUrl('api_app_customers_detail', ['idStore' => $store->getId(), 'idCustomer' => $customer->getId()])
                ];
            }

            if ($page != 1) {
                $data['links']['prev'] = $this->generateUrl('api_app_customers_list_page', ['idStore' => $store->getId(), 'page' => $page - 1]);
            }

            $data['links']['self'] = $this->generateUrl('api_app_customers_list_page', ['idStore' => $store->getId(), 'page' => $page]);
            $data['links']['next'] = $this->generateUrl('api_app_customers_list_page', ['idStore' => $store->getId(), 'page' => $page + 1]);

            return $data;
        });
    }
}

<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Store;
use App\Repository\CustomerRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class CustomerController extends RouteController
{
    private CustomerRepository $customerRepository;
    private StoreRepository $storeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CustomerRepository $customerRepository, StoreRepository $storeRepository, EntityManagerInterface $entityManager)
    {
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->entityManager = $entityManager;
    }

    #[Route(
        '/store/{idStore}/customers',
        name: 'app_customers_list',
        requirements: ['idStore' => '\d+'],
        methods: ['GET']
    )]
    public function customersList(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store
    ): JsonResponse {
        return $this->json($this->getCustomerPageSchema($store));
    }

    #[Route(
        '/store/{idStore}/customers/page/{page}',
        name: 'app_customers_list_page',
        requirements: ['idStore' => '\d+', 'page' => '\d+'],
        methods: ['GET']
    )]
    public function customerListPage(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store,
        int $page
    ): JsonResponse {
        return $this->json($this->getCustomerPageSchema($store, $page));
    }

    #[Route(
        '/store/{idStore}/customers/detail/{idCustomer}',
        name: 'app_customers_detail',
        requirements: ['idStore' => '\d+', 'idCustomer' => '\d+'],
        methods: ['GET']
    )]
    public function customerDetail(
        #[MapEntity(mapping: ['idStore' => 'store', 'idCustomer' => 'id'])]
        Customer $customer
    ): JsonResponse {
        return $this->json($this->getObjectDetail(
            $customer->getData(),
            $this->generateUrl('api_app_customers_detail', ['idStore' => $customer->getStore()->getId(), 'idCustomer' => $customer->getId()])
        ));
    }

    #[Route(
        '/store/{idStore}/customers/add',
        name: 'app_customers_add',
        requirements: ['idStore' => '\d+'],
        methods: ['POST']
    )]
    public function createCustomer(
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store,
        Request $request
    ): JsonResponse {
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
            'data' => $customer->getData()
        ], 201);
    }

    #[Route(
        '/store/{idStore}/customers/{idCustomer}/delete',
        name: "app_customers_delete",
        requirements: ['idStore' => '\d+', 'idCustomer' => '\d+'],
        methods: ['DELETE']
    )]
    public function deleteCustomer(
        #[MapEntity(mapping: ['idCustomer' => 'id', 'idStore' => 'store'])]
        Customer $customer
    ): JsonResponse {
        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Customer successfully removed'
        ]);
    }

    private function getCustomerPageSchema(Store $store, int $page = 1): array
    {
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
    }
}

<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Store;
use App\Repository\CustomerRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/store/{storeId}/customers', name: 'app_customers_list', methods: ['GET'])]
    public function customersList(int $storeId): JsonResponse
    {
        return $this->json($this->getCustomerPageSchema($storeId));
    }

    #[Route('/store/{storeId}/customers/page/{page}', name: 'app_customers_list_page')]
    public function customerListPage(int $storeId, int $page): JsonResponse
    {
        return $this->json($this->getCustomerPageSchema($storeId, $page));
    }

    #[Route('/store/{storeId}/customers/detail/{id}', name: 'app_customers_detail', methods: ['GET'])]
    public function customerDetail(Customer $customer): JsonResponse
    {
        return $this->json($this->getObjectDetail(
            $customer->getData(),
            $this->generateUrl('app_customers_detail', ['storeId' => $customer->getStore()->getId(), 'id' => $customer->getId()])
        ));
    }

    #[Route('/store/{storeId}/customers/add', name: 'app_customers_add', methods: ['POST'])]
    public function createCustomer(int $storeId, Request $request): JsonResponse
    {
        $store = $this->storeRepository->find($storeId);
        if (!$store) {
            return $this->json([
                'status' => '404',
                'error' => 'Not Found',
                'message' => "Store with id $storeId not found."
            ], 404);
        }

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
        #[MapEntity(mapping: ['idStore' => 'id'])]
        Store $store,
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

    private function getCustomerPageSchema(int $storeId, int $page = 1): array
    {
        $customers = $this->customerRepository->findByPage($storeId, $page);

        $data['data'] = [];
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $data['data'][] = [
                'id' => $customer->getId(),
                'lastname' => $customer->getLastname(),
                'firstname' => $customer->getFirstname(),
                'email' => $customer->getEmail(),
                'link' => $this->generateUrl('app_customers_detail', ['storeId' => $storeId, 'id' => $customer->getId()])
            ];
        }

        if ($page != 1) {
            $data['links']['prev'] = $this->generateUrl('app_customers_list_page', ['storeId' => $storeId, 'page' => $page - 1]);
        }

        $data['links']['self'] = $this->generateUrl('app_customers_list_page', ['storeId' => $storeId, 'page' => $page]);
        $data['links']['next'] = $this->generateUrl('app_customers_list_page', ['storeId' => $storeId, 'page' => $page + 1]);

        return $data;
    }
}

<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    #[Route('/customers', name: 'app_customers_list', methods: ['GET'])]
    public function customersList(): JsonResponse
    {
        return $this->json($this->getCustomerPageSchema());
    }

    #[Route('/customers/page/{page}', name: 'app_customers_list_page')]
    public function customerListPage(int $page): JsonResponse
    {
        return $this->json($this->getCustomerPageSchema($page));
    }

    #[Route('/customers/detail/{id}', name: 'app_customers_detail', methods: ['GET'])]
    public function productDetail(Customer $customer): JsonResponse
    {
        $data['data'] = $customer->getData();
        $data['links']['self'] = $this->generateUrl('app_customers_detail', ['id' => $customer->getId()]);

        return $this->json($data);
    }

    private function getCustomerPageSchema(int $page = 1): array
    {
        $customers = $this->customerRepository->findByPage($page);

        $data['data'] = [];
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $data['data'][] = [
                'id' => $customer->getId(),
                'lastname' => $customer->getLastname(),
                'firstname' => $customer->getFirstname(),
                'email' => $customer->getEmail(),
                'link' => $this->generateUrl('app_customers_detail', ['id' => $customer->getId()])
            ];
        }

        if ($page != 1) {
            $data['links']['prev'] = $this->generateUrl('app_customers_list_page', ['page' => $page - 1]);
        }

        $data['links']['self'] = $this->generateUrl('app_customers_list_page', ['page' => $page]);
        $data['links']['next'] = $this->generateUrl('app_customers_list_page', ['page' => $page + 1]);

        return $data;
    }
}

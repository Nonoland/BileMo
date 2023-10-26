<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Store;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('BeliMo')
            ->setFaviconPath('assets/favicon.png')
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Products', 'fas fa-box', Product::class);
        yield MenuItem::linkToCrud('Stores', 'fas fa-store', Store::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Customers', 'fas fa-person', Customer::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class)->setPermission('ROLE_ADMIN');
    }
}

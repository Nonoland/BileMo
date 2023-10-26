<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }
}

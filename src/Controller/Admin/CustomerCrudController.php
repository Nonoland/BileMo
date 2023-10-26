<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $store = AssociationField::new('store')
            ->setLabel('Store')
            ->setFormTypeOptions([
                'class' => Store::class,
                'choice_label' => 'name'
            ])
        ;

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            return [
                'lastname',
                'firstname',
                'email',
                $store
            ];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                'id',
                'lastname',
                'firstname',
                'email',
                $store
            ];
        }

        return [
            'lastname',
            'firstname',
            'email',
            $store
        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $stores = AssociationField::new('stores')
            ->setLabel('Stores')
            ->setFormTypeOptions([
                'class' => Store::class,
                'choice_label' => 'name'
            ])
        ;

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            return [
                'email',
                $stores
            ];
        }

        return [
            'id',
            'email',
            $stores
        ];
    }
}

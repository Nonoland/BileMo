<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\Store;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('lastname'),
            TextField::new('firstname'),
            EmailField::new('email'),
            AssociationField::new('store')
                ->setLabel('Store')
                ->setFormTypeOptions([
                    'class' => Store::class,
                    'choice_label' => 'name'
                ])
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $response;
        }

        $response
            ->leftJoin('entity.store', 'store')
            ->leftJoin('store.users', 'users')
            ->andWhere('users.id = :user')
            ->setParameter('user', $this->getUser())
        ;

        return $response;
    }

    public function edit(AdminContext $context)
    {
        /** @var Customer $customer */
        $customer = $context->getEntity()->getInstance();
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getStores()->contains($customer->getStore())) {
            /** @var AdminUrlGenerator $adminUrlGenerator */
            $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
            return $this->redirect($adminUrlGenerator->setController(CustomerCrudController::class)->setAction('index')->setEntityId(null)->generateUrl());
        }

        return parent::edit($context);
    }
}

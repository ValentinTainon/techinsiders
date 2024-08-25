<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setEntityLabelInSingular(t('category.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('category.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle('new', t('create.category', [], 'EasyAdminBundle'))
            ->setPageTitle('edit', t('edit.category', [], 'EasyAdminBundle'))
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.category', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.category.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', t('name.label', [], 'forms'));
        
        yield SlugField::new('slug', t('slug.label', [], 'forms'))
            ->setTargetFieldName('name')
            ->setFormTypeOption('row_attr', ['style' => 'display: none;']);
    }
}

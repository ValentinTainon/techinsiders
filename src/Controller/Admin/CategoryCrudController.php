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
            ->setEntityLabelInSingular(t('admin.entity.label.singular.category', [], 'admin'))
            ->setEntityLabelInPlural(t('admin.entity.label.plural.category', [], 'admin'))
            ->setPageTitle('new', t('admin.page.new.title.category', [], 'admin'))
            ->setPageTitle('edit', t('admin.page.edit.title.category', [], 'admin'))
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('admin.action.new.category', [], 'admin')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('admin.action.save_and_add_another.category', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('admin.action.save_and_continue', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('admin.action.save', [], 'admin')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', t('admin.form.label.name', [], 'admin'));
        yield SlugField::new('slug', t('admin.form.label.slug', [], 'admin'))
            ->setTargetFieldName('name')
            ->setFormTypeOption('row_attr', ['style' => 'display: none;']);
    }
}

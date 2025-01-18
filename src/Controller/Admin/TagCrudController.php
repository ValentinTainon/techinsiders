<?php

namespace App\Controller\Admin;

use function Symfony\Component\Translation\t;
use App\Entity\Tag;
use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission(UserRole::SUPER_ADMIN->value)
            ->setEntityLabelInSingular(t('tag.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('tag.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_NEW, t('create.tag', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_EDIT, t('edit.tag', [], 'EasyAdminBundle'))
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action->setLabel(t('create.tag', [], 'EasyAdminBundle'))
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action->setLabel(t('create_and_add.tag.label', [], 'EasyAdminBundle'))
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset()->addCssClass('custom-max-width');
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm();

        yield TextField::new('name', t('name.label', [], 'forms'))
            ->setColumns('col-sm-6 col-md-5');

        yield IntegerField::new('postsCount', t('posts.label', [], 'forms'))
            ->hideWhenCreating()
            ->hideWhenUpdating()
            ->setTextAlign('center');
    }
}

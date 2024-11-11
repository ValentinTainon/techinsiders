<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('comment.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('comment.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_NEW, t('create.comment', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_EDIT, t('edit.comment', [], 'EasyAdminBundle'))
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (is_granted("ROLE_EDITOR"))'
        );

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermissions([
                Action::EDIT => $expression,
                Action::DELETE => $expression,
                Action::BATCH_DELETE => $expression
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel(t('create.comment', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission('ROLE_SUPER_ADMIN');

        $createdAtField = DateTimeField::new('createdAt', t('created_at.label', [], 'forms'))
            ->setDisabled()
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-5');

        $updatedAtField = DateTimeField::new('updatedAt', t('updated_at.label', [], 'forms'))
            ->setDisabled()
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-5');

        if ($this->isUpdatedAtNull()) {
            $updatedAtField->hideWhenUpdating();
        }

        $userField = AssociationField::new('user', t('author.label', [], 'forms'))
            ->setDisabled()
            ->setColumns('col-sm-6 col-md-5');

        $postField = AssociationField::new('post', t('post.label.singular', [], 'EasyAdminBundle'))
            ->setDisabled()
            ->setRequired(false)
            ->setColumns(10);

        $contentField = TextareaField::new('content', t('content.label', [], 'forms'))
            ->setColumns(10);

        yield FormField::addFieldset()->addCssClass('custom-max-width');
        yield $createdAtField;
        yield $updatedAtField;

        yield FormField::addRow();
        yield $userField;

        yield FormField::addRow();
        yield $postField;

        yield $contentField;
    }

    private function isUpdatedAtNull(): bool
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof Comment) {
            return false;
        }

        return is_null($entityInstance->getUpdatedAt());
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $filters->add(EntityFilter::new('user', t('author.label', [], 'forms')));
        }

        $filters->add(EntityFilter::new('post', t('post.label.singular', [], 'EasyAdminBundle')))
            ->add(DateTimeFilter::new('createdAt', t('created_at.label', [], 'forms')))
            ->add(DateTimeFilter::new('updatedAt', t('updated_at.label', [], 'forms')));

        return $filters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $queryBuilder->where('entity.user = :user')
                ->setParameter('user', $this->getUser());
        }

        return $queryBuilder;
    }
}

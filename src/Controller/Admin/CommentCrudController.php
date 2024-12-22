<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Enum\UserRole;
use App\Entity\Comment;
use App\Enum\EditorConfigType;
use Doctrine\ORM\QueryBuilder;
use App\Security\Voter\CommentVoter;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\FormEvents;
use App\Form\Admin\Field\CkeditorField;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addAssetMapperEntry('ckeditor-init');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DETAIL)
            ->setPermissions([
                Action::EDIT => CommentVoter::EDIT,
                Action::DELETE => CommentVoter::DELETE,
                Action::BATCH_DELETE => CommentVoter::BATCH_DELETE
            ])
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action->setLabel(t('create.comment', [], 'EasyAdminBundle'))
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission(UserRole::SUPER_ADMIN->value);

        $createdAtField = DateTimeField::new('createdAt', t('created_at.label', [], 'forms'))
            ->setRequired(false)
            ->setDisabled()
            ->hideWhenCreating()
            ->setColumns('col-sm-6 col-md-5');

        $updatedAtField = DateTimeField::new('updatedAt', t('updated_at.label', [], 'forms'))
            ->setRequired(false)
            ->setDisabled()
            ->hideWhenCreating()
            ->setColumns('col-sm-6 col-md-5');

        if ($this->isUpdatedAtNull()) {
            $updatedAtField->hideWhenUpdating();
        }

        $userField = AssociationField::new('user', t('author.label', [], 'forms'))
            ->setDisabled()
            ->hideWhenCreating()
            ->setColumns('col-sm-6 col-md-5');

        $postField = AssociationField::new('post', t('post.label.singular', [], 'EasyAdminBundle'))
            ->setHtmlAttribute('required', true)
            ->setColumns(10);

        $contentField = CkeditorField::new('content', t('content.label', [], 'forms'))
            ->addFormTheme('bundles/EasyAdminBundle/crud/field/comment-editor-placeholder.html.twig')
            ->setFormTypeOption('attr', [
                'editor_data' => [
                    'editor_config_type' => EditorConfigType::STARTER->value,
                ]
            ])
            ->setHelp(
                t('comment_content.help.message', ['%max_comment_length_limit%' => 500], 'forms')
            )
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
        if ($this->isGranted(UserRole::SUPER_ADMIN->value)) {
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

        if (!$this->isGranted(UserRole::SUPER_ADMIN->value)) {
            $queryBuilder->where('entity.user = :user')
                ->setParameter('user', $this->getUser());
        }

        return $queryBuilder;
    }
}

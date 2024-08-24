<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use App\Form\Admin\Field\CkeditorField;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('post.label.singular', [], 'admin'))
            ->setEntityLabelInPlural(t('post.label.plural', [], 'admin'))
            ->setPageTitle('new', t('create.post', [], 'admin'))
            ->setPageTitle('edit', t('edit.post', [], 'admin'))
            ->addFormTheme('bundles/EasyAdminBundle/crud/field/ckeditor_init.html.twig')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->setPermissions([
                Action::EDIT => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DETAIL => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DELETE => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))')
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.post', [], 'admin')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.post.label', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'admin')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', t('author.label', [], 'admin'))
            ->onlyOnIndex();
        yield AssociationField::new('category', t('category.label.singular', [], 'admin'));
        yield DateTimeField::new('createdAt', t('created_at.label', [], 'admin'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);
        yield TextField::new('title', t('title.label', [], 'admin'));
        yield SlugField::new('slug', t('slug.label', [], 'admin'))
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setFormTypeOption('row_attr', ['style' => 'display: none;']);
        $postThumbnailField = ImageField::new('thumbnail', t('thumbnail.label', [], 'admin'))
            ->setBasePath('uploads/posts/thumbnails')
            ->setUploadDir('public/uploads/posts/thumbnails')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setHelp(t('image.field.help.message', [], 'admin'));
        if ($pageName === Crud::PAGE_EDIT && $this->isThumbnailExist()) {
            $postThumbnailField->setRequired(false);
        }
        yield $postThumbnailField;
        yield CkeditorField::new('content', t('content.label', [], 'admin'));
        $isVisible = BooleanField::new('isVisible', t('is_visible.label', [], 'admin'))
            ->setPermission('ROLE_ADMIN');
        if ($pageName === Crud::PAGE_INDEX) {
            $isVisible->renderAsSwitch(false);
        };
        yield $isVisible;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add(EntityFilter::new('user'));
        }
        $filters->add(EntityFilter::new('category'))
                ->add(DateTimeFilter::new('createdAt'))
                ->add(BooleanFilter::new('isVisible'));
        
        return $filters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $queryBuilder->where('entity.user = :user')
                        ->setParameter('user', $this->getUser());
        }

        return $queryBuilder;
    }

    public function isThumbnailExist(): bool
    {
        $post = $this->getContext()->getEntity()->getInstance();
        
        if (!$post || !$post->getThumbnail()) {
            return false;
        }
        
        return true;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Post) {
            $entityInstance
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            
            if (!$this->isGranted('ROLE_ADMIN')) {
                $entityInstance->setIsVisible(false);
            }
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }
}

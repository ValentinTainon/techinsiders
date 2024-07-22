<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use App\Admin\Field\CkeditorField;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Translation\TranslatableMessage;
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
            ->addFormTheme('bundles/EasyAdminBundle/crud/field/ckeditor_init.html.twig')
            ->setDefaultSort(['created_at' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->setPermissions([
                Action::EDIT => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DETAIL => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DELETE => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))')
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', 'Auteur')
            ->onlyOnIndex();
        yield AssociationField::new('category', 'Categorie');
        yield DateTimeField::new('created_at', 'Date de création')
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);
        yield TextField::new('title', new TranslatableMessage('title', [], 'admin'));
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setFormTypeOption('row_attr', ['style' => 'display: none;']);
        $postThumbnailField = ImageField::new('thumbnail', 'Miniature')
            ->setBasePath('uploads/posts/thumbnails')
            ->setUploadDir('public/uploads/posts/thumbnails')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setHelp('Upload an image with a maximum size of 2MB.');
        if ($pageName === Crud::PAGE_EDIT && $this->isThumbnailExist()) {
            $postThumbnailField->setRequired(false);
        }
        yield $postThumbnailField;
        yield CkeditorField::new('content', 'Contenu');
        $isVisible = BooleanField::new('is_visible', 'Visible')->setPermission('ROLE_ADMIN');
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
                ->add(DateTimeFilter::new('created_at'))
                ->add(BooleanFilter::new('is_visible'));
        
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

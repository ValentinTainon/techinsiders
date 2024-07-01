<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Translation\TranslatableMessage;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

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

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add(EntityFilter::new('user'));
        }
        $filters->add(EntityFilter::new('category'));
        
        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', 'Auteur')
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);
        yield AssociationField::new('category', 'Categorie');
        yield DateTimeField::new('created_at', 'Date de création')
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);
        yield TextField::new('title', new TranslatableMessage('title', [], 'admin'));
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName(['title'])
            ->setHelp('Doit correspondre au champ Titre');
        $postThumbnailField = ImageField::new('thumbnail', 'Vignette')
            ->setBasePath('uploads/posts/thumbnails')
            ->setUploadDir('public/uploads/posts/thumbnails')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setHelp('Upload an image with a maximum size of 2MB.');
        if ($pageName === Crud::PAGE_EDIT && $this->isThumbnailExist()) {
            $postThumbnailField->setRequired(false);
        }
        yield $postThumbnailField;
        $postContentField = TextareaField::new('content', 'Contenu')
            ->setFormTypeOptions([
                'block_name' => 'custom_content',
                'attr' => [
                    'data-locale' => $this->getContext()->getRequest()->getLocale(),
                ]
            ])
            ->onlyOnForms();
        if ($pageName === Crud::PAGE_EDIT) {
            $postContentField->setFormTypeOption('attr.data-content', $this->getPostContent());
        }
        yield $postContentField;
        $isVisible = BooleanField::new('is_visible', 'Visible');
        if ($pageName === Crud::PAGE_INDEX) {
            $isVisible->renderAsSwitch(false);
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            $isVisible->hideOnForm();
        }
        yield $isVisible;
    }
        
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Post) {
            $entityInstance
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTimeImmutable('now'));
            
            if (!$this->isGranted('ROLE_ADMIN')) {
                $entityInstance->setIsVisible(false);
            }
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function isThumbnailExist(): bool
    {
        $post = $this->getContext()->getEntity()->getInstance();

        if (!$post || !$post->getThumbnail()) {
            return false;
        }

        return true;
    }

    public function getPostContent(): ?string
    {
        $post = $this->getContext()->getEntity()->getInstance();

        if (!$post || !$post->getContent()) {
            return null;
        }

        return $post->getContent();
    }
}

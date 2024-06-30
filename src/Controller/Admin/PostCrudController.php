<?php

namespace App\Controller\Admin;

use App\Entity\Post;
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
        return $filters
            ->add(EntityFilter::new('author'))
            ->add(EntityFilter::new('category'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('author', 'Auteur');
        yield AssociationField::new('category', 'Categorie');
        yield DateTimeField::new('created_at', 'Date de création')
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);
        yield TextField::new('title', new TranslatableMessage('title', [], 'admin'));
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->addWebpackEncoreEntries('custom-slug-field');
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
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente de l\'approbation de l\'administrateur' => 'PENDING',
                'Validé' => 'VALIDATED',
                'Rejeté' => 'REJECTED',
            ]);
    }

    public function getPostContent(): ?string
    {
        $post = $this->getContext()->getEntity()->getInstance();

        if (!$post || !$post->getContent()) {
            return null;
        }

        return $post->getContent();
    }

    public function isThumbnailExist(): bool
    {
        $post = $this->getContext()->getEntity()->getInstance();

        if (!$post || !$post->getThumbnail()) {
            return false;
        }

        return true;
    }
}

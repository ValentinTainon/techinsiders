<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
        return $crud->setFormThemes(['bundles/EasyAdminBundle/crud/field/ckeditor.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('author', 'Auteur')
            ->setDisabled();
        yield AssociationField::new('category', 'Categorie');
        yield TextField::new('title', 'Titre');
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName(['category', 'title']);
        yield TextareaField::new('content', 'Contenu')
            ->setFormTypeOptions([
                'block_name' => 'custom_content',
            ])
            ->addWebpackEncoreEntries('ckeditor_init');
        yield ImageField::new('thumbnail', 'Vignette')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp('Upload an image with a maximum size of 2MB.');
        yield DateTimeField::new('created_at', 'Date de création')->hideWhenCreating()->setDisabled();
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Valider' => 'VALIDATED',
                'Rejeter' => 'REJECTED',
            ]);
    }
}

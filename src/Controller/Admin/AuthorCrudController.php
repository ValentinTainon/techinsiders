<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nickname', 'Pseudo');
        yield TextField::new('email', 'Email');
        yield TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe', 'hash_property_path' => 'password'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                'mapped' => false,
                'constraints' => [
                    new Length(['max' => 4096]), // max length allowed by Symfony for security reasons
                    new Regex([
                        'pattern' => '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&.*-]).{8,}$/',
                        'message' => 'Votre mot de passe doit contenir au minimum 8 caractères avec au moins une lettre majuscule,
                        une lettre minuscule, un chiffre et un caractère spécial.'
                    ]),
                ],
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyWhenCreating();
        yield ImageField::new('avatar', 'Avatar')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp('Upload an image with a maximum size of 2MB.');
        yield TextareaField::new('about', 'À propos');
    }
}

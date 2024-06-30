<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Validator\Constraints\Regex;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nickname', 'Pseudo');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'Editor' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices(true)
            ->setRequired(true);
        yield EmailField::new('email', 'Email');
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
            ->onlyOnForms();
        yield ImageField::new('avatar', 'Avatar')
            ->setBasePath('uploads/authors/avatars')
            ->setUploadDir('public/uploads/authors/avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp('Upload an image with a maximum size of 2MB.');
        yield TextareaField::new('about', 'À propos de vous');
        yield TextField::new('checkPassword')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'label' => 'Afin de valider les modifications, veuillez saisir votre mot de passe actuel',
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => 'Votre mot de passe actuel ne correspond pas',
                    ])
                ],
            ])
            ->onlyWhenUpdating()
            ->setRequired(true);
    }
}

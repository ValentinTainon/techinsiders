<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Validator\Constraints\Regex;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Validator\Constraints\Length;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->setPermissions([
                Action::INDEX => 'ROLE_SUPER_ADMIN',
                Action::NEW => 'ROLE_SUPER_ADMIN',
                Action::EDIT => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DETAIL => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))'),
                Action::DELETE => new Expression('is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and (is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")))')
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nickname', 'Pseudo');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Super Admin' => 'ROLE_SUPER_ADMIN',
                'Admin' => 'ROLE_ADMIN',
                'Editor' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices(true)
            ->setRequired(true)
            ->setPermission('ROLE_SUPER_ADMIN');
        yield EmailField::new('email', 'Email');
        yield TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nouveau mot de passe', 'hash_property_path' => 'password'],
                'second_options' => ['label' => 'Répéter le nouveau mot de passe'],
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
            ->setBasePath('uploads/users/avatars')
            ->setUploadDir('public/uploads/users/avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp('Upload an image with a maximum size of 2MB.');
        yield TextareaField::new('about', 'À propos de vous');
        yield TextField::new('checkPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => 'Votre mot de passe actuel ne correspond pas',
                    ])
                ],
            ])
            ->setHelp('Afin de valider les modifications, veuillez saisir votre mot de passe actuel')
            ->onlyWhenUpdating()
            ->setRequired(true);
    }
}

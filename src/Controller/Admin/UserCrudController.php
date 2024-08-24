<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\Field\PasswordField;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('user.label.singular', [], 'admin'))
            ->setEntityLabelInPlural(t('user.label.plural', [], 'admin'))
            ->setPageTitle('new', t('create.user', [], 'admin'))
            ->setPageTitle('edit', t('edit.user', [], 'admin'))
            ->setDefaultSort(['username' => 'ASC']);
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
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.user', [], 'admin')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.user.label', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'admin')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', t('username.label', [], 'admin'));
        yield ChoiceField::new('roles', t('roles.label', [], 'admin'))
            ->setChoices([
                'Super Admin' => 'ROLE_SUPER_ADMIN',
                'Admin' => 'ROLE_ADMIN',
                'Editor' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices(true)
            ->setRequired(true)
            ->setPermission('ROLE_SUPER_ADMIN');
        yield EmailField::new('email', t('email.label', [], 'admin'));
        $passwordField = PasswordField::new('plainPassword')
            ->setRequired($pageName === Crud::PAGE_NEW);
        if($pageName === Crud::PAGE_EDIT) {
            $passwordField->setFormTypeOptions([
                'first_options' => [
                    'label' => t('new.password.label', [], 'admin')
                ],
                'second_options' => [
                    'label' => t('repeat.new.password.label', [], 'admin')
                ]
            ]);
        }
        yield $passwordField;
        yield ImageField::new('avatar', t('avatar.label', [], 'admin'))
            ->setBasePath('uploads/users/avatars')
            ->setUploadDir('public/uploads/users/avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp(t('image.field.help.message', [], 'admin'));
        yield TextareaField::new('about', t('about.label', [], 'admin'));
        yield TextField::new('userPassword', t('password.label', [], 'admin'))
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => t('check.user.password.constraint.message', [], 'validators'),
                    ])
                ],
            ])
            ->setHelp(t('check.user.password.help.message', [], 'admin'))
            ->onlyWhenUpdating()
            ->setRequired(true);
    }
}

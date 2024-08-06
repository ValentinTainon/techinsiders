<?php

namespace App\Controller\Admin;

use App\Entity\User;
use function Symfony\Component\Translation\t;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('admin.entity.label.singular.user', [], 'admin'))
            ->setEntityLabelInPlural(t('admin.entity.label.plural.user', [], 'admin'))
            ->setPageTitle('new', t('admin.page.new.title.user', [], 'admin'))
            ->setPageTitle('edit', t('admin.page.edit.title.user', [], 'admin'))
            ->setDefaultSort(['nickname' => 'ASC']);
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
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('admin.action.new.user', [], 'admin')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('admin.action.save_and_add_another.user', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('admin.action.save_and_continue', [], 'admin')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('admin.action.save', [], 'admin')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nickname', t('admin.form.label.nickname', [], 'admin'));
        yield ChoiceField::new('roles', t('admin.form.label.roles', [], 'admin'))
            ->setChoices([
                'Super Admin' => 'ROLE_SUPER_ADMIN',
                'Admin' => 'ROLE_ADMIN',
                'Editor' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices(true)
            ->setRequired(true)
            ->setPermission('ROLE_SUPER_ADMIN');
        yield EmailField::new('email', t('admin.form.label.email', [], 'admin'));
        yield TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => $pageName === Crud::PAGE_EDIT ? t('admin.form.label.password.new', [], 'admin') : t('admin.form.label.password', [], 'admin'), 
                    'hash_property_path' => 'password'
                ],
                'second_options' => ['label' => $pageName === Crud::PAGE_EDIT ? t('admin.form.label.password.new.repeat', [], 'admin') : t('admin.form.label.password.repeat', [], 'admin')], 
                'mapped' => false,
                'constraints' => [
                    new Length(['max' => 4096]), // max length allowed by Symfony for security reasons
                    new Regex([
                        'pattern' => '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&.,;*-+=_:§]).{8,}$/',
                        'message' => t('admin.form.message.password', [], 'admin')
                    ]),
                ],
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms();
        yield ImageField::new('avatar', t('admin.form.label.avatar', [], 'admin'))
            ->setBasePath('uploads/users/avatars')
            ->setUploadDir('public/uploads/users/avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp(t('admin.form.help.imageField', [], 'admin'));
        yield TextareaField::new('about', t('admin.form.label.about', [], 'admin'));
        yield TextField::new('checkPassword', t('admin.form.label.password', [], 'admin'))
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => t('admin.form.message.checkPassword', [], 'admin'),
                    ])
                ],
            ])
            ->setHelp(t('admin.form.help.checkPassword', [], 'admin'))
            ->onlyWhenUpdating()
            ->setRequired(true);
    }
}

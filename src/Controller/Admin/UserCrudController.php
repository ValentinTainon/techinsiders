<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use App\Service\EmailService;
use App\Config\UserAvatarConfig;
use App\Security\Voter\UserVoter;
use App\Form\Admin\Field\PasswordField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private EmailService $emailService,
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('user.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('user.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_NEW, t('create.user', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_EDIT, t('edit.user', [], 'EasyAdminBundle'))
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermissions([
                Action::EDIT => UserVoter::EDIT,
                Action::DETAIL => UserVoter::DETAIL,
                Action::DELETE => UserVoter::DELETE,
                Action::BATCH_DELETE => UserVoter::BATCH_DELETE
            ])
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action): Action =>
                $action->setLabel(t('create.user', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-user-plus')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action): Action =>
                $action->setIcon('fa fa-user-edit')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn(Action $action): Action =>
                $action->setIcon('fa fa-user-minus')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action): Action =>
                $action->setLabel(t('create_and_add.user.label', [], 'EasyAdminBundle'))
            );
    }

    public function configureFields(string $pageName): iterable
    {
        $userAvatarConfig = UserAvatarConfig::getConfig();

        yield FormField::addFieldset()->addCssClass('custom-max-width');
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission(UserRole::SUPER_ADMIN->value);

        yield TextField::new('username', t('username.label', [], 'forms'))
            ->setColumns('col-sm-6 col-md-5');

        yield EmailField::new('email', t('email.label', [], 'forms'))
            ->setRequired(true)
            ->setColumns('col-sm-6 col-md-5');

        $passwordField = PasswordField::new('plainPassword')
            ->setRequired($pageName === Crud::PAGE_NEW);

        if ($pageName === Crud::PAGE_EDIT) {
            $passwordField->setFormTypeOptions([
                'first_options' => [
                    'label' => t('new.password.label', [], 'forms')
                ],
                'second_options' => [
                    'label' => t('repeat.new.password.label', [], 'forms')
                ]
            ]);
        }

        yield FormField::addRow();
        yield $passwordField;

        yield FormField::addRow();
        yield ChoiceField::new('role', t('role.label', [], 'forms'))
            ->setChoices([
                UserRole::SUPER_ADMIN->label($this->translator) => UserRole::SUPER_ADMIN,
                UserRole::ADMIN->label($this->translator) => UserRole::ADMIN,
                UserRole::EDITOR->label($this->translator) => UserRole::EDITOR,
                UserRole::USER->label($this->translator) => UserRole::USER,
                UserRole::GUEST->label($this->translator) => UserRole::GUEST,
            ])
            ->renderAsBadges(
                [
                    UserRole::SUPER_ADMIN->value => 'danger',
                    UserRole::ADMIN->value => 'warning',
                    UserRole::EDITOR->value => 'success',
                    UserRole::USER->value => 'primary',
                    UserRole::GUEST->value => 'secondary',
                ]
            )
            ->hideWhenCreating()
            ->setColumns('col-sm-6 col-md-5')
            ->setDisabled(!$this->isGranted(UserRole::SUPER_ADMIN->value));

        yield IntegerField::new('postsCount', t('posts.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setTextAlign('center')
            ->setColumns('col-sm-6 col-md-5');

        yield FormField::addRow();
        yield ImageField::new('avatar', t('avatar.label', [], 'forms'))
            ->setBasePath($userAvatarConfig->basePath())
            ->setUploadDir($userAvatarConfig->uploadDir())
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true,
                    maxSize: $userAvatarConfig->maxFileSize(),
                    mimeTypes: $userAvatarConfig->allowedMimeTypes()
                )
            )
            ->setHelp(
                t(
                    'image.field.help.message',
                    [
                        '%formats%' => $userAvatarConfig->allowedMimeTypesExtensions(),
                        '%size%' => $userAvatarConfig->maxFileSize()
                    ],
                    'forms'
                )
            )
            ->setColumns(10);

        yield TextareaField::new('about', t('about.label', [], 'forms'))
            ->hideOnIndex()
            ->hideWhenCreating()
            ->setColumns(10);

        yield BooleanField::new('isVerified', t('is_verified.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->onlyOnIndex()
            ->setPermission(UserRole::SUPER_ADMIN->value);

        yield TextField::new('userPassword', t('password.label', [], 'forms'))
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => t('check_user_password.constraint.message', [], 'validators'),
                    ])
                ],
            ])
            ->setHelp(t('check.user.password.help.message', [], 'forms'))
            ->onlyWhenUpdating()
            ->setRequired(true)
            ->setColumns('col-sm-6 col-md-5')
            ->setDisabled($this->isGranted(UserRole::SUPER_ADMIN->value));
    }

    public function configureFilters(Filters $filters): Filters
    {
        if (!$this->isGranted(UserRole::SUPER_ADMIN->value)) {
            return $filters;
        }

        return $filters->add(
            ChoiceFilter::new('role', t('role.label', [], 'forms'))
                ->setTranslatableChoices([
                    UserRole::SUPER_ADMIN->value => UserRole::SUPER_ADMIN->label($this->translator),
                    UserRole::ADMIN->value => UserRole::ADMIN->label($this->translator),
                    UserRole::EDITOR->value => UserRole::EDITOR->label($this->translator),
                    UserRole::USER->value => UserRole::USER->label($this->translator),
                    UserRole::GUEST->value => UserRole::GUEST->label($this->translator),
                ])
        )
            ->add(BooleanFilter::new('isVerified', t('is_verified.label', [], 'forms')));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) return;

        $avatar = $entityInstance->getAvatar();

        parent::deleteEntity($entityManager, $entityInstance);

        if ($avatar !== null) {
            $this->deleteUserAvatar($avatar);
        }

        $this->security->logout(false);

        $this->addFlash('success', t('user_account_deleted', [], 'flashes'));
    }

    private function deleteUserAvatar(string $avatar): void
    {
        $filesystem = new Filesystem();
        $userAvatarPath = "{$this->getParameter('kernel.project_dir')}/public/uploads/images/users/avatars/{$avatar}";

        if ($filesystem->exists($userAvatarPath)) {
            $filesystem->remove($userAvatarPath);
        }
    }
}

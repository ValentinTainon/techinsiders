<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\EmailService;
use App\Form\Admin\Field\PasswordField;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    public const string AVATAR_BASE_PATH = 'uploads/images/users/avatars/';
    public const string AVATAR_UPLOAD_DIR = 'public/' . self::AVATAR_BASE_PATH;
    public const string DEFAULT_IMAGES_DIR = '../assets/images/default/';
    private const string AVATAR_MAX_FILE_SIZE = '500k';
    private const string EA_USER_EMAILS_DIR = 'bundles/EasyAdminBundle/crud/user/emails/';

    public function __construct(
        private EmailService $emailService,
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy
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
        $assignEditorRole = Action::new('assignEditorRole')
            ->setLabel(t('assign_to_editor_group.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-success')
            ->displayIf(
                fn(User $subject): bool =>
                $this->canBeAssignEditorRole($subject)
            );

        $assignAdminRole = Action::new('assignAdminRole')
            ->setLabel(t('promote_to_admin.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-success')
            ->displayIf(
                fn(User $subject): bool =>
                $this->canBeAssignAdminRole($subject)
            );

        $reassignEditorRole = Action::new('reassignEditorRole')
            ->setLabel(t('reassign_to_editor_group.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-minus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-danger')
            ->displayIf(
                fn(User $subject): bool =>
                $this->canBeReassignEditorRole($subject)
            );

        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (user === subject and is_granted("ROLE_EDITOR"))'
        );

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $assignEditorRole)
            ->add(Crud::PAGE_EDIT, $assignAdminRole)
            ->add(Crud::PAGE_EDIT, $reassignEditorRole)
            ->setPermissions([
                Action::INDEX => 'ROLE_SUPER_ADMIN',
                Action::NEW => 'ROLE_SUPER_ADMIN',
                Action::EDIT => $expression,
                Action::DETAIL => $expression,
                Action::DELETE => $expression,
                Action::BATCH_DELETE => $expression
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
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn(Action $action): Action =>
                $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle'))
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn(Action $action): Action =>
                $action->setLabel(t('save.label', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-save')
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset()->addCssClass('custom-max-width');
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission('ROLE_SUPER_ADMIN');

        yield TextField::new('username', t('username.label', [], 'forms'))
            ->setColumns('col-sm-6 col-md-5');

        yield EmailField::new('email', t('email.label', [], 'forms'))
            ->setRequired(true)
            ->setColumns('col-sm-6 col-md-5');

        yield FormField::addRow();
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

        yield $passwordField;

        yield FormField::addRow();
        yield ChoiceField::new('roles', t('roles.label', [], 'forms'))
            ->setTranslatableChoices([
                'ROLE_SUPER_ADMIN' => t('roles.super_admin.label', [], 'forms'),
                'ROLE_ADMIN' => t('roles.admin.label', [], 'forms'),
                'ROLE_EDITOR' => t('roles.editor.label', [], 'forms'),
            ])
            ->allowMultipleChoices(true)
            ->hideWhenCreating()
            ->setDisabled()
            ->setColumns('col-sm-6 col-md-5');

        yield IntegerField::new('postsCount', t('posts.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setTextAlign('center')
            ->setColumns('col-sm-6 col-md-5');

        yield FormField::addRow();
        yield ImageField::new('avatar', t('avatar.label', [], 'forms'))
            ->setBasePath(self::AVATAR_BASE_PATH)
            ->setUploadDir($this->hasDefaultAvatar() ? self::DEFAULT_IMAGES_DIR : self::AVATAR_UPLOAD_DIR)
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFormTypeOptions([
                'allow_delete' => $pageName === Crud::PAGE_EDIT && !$this->hasDefaultAvatar(),
                'upload_delete' =>
                fn(File $file) =>
                $file->getFilename() !== User::DEFAULT_USER_AVATAR_FILE_NAME ? unlink($file->getPathname()) : null
            ])
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true,
                    maxSize: self::AVATAR_MAX_FILE_SIZE,
                    mimeTypes: [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/svg+xml',
                        'image/gif',
                    ]
                )
            )
            ->setHelp(t('avatar.field.help.message', ['%size%' => self::AVATAR_MAX_FILE_SIZE], 'forms'))
            ->hideWhenCreating()
            ->setColumns(10);

        yield TextareaField::new('about', t('about.label', [], 'forms'))
            ->hideOnIndex()
            ->hideWhenCreating()
            ->setColumns(10);

        yield BooleanField::new('isVerified', t('is_verified.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->onlyOnIndex()
            ->setPermission('ROLE_SUPER_ADMIN');

        yield BooleanField::new('isGuest', t('is_guest.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->onlyOnIndex()
            ->setPermission('ROLE_SUPER_ADMIN');

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
            ->setColumns('col-sm-6 col-md-5');
    }

    public function hasDefaultAvatar(): bool
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof User) {
            return false;
        }

        return $entityInstance->getAvatar() === User::DEFAULT_USER_AVATAR_FILE_NAME;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $filters;
        }

        return $filters->add(
            ArrayFilter::new('roles', t('roles.label', [], 'forms'))
                ->setTranslatableChoices([
                    'ROLE_SUPER_ADMIN' => t('roles.super_admin.label', [], 'forms'),
                    'ROLE_ADMIN' => t('roles.admin.label', [], 'forms'),
                    'ROLE_EDITOR' => t('roles.editor.label', [], 'forms'),
                ])
        )
            ->add(BooleanFilter::new('isVerified', t('is_verified.label', [], 'forms')))
            ->add(BooleanFilter::new('isGuest', t('is_guest.label', [], 'forms')));
    }

    private function canBeAssignEditorRole(User $subject): bool
    {
        return $this->isGranted('ROLE_SUPER_ADMIN')
            && (!in_array('ROLE_EDITOR', $this->roleHierarchy->getReachableRoleNames($subject->getRoles()), true));
    }

    private function canBeReassignEditorRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted('ROLE_SUPER_ADMIN')
            && in_array('ROLE_ADMIN', $subjectRoles, true)
            && !in_array('ROLE_SUPER_ADMIN', $subjectRoles, true);
    }

    private function canBeAssignAdminRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted('ROLE_SUPER_ADMIN')
            && in_array('ROLE_EDITOR', $subjectRoles, true)
            && !in_array('ROLE_ADMIN', $subjectRoles, true);
    }

    private function manageUserRole(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $subject = $context->getEntity()->getInstance();

        if (!$subject || !$subject instanceof User) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));
            return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->generateUrl());
        } elseif (!$this->canBeAssignEditorRole($subject) && !$this->canBeAssignAdminRole($subject)) {
            $this->addFlash('danger', t('user_cannot_be_assign_to_a_specific_role', [], 'flashes'));
            return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->generateUrl());
        }

        if ($this->canBeAssignEditorRole($subject) || $this->canBeReassignEditorRole($subject)) {
            $isAdmin = in_array('ROLE_ADMIN', $this->roleHierarchy->getReachableRoleNames($subject->getRoles()), true);
            $role = 'ROLE_EDITOR';
            $emailSubject = sprintf('%sassigned_to_editor_group.subject', $isAdmin ? 're' : '');
            $emailTemplate = sprintf(self::EA_USER_EMAILS_DIR . '%sassigned_to_editor_group.html.twig', $isAdmin ? 're' : '');
            $flashMessage = t(sprintf('%sassigned_to_editor_group', $isAdmin ? 're' : ''), [], 'flashes');
        } elseif ($this->canBeAssignAdminRole($subject)) {
            $role = 'ROLE_ADMIN';
            $emailSubject = 'promoted_to_admin.subject';
            $emailTemplate = self::EA_USER_EMAILS_DIR . 'promoted_to_admin.html.twig';
            $flashMessage = t('editor_promoted_to_admin', [], 'flashes');
        }

        $subject->setRoles([$role]);
        $entityManager->persist($subject);
        $entityManager->flush();

        $this->emailService->sendTemplatedEmail(
            $subject->getEmail(),
            $subject->getUsername(),
            $emailSubject,
            $emailTemplate,
            ['username' => $subject->getUsername()]
        );

        $this->addFlash('success', $flashMessage);

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::EDIT)
                ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                ->generateUrl()
        );
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->emailService->sendRegistrationConfirmationEmail(
                $entityInstance,
                $entityInstance->getEmail(),
                $entityInstance->getUsername(),
                'confirm_email.subject',
                'registration/confirmation_email.html.twig',
                ['username' => $entityInstance->getUsername()]
            );
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\MimeType;
use App\Enum\UserRole;
use App\Service\PathService;
use App\Service\EmailService;
use App\Form\Admin\Field\PasswordField;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Response;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    private const string AVATAR_MAX_FILE_SIZE = '500k';

    public function __construct(
        private EmailService $emailService,
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy,
        private PathService $pathService,
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
            sprintf(
                'is_granted("%s") or (user === subject and is_granted("%s"))',
                UserRole::SUPER_ADMIN->value,
                UserRole::EDITOR->value
            )
        );

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $assignEditorRole)
            ->add(Crud::PAGE_EDIT, $assignAdminRole)
            ->add(Crud::PAGE_EDIT, $reassignEditorRole)
            ->setPermissions([
                Action::INDEX => UserRole::SUPER_ADMIN->value,
                Action::NEW => UserRole::SUPER_ADMIN->value,
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
            ->setRequired(false)
            ->setDisabled()
            ->setColumns('col-sm-6 col-md-5');

        yield IntegerField::new('postsCount', t('posts.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setTextAlign('center')
            ->setColumns('col-sm-6 col-md-5');

        yield FormField::addRow();
        yield ImageField::new('avatar', t('avatar.label', [], 'forms'))
            ->setBasePath(PathService::USERS_AVATAR_BASE_PATH)
            ->setUploadDir(PathService::USERS_AVATAR_UPLOAD_DIR)
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true,
                    maxSize: self::AVATAR_MAX_FILE_SIZE,
                    mimeTypes: [
                        MimeType::JPEG->value,
                        MimeType::PNG->value,
                        MimeType::WEBP->value,
                        MimeType::SVG->value,
                        MimeType::GIF->value,
                    ]
                )
            )
            ->setHelp(
                t(
                    'image.field.help.message',
                    [
                        '%formats%' => implode(
                            ', ',
                            array_merge(
                                MimeType::JPEG->extensions(),
                                MimeType::PNG->extensions(),
                                MimeType::WEBP->extensions(),
                                MimeType::SVG->extensions(),
                                MimeType::GIF->extensions(),
                            )
                        ),
                        '%size%' => self::AVATAR_MAX_FILE_SIZE
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
            ->setColumns('col-sm-6 col-md-5');
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

    private function canBeAssignEditorRole(User $subject): bool
    {
        return $this->isGranted(UserRole::SUPER_ADMIN->value)
            && (!in_array(UserRole::EDITOR->value, $this->roleHierarchy->getReachableRoleNames($subject->getRoles()), true));
    }

    private function canBeReassignEditorRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted(UserRole::SUPER_ADMIN->value)
            && in_array(UserRole::ADMIN->value, $subjectRoles, true)
            && !in_array(UserRole::SUPER_ADMIN->value, $subjectRoles, true);
    }

    private function canBeAssignAdminRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted(UserRole::SUPER_ADMIN->value)
            && in_array(UserRole::EDITOR->value, $subjectRoles, true)
            && !in_array(UserRole::ADMIN->value, $subjectRoles, true);
    }

    private function manageUserRole(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $subject = $context->getEntity()->getInstance();

        if (!$subject || !$subject instanceof User) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));
            return $this->redirectToRoute('admin_user_index');
        } elseif (!$this->canBeAssignEditorRole($subject) && !$this->canBeAssignAdminRole($subject)) {
            $this->addFlash('danger', t('user_cannot_be_assign_to_a_specific_role', [], 'flashes'));
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->canBeAssignEditorRole($subject) || $this->canBeReassignEditorRole($subject)) {
            $isAdmin = in_array(UserRole::ADMIN->value, $this->roleHierarchy->getReachableRoleNames([$subject->getRole()]), true);
            $role = UserRole::EDITOR;
            $emailSubject = sprintf('%sassigned_to_editor_group.subject', $isAdmin ? 're' : '');
            $emailTemplate = sprintf('%sassigned_to_editor_group.html.twig', $isAdmin ? 're' : '');
            $flashMessage = t(sprintf('%sassigned_to_editor_group', $isAdmin ? 're' : ''), [], 'flashes');
        } elseif ($this->canBeAssignAdminRole($subject)) {
            $role = UserRole::ADMIN;
            $emailSubject = 'promoted_to_admin.subject';
            $emailTemplate = 'promoted_to_admin.html.twig';
            $flashMessage = t('editor_promoted_to_admin', [], 'flashes');
        }

        $subject->setRole($role);
        $entityManager->persist($subject);
        $entityManager->flush();

        $this->emailService->sendEmailToUser(
            $subject->getEmail(),
            $subject->getUsername(),
            $emailSubject,
            $emailTemplate,
            [],
            ['username' => $subject->getUsername()]
        );

        $this->addFlash('success', $flashMessage);

        return $this->redirectToRoute(
            'admin_user_edit',
            [
                'entityId' => $context->getEntity()->getPrimaryKeyValue()
            ]
        );
    }
}

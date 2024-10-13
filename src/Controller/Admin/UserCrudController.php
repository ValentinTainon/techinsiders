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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
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
    private const string DEFAULT_AVATAR_FILE_NAME = 'default-avatar.svg';

    private const string MAX_AVATAR_FILE_SIZE = '500k';

    public function __construct(
        private EmailService $emailService, 
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy
    )
    {
    }
    
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    private function userInstance(): ?User
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if ($entityInstance instanceof User) {
            return $entityInstance;
        }

        return null;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('user.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('user.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle('new', t('create.user', [], 'EasyAdminBundle'))
            ->setPageTitle('edit', t('edit.user', [], 'EasyAdminBundle'))
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $assignUserToEditorGroupAction = Action::new('assignUserToEditorGroupAction')
            ->setLabel(t('assign_to_editor_group.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-success')
            ->displayIf(fn (User $subject): bool => 
                $this->canBeAssignedToEditorRole($subject)
            );

        $promoteEditorToAdminAction = Action::new('promoteEditorToAdminAction')
            ->setLabel(t('promote_to_admin.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-success')
            ->displayIf(fn (User $subject): bool => 
                $this->canBeAssignedToAdminRole($subject)
            );

        $reassignAdminToEditorGroupAction = Action::new('reassignAdminToEditorGroupAction')
            ->setLabel(t('reassign_to_editor_group.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-minus')
            ->linkToCrudAction('manageUserRole')
            ->addCssClass('btn btn-danger')
            ->displayIf(fn (User $subject): bool => 
                $this->canBeAssignedToEditorRole($subject)
        );

        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and is_granted("ROLE_EDITOR"))'
        );

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $assignUserToEditorGroupAction)
            ->add(Crud::PAGE_EDIT, $promoteEditorToAdminAction)
            ->add(Crud::PAGE_EDIT, $reassignAdminToEditorGroupAction)
            ->setPermissions([
                Action::INDEX => 'ROLE_SUPER_ADMIN',
                Action::NEW => 'ROLE_SUPER_ADMIN',
                Action::EDIT => $expression,
                Action::DETAIL => $expression,
                Action::DELETE => $expression
            ])
            ->update(
                Crud::PAGE_INDEX, 
                Action::NEW, fn (Action $action): Action => 
                $action->setLabel(t('create.user', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-user-plus')
            )
            ->update(
                Crud::PAGE_INDEX, 
                Action::EDIT, fn (Action $action): Action => 
                $action->setIcon('fa fa-user-edit')
            )
            ->update(
                Crud::PAGE_INDEX, 
                Action::DELETE, fn (Action $action): Action => 
                $action->setIcon('fa fa-user-minus')
            )
            ->update(Crud::PAGE_NEW, 
                Action::SAVE_AND_ADD_ANOTHER, fn (Action $action): Action => 
                $action->setLabel(t('create_and_add.user.label', [], 'EasyAdminBundle'))
            )
            ->update(Crud::PAGE_EDIT, 
                Action::SAVE_AND_CONTINUE, fn (Action $action): Action => 
                $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle'))
            )
            ->update(Crud::PAGE_EDIT, 
                Action::SAVE_AND_RETURN, fn (Action $action): Action => 
                $action->setLabel(t('save.label', [], 'EasyAdminBundle'))
                    ->setIcon('fa fa-save')
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission('ROLE_SUPER_ADMIN');
            
        yield TextField::new('username', t('username.label', [], 'forms'));

        yield ChoiceField::new('roles', t('roles.label', [], 'forms'))
            ->setTranslatableChoices([
                'ROLE_SUPER_ADMIN' => t('roles.super_admin.label', [], 'forms'),
                'ROLE_ADMIN' => t('roles.admin.label', [], 'forms'),
                'ROLE_EDITOR' => t('roles.editor.label', [], 'forms'),
            ])
            ->allowMultipleChoices(true)
            ->hideWhenCreating()
            ->setDisabled();

        yield EmailField::new('email', t('email.label', [], 'forms'))
            ->setRequired(true);

        $passwordField = PasswordField::new('plainPassword')
            ->setRequired($pageName === Crud::PAGE_NEW);

        if($pageName === Crud::PAGE_EDIT) {
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

        yield ImageField::new('avatar', t('avatar.label', [], 'forms'))
            ->setBasePath('uploads/images/users/avatars')
            ->setUploadDir($this->getParameter('uploads_images_relative_path').'/users/avatars')
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFormTypeOptions([
                'empty_data' => '',
                'allow_delete' => $pageName === Crud::PAGE_EDIT && $this->userInstance()->getAvatar() !== self::DEFAULT_AVATAR_FILE_NAME,
                'upload_delete' => 
                    fn (File $file) => 
                        $file->getFilename() !== self::DEFAULT_AVATAR_FILE_NAME ? unlink($file->getPathname()) : null
            ])
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true, 
                    maxSize: self::MAX_AVATAR_FILE_SIZE, 
                    mimeTypes: [
                        'image/jpeg', 
                        'image/png', 
                        'image/webp', 
                        'image/svg+xml', 
                        'image/gif',
                    ]
                )
            )
            ->setHelp(t('avatar.field.help.message', ['%size%' => self::MAX_AVATAR_FILE_SIZE], 'forms'))
            ->setRequired(false);

        yield TextareaField::new('about', t('about.label', [], 'forms'))
            ->hideOnIndex();

        yield BooleanField::new('isVerified', t('is_verified.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->setDisabled()
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
            ->setRequired(true);
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add(
                ArrayFilter::new('roles', t('roles.label', [], 'forms'))
                ->setTranslatableChoices([
                    'ROLE_SUPER_ADMIN' => t('roles.super_admin.label', [], 'forms'),
                    'ROLE_ADMIN' => t('roles.admin.label', [], 'forms'),
                    'ROLE_EDITOR' => t('roles.editor.label', [], 'forms'),
                ])
            )
            ->add(BooleanFilter::new('isVerified', t('is_verified.label', [], 'forms')));
        }
        
        return $filters;
    }

    private function canBeAssignedToEditorRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted('ROLE_SUPER_ADMIN') 
            && (!in_array('ROLE_EDITOR', $subjectRoles, true)
            || in_array('ROLE_ADMIN', $subjectRoles, true)
            && !in_array('ROLE_SUPER_ADMIN', $subjectRoles, true));
    }

    private function canBeAssignedToAdminRole(User $subject): bool
    {
        $subjectRoles = $this->roleHierarchy->getReachableRoleNames($subject->getRoles());

        return $this->isGranted('ROLE_SUPER_ADMIN') 
            && in_array('ROLE_EDITOR', $subjectRoles, true) 
            && !in_array('ROLE_ADMIN', $subjectRoles, true);
    }

    public function manageUserRole(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $subject = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $crudUrl = $adminUrlGenerator->setController(self::class)->setAction('edit')->generateUrl();
        $isAdmin = in_array('ROLE_ADMIN', 
            $this->roleHierarchy->getReachableRoleNames($subject->getRoles()), 
            true
        );

        if (!$subject) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));

            return $this->redirect($crudUrl);
        }

        if ($this->canBeAssignedToEditorRole($subject)) {
            $subject->setRoles(['ROLE_EDITOR']);
            $entityManager->persist($subject);
            $entityManager->flush();

            $this->emailService->sendTemplatedEmail(
                $subject->getEmail(), 
                $subject->getUsername(),
                sprintf('%sassigned_to_editor_group.subject', $isAdmin ? 're' : ''),
                sprintf('bundles/EasyAdminBundle/crud/user/emails/%sassigned_to_editor_group.html.twig', $isAdmin ? 're' : ''),
                ['username' => $subject->getUsername()]
            );
            
            $this->addFlash('success', t(sprintf('%sassigned_to_editor_group', $isAdmin ? 're' : ''), [], 'flashes'));
        } elseif ($this->canBeAssignedToAdminRole($subject)) {
            $subject->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($subject);
            $entityManager->flush();

            $this->emailService->sendTemplatedEmail(
                $subject->getEmail(), 
                $subject->getUsername(),
                'promoted_to_admin.subject',
                'bundles/EasyAdminBundle/crud/user/emails/promoted_to_admin.html.twig',
                ['username' => $subject->getUsername()]
            );
            
            $this->addFlash('success', t('editor_promoted_to_admin', [], 'flashes'));
        }
        
        return $this->redirect($crudUrl);
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

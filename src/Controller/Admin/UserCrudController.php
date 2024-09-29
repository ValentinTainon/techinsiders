<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use App\Form\Admin\Field\PasswordField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use function Symfony\Component\Translation\t;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private MailerInterface $mailer, 
        private EmailVerifier $emailVerifier,
        private TranslatorInterface $translator,
        private RoleHierarchyInterface $roleHierarchy
    )
    {
    }
    
    public static function getEntityFqcn(): string
    {
        return User::class;
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
            ->linkToCrudAction('handleEditorMembership')
            ->addCssClass('btn btn-success')
            ->displayIf(fn (User $subject): bool => 
                $this->isGranted('ROLE_SUPER_ADMIN') 
                && !in_array('ROLE_EDITOR', $this->getAllUserRoles($subject->getRoles()), true)
            );

        $promoteEditorToAdminAction = Action::new('promoteEditorToAdminAction')
            ->setLabel(t('promote_to_admin.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-plus')
            ->linkToCrudAction('handleAdminMembership')
            ->addCssClass('btn btn-success')
            ->displayIf(fn (User $subject): bool => 
                $this->isGranted('ROLE_SUPER_ADMIN') 
                && in_array('ROLE_EDITOR', $this->getAllUserRoles($subject->getRoles()), true)
                && !in_array('ROLE_ADMIN', $this->getAllUserRoles($subject->getRoles()), true)
            );

        $reassignAdminToEditorGroupAction = Action::new('reassignAdminToEditorGroupAction')
            ->setLabel(t('reassign_to_editor_group.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-minus')
            ->linkToCrudAction('handleAdminMembership')
            ->addCssClass('btn btn-danger')
            ->displayIf(fn (User $subject): bool => 
                $this->isGranted('ROLE_SUPER_ADMIN') 
                && in_array('ROLE_ADMIN', $this->getAllUserRoles($subject->getRoles()), true)
                && !in_array('ROLE_SUPER_ADMIN', $this->getAllUserRoles($subject->getRoles()), true)
        );

        $deleteUserAction = Action::new('deleteUserAction')
            ->setLabel(t('disable_user.label', [], 'EasyAdminBundle'))
            ->setIcon('fa fa-user-minus')
            ->linkToCrudAction('deleteUser')
            ->addCssClass('btn btn-danger')
            ->displayIf(fn (User $subject): bool => 
                $this->isGranted('ROLE_SUPER_ADMIN') 
                && in_array('ROLE_EDITOR', $this->getAllUserRoles($subject->getRoles()), true)
                && !in_array('ROLE_SUPER_ADMIN', $this->getAllUserRoles($subject->getRoles()), true)
        );

        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and is_granted("ROLE_EDITOR"))'
        );

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $assignUserToEditorGroupAction)
            ->add(Crud::PAGE_EDIT, $promoteEditorToAdminAction)
            ->add(Crud::PAGE_EDIT, $reassignAdminToEditorGroupAction)
            ->add(Crud::PAGE_EDIT, $deleteUserAction)
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
        yield TextField::new('username', t('username.label', [], 'forms'));

        yield ChoiceField::new('roles', t('roles.label', [], 'forms'))
            ->setChoices([
                'Super Admin' => 'ROLE_SUPER_ADMIN',
                'Admin' => 'ROLE_ADMIN',
                'Editor' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices(true)
            ->hideWhenCreating()
            ->setDisabled()
            ->setPermission('ROLE_SUPER_ADMIN');

        yield EmailField::new('email', t('email.label', [], 'forms'));

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
            ->setBasePath('uploads/users/avatars')
            ->setUploadDir('public/uploads/users/avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setHelp(t('image.field.help.message', [], 'forms'));

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

    private function getAllUserRoles(array $userRoles): array
    {
        return $this->roleHierarchy->getReachableRoleNames($userRoles);
    }

    public function handleEditorMembership(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $user = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $crudUrl = $adminUrlGenerator->setController(self::class)->setAction('edit')->generateUrl();

        if (!$user) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));

            return $this->redirect($crudUrl);
        }

        $userRoles = $this->getAllUserRoles($user->getRoles());

        if (!in_array('ROLE_EDITOR', $userRoles, true)) {
            $user->setRoles(['ROLE_EDITOR']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($user->getEmail(), $user->getUsername()))
                    ->subject($this->translator->trans('assigned_to_editor_group.subject', [], 'emails'))
                    ->htmlTemplate('bundles/EasyAdminBundle/crud/user/emails/assigned_to_editor_group.html.twig')
                    ->context([
                        'username' => $user->getUsername()
                    ])
            );
            
            $this->addFlash('success', t('user_assigned_to_editor_group', [], 'flashes'));
        }
        
        return $this->redirect($crudUrl);
    }

    public function handleAdminMembership(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $user = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $crudUrl = $adminUrlGenerator->setController(self::class)->setAction('edit')->generateUrl();

        if (!$user) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));

            return $this->redirect($crudUrl);
        }

        $userRoles = $this->getAllUserRoles($user->getRoles());

        if (in_array('ROLE_SUPER_ADMIN', $userRoles, true)
        || !in_array('ROLE_EDITOR', $userRoles, true)) {
            return $this->redirect($crudUrl);
        } elseif (in_array('ROLE_ADMIN', $userRoles, true)) {
            $user->setRoles(['ROLE_EDITOR']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($user->getEmail(), $user->getUsername()))
                    ->subject($this->translator->trans('reassigned_to_editor_group.subject', [], 'emails'))
                    ->htmlTemplate('bundles/EasyAdminBundle/crud/user/emails/reassigned_to_editor_group.html.twig')
                    ->context([
                        'username' => $user->getUsername()
                    ])
            );
            
            $this->addFlash('success', t('admin_reassigned_to_editor_group', [], 'flashes'));
        } else {
            $user->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($user->getEmail(), $user->getUsername()))
                    ->subject($this->translator->trans('promoted_to_admin.subject', [], 'emails'))
                    ->htmlTemplate('bundles/EasyAdminBundle/crud/user/emails/promoted_to_admin.html.twig')
                    ->context([
                        'username' => $user->getUsername()
                    ])
            );
            
            $this->addFlash('success', t('editor_promoted_to_admin', [], 'flashes'));
        }
        
        return $this->redirect($crudUrl);
    }

    public function deleteUser(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $user = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $crudUrl = $adminUrlGenerator->setController(self::class)->setAction('edit')->generateUrl();

        if (!$user) {
            $this->addFlash('danger', t('user_not_found', [], 'flashes'));

            return $this->redirect($crudUrl);
        }

        $userRoles = $this->getAllUserRoles($user->getRoles());

        if (in_array('ROLE_EDITOR', $userRoles, true)) {
            $user
            ->setUsername('Auteur supprimé')
            ->setRoles([])
            ->setEmail(null)
            ->setPassword(null)
            ->setAvatar('anonymous-avatar.svg')
            ->setAbout(null)
            ->setVerified(false)
            ->setActive(false);
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($user->getEmail(), $user->getUsername()))
                    ->subject($this->translator->trans('update_on_your_status.subject', [], 'emails'))
                    ->htmlTemplate('bundles/EasyAdminBundle/crud/user/emails/removed_from_editor_group.html.twig')
                    ->context([
                        'username' => $user->getUsername()
                    ])
            );
            
            $this->addFlash('success', t('user_removed_from_editor_group', [], 'flashes'));
        }
        
        return $this->redirect($crudUrl);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $entityInstance,
                (new TemplatedEmail())
                    ->to(new Address($entityInstance->getEmail(), $entityInstance->getUsername()))
                    ->subject($this->translator->trans('confirm_email.subject', [], 'emails'))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'username' => $entityInstance->getUsername()
                    ])
            );
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $validateUser = Action::new('validateUser', 'Valider un rédacteur')
                ->linkToCrudAction('assignEditorRole')
                ->setCssClass('btn btn-success');
        }

        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (subject.getId() === user.getId() and is_granted("ROLE_EDITOR"))'
        );

        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $validateUser)
            ->setPermissions([
                Action::INDEX => 'ROLE_SUPER_ADMIN',
                Action::NEW => 'ROLE_SUPER_ADMIN',
                Action::EDIT => $expression,
                Action::DETAIL => $expression,
                Action::DELETE => $expression
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.user', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.user.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield BooleanField::new('isVerified', t('is_verified.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->setDisabled()
            ->hideWhenCreating()
            ->setPermission('ROLE_SUPER_ADMIN');
        
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

        yield TextareaField::new('about', t('about.label', [], 'forms'));

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

    public function assignEditorRole(AdminContext $context, RoleHierarchyInterface $roleHierarchy, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $crudUrl = $adminUrlGenerator->setController(self::class)->setAction('edit')->generateUrl();

        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');

            return $this->redirect($crudUrl);
        }

        $userRoles = $roleHierarchy->getReachableRoleNames($user->getRoles());

        if (!in_array('ROLE_EDITOR', $userRoles)) {

            $user->setRoles(['ROLE_EDITOR']);
            $entityManager->persist($user);
            $entityManager->flush();

            $mailer->send(
                (new TemplatedEmail())
                    ->to(new Address($user->getEmail(), $user->getUsername()))
                    ->subject(t('new_editor_request_validate.subject', [], 'emails'))
                    ->htmlTemplate('bundles/EasyAdminBundle/emails/new_editor_request_validate.html.twig')
                    ->context([
                        'username' => $user->getUsername()
                    ])
            );
            
            $this->addFlash('success', 'L\'utilisateur a été validé et l\'email a été envoyé.');
        } else {
            $this->addFlash('info', 'Cet utilisateur est déjà un éditeur.');
        }
        
        return $this->redirect($crudUrl);
    }
}

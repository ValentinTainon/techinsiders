<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Mime\Address;
use App\Form\Admin\Field\CkeditorField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use function Symfony\Component\Translation\t;
use Symfony\Component\Mailer\MailerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    public function __construct(private MailerInterface $mailer, private TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    private function postInstance(): Post|null
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if ($entityInstance instanceof Post) {
            return $entityInstance;
        }

        return null;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('post.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('post.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle('new', t('create.post', [], 'EasyAdminBundle'))
            ->setPageTitle('edit', t('edit.post', [], 'EasyAdminBundle'))
            ->addFormTheme('bundles/EasyAdminBundle/crud/field/ckeditor.html.twig')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and is_granted("ROLE_EDITOR"))'
        );
        
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->setPermissions([
                Action::EDIT => $expression,
                Action::DETAIL => $expression,
                Action::DELETE => $expression
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.post', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.post.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', t('author.label', [], 'forms'))
            ->onlyOnIndex();

        yield AssociationField::new('category', t('category.label.singular', [], 'EasyAdminBundle'));
        
        yield DateTimeField::new('createdAt', t('created_at.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);

        if (!is_null($this->postInstance()) && !empty($this->postInstance()->getUpdatedAt())) {
            yield DateTimeField::new('updatedAt', t('updated_at.label', [], 'forms'))
                ->hideWhenCreating()
                ->setDisabled()
                ->setRequired(false);
        }

        yield TextField::new('title', t('title.label', [], 'forms'));
        
        yield SlugField::new('slug', t('slug.label', [], 'forms'))
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setFormTypeOption('row_attr', ['style' => 'display: none;']);

        $postThumbnailField = ImageField::new('thumbnail', t('thumbnail.label', [], 'forms'))
            ->setBasePath('uploads/posts/thumbnails')
            ->setUploadDir('public/uploads/posts/thumbnails')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setHelp(t('image.field.help.message', [], 'forms'));

        if ($pageName === Crud::PAGE_EDIT && !is_null($this->postInstance()) && !empty($this->postInstance()->getThumbnail())) {
            $postThumbnailField->setRequired(false);
        }

        yield $postThumbnailField;

        yield CkeditorField::new('content', t('content.label', [], 'forms'));

        yield BooleanField::new('isVisible', t('is_visible.label', [], 'forms'))
            ->renderAsSwitch(false)
            ->setDisabled()
            ->onlyOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add(EntityFilter::new('user'));
        }

        $filters->add(EntityFilter::new('category'))
                ->add(DateTimeFilter::new('createdAt'))
                ->add(BooleanFilter::new('isVisible'));
        
        return $filters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $queryBuilder->where('entity.user = :user')
                        ->setParameter('user', $this->getUser());
        }

        return $queryBuilder;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Post) {
            /** @var User $user */
            $user = $this->getUser();

            $entityInstance
                ->setUser($user)
                ->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            
            if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
                $this->mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address($user->getEmail(), $user->getUsername()))
                        ->to(new Address($this->getParameter('app_contact_email'), $this->getParameter('app_name')))
                        ->subject($this->translator->trans('new_post_validation_request.subject', [], 'emails'))
                        ->htmlTemplate('bundles/EasyAdminBundle/crud/post/emails/new_post_validation_request.html.twig')
                        ->context([
                            'username' => $user->getUsername(),
                            'post_title' => $entityInstance->getTitle()
                        ])
                );
            }
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Post) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}

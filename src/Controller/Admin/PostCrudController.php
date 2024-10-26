<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\QueryBuilder;
use App\Form\Admin\Field\CkeditorField;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
    private const string maxThumbnailSize = '400k';

    public function __construct(private EmailService $emailService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    private function postInstance(): ?Post
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if ($entityInstance instanceof Post) {
            return $entityInstance;
        }

        return null;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addAssetMapperEntry('ckeditor-init');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('post.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('post.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle('new', t('create.post', [], 'EasyAdminBundle'))
            ->setPageTitle('edit', t('edit.post', [], 'EasyAdminBundle'))
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $expression = new Expression(
            'is_granted("ROLE_SUPER_ADMIN") or (subject.getUser() === user and is_granted("ROLE_EDITOR"))'
        );
        
        return $actions
            ->disable(Crud::PAGE_DETAIL)
            ->setPermissions([
                Action::EDIT => $expression,
                Action::DELETE => $expression,
                Action::BATCH_DELETE => $expression
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel(t('create.post', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel(t('create_and_add.post.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission('ROLE_SUPER_ADMIN');

        yield AssociationField::new('user', t('author.label', [], 'forms'))
            ->onlyOnIndex();

        yield AssociationField::new('category', t('category.label.singular', [], 'EasyAdminBundle'))
            ->setColumns('col-sm-6 col-lg-5 col-xxl-3');
        FormField::addRow();
        
        yield DateTimeField::new('createdAt', t('created_at.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false);

        if (!is_null($this->postInstance()) && !is_null($this->postInstance()->getUpdatedAt())) {
            yield DateTimeField::new('updatedAt', t('updated_at.label', [], 'forms'))
                ->hideWhenCreating()
                ->setDisabled()
                ->setRequired(false);
        }

        yield ImageField::new('thumbnail', t('thumbnail.label', [], 'forms'))
            ->setBasePath('uploads/posts/thumbnails')
            ->setUploadDir($this->getParameter('uploads_images_relative_path').'/posts/thumbnails')
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true, 
                    maxSize: self::maxThumbnailSize, 
                    mimeTypes: [
                        'image/jpeg', 
                        'image/png', 
                        'image/webp',
                    ]
                )
            )
            ->setHelp(t('thumbnail.field.help.message', ['%size%' => self::maxThumbnailSize], 'forms'))
            ->setRequired($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT && empty($this->postInstance()->getThumbnail()) ? true : false);

        yield TextField::new('title', t('title.label', [], 'forms'))
            ->setColumns(10)
            ->setFormTypeOption('row_attr', ['style' => 'max-width: 1000px;']);
        
        $slugField = SlugField::new('slug', t('slug.label', [], 'forms'))
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setColumns(10)
            ->setFormTypeOption('row_attr', ['style' => 'max-width: 1000px;']);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $slugField->setFormTypeOption('row_attr', ['style' => 'display: none;']);
        }

        yield $slugField;

        yield CkeditorField::new('content', t('content.label', [], 'forms'));

        yield BooleanField::new('isVisible', t('is_visible.label', [], 'forms'))
            ->renderAsSwitch($pageName === Crud::PAGE_INDEX ? false : true)
            ->hideWhenCreating()
            ->setPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add(EntityFilter::new('user', t('author.label', [], 'forms')));
        }

        $filters->add(EntityFilter::new('category', t('category.label.singular', [], 'EasyAdminBundle')))
                ->add(DateTimeFilter::new('createdAt', t('created_at.label', [], 'forms')))
                ->add(BooleanFilter::new('isVisible', t('is_visible.label', [], 'forms')));

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

            $entityInstance->setUser($user);
            
            if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
                $this->emailService->sendTemplatedEmail(
                    $this->getParameter('app_contact_email'),
                    $this->getParameter('app_name'),
                    'new_post_validation_request.subject',
                    'bundles/EasyAdminBundle/crud/post/emails/new_post_validation_request.html.twig',
                    [
                        'username' => $user->getUsername(),
                        'post_title' => $entityInstance->getTitle()
                    ],
                    false,
                    $user->getEmail(), 
                    $user->getUsername()
                );
            }
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }
}

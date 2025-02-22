<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Enum\UserRole;
use App\Enum\PostStatus;
use Doctrine\ORM\QueryBuilder;
use App\Config\PostContentConfig;
use App\Form\Field\CKEditor5Type;
use App\Security\Voter\PostVoter;
use App\Config\PostThumbnailConfig;
use App\Form\Field\Admin\CKEditor5CollectionField;
use App\Form\Field\Admin\CKEditor5Field;
use App\Form\Field\CKEditor5CollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Form\Extension\Core\Type\UuidType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('post.label.singular', [], 'EasyAdminBundle'))
            ->setEntityLabelInPlural(t('post.label.plural', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_NEW, t('create.post', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_EDIT, t('edit.post', [], 'EasyAdminBundle'))
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DETAIL)
            ->setPermissions([
                Action::EDIT => PostVoter::EDIT,
                Action::DELETE => PostVoter::DELETE,
                Action::BATCH_DELETE => PostVoter::BATCH_DELETE
            ])
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action->setLabel(t('create.post', [], 'EasyAdminBundle'))
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action->setLabel(t('create_and_add.post.label', [], 'EasyAdminBundle'))
            );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab(t('general_information.label', [], 'forms'))
            ->addCssClass('custom-max-width');
        yield IdField::new('id', t('id.label', [], 'forms'))
            ->hideOnForm()
            ->setPermission(UserRole::SUPER_ADMIN->value);

        yield DateTimeField::new('createdAt', t('created_at.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-5');

        $updatedAtField = DateTimeField::new('updatedAt', t('updated_at.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-5');

        if ($this->isUpdatedAtNull()) {
            $updatedAtField->hideWhenUpdating();
        }

        yield $updatedAtField;

        yield FormField::addRow();
        yield AssociationField::new('user', t('author.label', [], 'forms'))
            ->hideWhenCreating()
            ->setDisabled()
            ->setRequired(false)
            ->setColumns('col-sm-6 col-md-5');

        yield AssociationField::new('category', t('category.label.singular', [], 'EasyAdminBundle'))
            ->setHtmlAttribute('required', true)
            ->setColumns('col-sm-6 col-md-5');

        yield AssociationField::new('tags', t('tag.label.plural', [], 'EasyAdminBundle'))
            ->setColumns('col-sm-6 col-md-5')
            ->setTextAlign('center');

        if (Crud::PAGE_INDEX === $pageName) {
            yield ImageField::new('thumbnail', t('thumbnail.label', [], 'forms'))
                ->formatValue(function ($value, $entity) {
                    if (str_starts_with($entity->getThumbnail(), 'https://')) {
                        return $entity->getThumbnail();
                    } else {
                        return PostThumbnailConfig::getConfig($entity->getStringifyUuid())->basePath() . $entity->getThumbnail();
                    }
                });
        }

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName && !str_starts_with($this->getEntityThumbnail(), 'https://')) {
            $this->createThumbnailUploadDirIfNotExist($this->getEntityUuid());
            $postThumbnailConfig = PostThumbnailConfig::getConfig($this->getEntityUuid());

            yield ImageField::new('thumbnail', t('thumbnail.label', [], 'forms'))
                ->setBasePath($postThumbnailConfig->basePath())
                ->setUploadDir($postThumbnailConfig->uploadDir())
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOption('allow_delete', false)
                ->setFileConstraints(
                    new Image(
                        detectCorrupted: true,
                        maxSize: $postThumbnailConfig->maxFileSize(),
                        mimeTypes: $postThumbnailConfig->allowedMimeTypes()
                    )
                )->setHelp(
                    t(
                        'image.field.help.message',
                        [
                            '%formats%' => $postThumbnailConfig->allowedMimeTypesExtensions(),
                            '%size%' => $postThumbnailConfig->maxFileSize()
                        ],
                        'forms'
                    )
                )
                ->setRequired(Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName && empty($this->getEntityThumbnail()))
                ->setColumns(10);
        }

        yield FormField::addTab(t('post_content.label', [], 'forms'))
            ->addCssClass('custom-max-width');
        yield TextField::new('title', t('title.label', [], 'forms'))
            ->addCssClass('title-field')
            ->setColumns(12);

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield CKEditor5Field::new('content', t('content.label', [], 'forms'))
                ->setFormTypeOptions([
                    CKEditor5Type::USE_FEATURE_RICH_EDITOR_OPTION => true,
                    CKEditor5Type::MIN_CHARACTERS_OPTION          => PostContentConfig::MIN_CHARACTERS,
                    CKEditor5Type::UPLOAD_DIR_OPTION              => "images/uploads/post/{$this->getEntityUuid()}/content"
                ])
                ->setHelp(
                    t('post_content.help.message', ['%min_characters%' => PostContentConfig::MIN_CHARACTERS], 'forms')
                );
        }

        yield IntegerField::new('numberOfViews', t('number_of_views.label', [], 'forms'))
            ->onlyOnIndex()
            ->setTextAlign('center');

        yield FormField::addTab(t('comments.label', [], 'forms'))
            ->addCssClass('custom-max-width')
            ->onlyWhenUpdating();
        yield CKEditor5CollectionField::new('comments', false);
        yield AssociationField::new('comments', t('comments.label', [], 'forms'))
            ->onlyOnIndex()
            ->setTextAlign('center');

        yield FormField::addTab(t('status.label', [], 'forms'));
        yield ChoiceField::new('status', t('status.label', [], 'forms'))
            ->setChoices([
                PostStatus::DRAFTED->label($this->translator) => PostStatus::DRAFTED,
                PostStatus::READY_FOR_REVIEW->label($this->translator) => PostStatus::READY_FOR_REVIEW,
                PostStatus::PUBLISHED->label($this->translator) => PostStatus::PUBLISHED,
                PostStatus::REJECTED->label($this->translator) => PostStatus::REJECTED
            ])
            ->renderAsBadges([
                PostStatus::DRAFTED->value => 'primary',
                PostStatus::READY_FOR_REVIEW->value => 'warning',
                PostStatus::PUBLISHED->value => 'success',
                PostStatus::REJECTED->value => 'danger',
            ])
            ->setFormTypeOption(
                'choice_filter',
                fn(PostStatus $choice): bool =>
                $this->isGranted(UserRole::SUPER_ADMIN->value) || $choice === PostStatus::DRAFTED || $choice === PostStatus::READY_FOR_REVIEW
            )
            ->setColumns('col-sm-6 col-md-5');

        yield IdField::new('uuid')
            ->setFormType(UuidType::class)
            ->setFormTypeOption('row_attr', ['hidden' => true])
            ->onlyWhenCreating();
    }

    private function isUpdatedAtNull(): bool
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof Post) {
            return false;
        }

        return $entityInstance->getUpdatedAt() === null;
    }

    private function getEntityThumbnail(): ?string
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof Post) {
            return false;
        }

        return $entityInstance->getThumbnail();
    }

    private function getEntityUuid(): string
    {
        try {
            $entityInstance = $this->getContext()->getEntity()->getInstance();

            if (!$entityInstance instanceof Post) {
                throw new \RuntimeException('Entity instance is not an instance of Post');
            }

            return $entityInstance->getStringifyUuid();
        } catch (\Throwable $th) {
            throw new \RuntimeException('Failed to get entity UUID', 0, $th);
        }
    }

    private function createThumbnailUploadDirIfNotExist(string $entityUuid): void
    {
        try {
            if (empty($entityUuid)) {
                throw new \RuntimeException('Entity UUID is empty');
            }

            $thumbnailUploadDir = "{$this->getParameter('kernel.project_dir')}/public/images/uploads/post/{$entityUuid}/thumbnail";

            $filesystem = new Filesystem();

            if (!$filesystem->exists($thumbnailUploadDir)) {
                $filesystem->mkdir($thumbnailUploadDir, 0775);
            }
        } catch (\Throwable $th) {
            throw new \RuntimeException('Failed to create thumbnail upload directory', 0, $th);
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UserRole::SUPER_ADMIN->value)) {
            $filters->add(EntityFilter::new('user', t('author.label', [], 'forms')));
        }

        $filters->add(EntityFilter::new('category', t('category.label.singular', [], 'EasyAdminBundle')))
            ->add(DateTimeFilter::new('createdAt', t('created_at.label', [], 'forms')))
            ->add(DateTimeFilter::new('updatedAt', t('updated_at.label', [], 'forms')))
            ->add(
                ChoiceFilter::new('status', t('status.label', [], 'forms'))
                    ->setTranslatableChoices([
                        PostStatus::DRAFTED->value => PostStatus::DRAFTED->label($this->translator),
                        PostStatus::READY_FOR_REVIEW->value => PostStatus::READY_FOR_REVIEW->label($this->translator),
                        PostStatus::PUBLISHED->value => PostStatus::PUBLISHED->label($this->translator),
                        PostStatus::REJECTED->value => PostStatus::REJECTED->label($this->translator),
                    ])
            );

        return $filters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted(UserRole::SUPER_ADMIN->value)) {
            $qb->where('entity.user = :user')
                ->setParameter('user', $this->getUser());
        }

        return $qb;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Post) return;

        $uuid = $entityInstance->getStringifyUuid();

        parent::deleteEntity($entityManager, $entityInstance);

        $this->deletePostMedia($uuid);
    }

    private function deletePostMedia(?string $uuid): void
    {
        $filesystem = new Filesystem();
        $postMediaDir = "{$this->getParameter('kernel.project_dir')}/public/images/uploads/post/{$uuid}";

        if (null !== $uuid && $filesystem->exists($postMediaDir)) {
            $filesystem->remove($postMediaDir);
        }
    }
}

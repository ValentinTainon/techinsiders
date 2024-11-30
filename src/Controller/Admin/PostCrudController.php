<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Enum\UserRole;
use App\Enum\MimeType;
use App\Enum\PostStatus;
use App\Service\PathService;
use Doctrine\ORM\QueryBuilder;
use App\Form\PostCommentsFormType;
use App\Repository\PostRepository;
use App\Form\Admin\Field\CkeditorField;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    private const string THUMBNAIL_MAX_FILE_SIZE = '400k';
    private const string EDITOR_CONFIG_TYPE = 'feature-rich';

    public function __construct(
        private PostRepository $postRepository,
        private TranslatorInterface $translator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Post::class;
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
            ->setPageTitle(Crud::PAGE_NEW, t('create.post', [], 'EasyAdminBundle'))
            ->setPageTitle(Crud::PAGE_EDIT, t('edit.post', [], 'EasyAdminBundle'))
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $expression = new Expression(
            sprintf(
                'is_granted("%s") or (user === subject and is_granted("%s"))',
                UserRole::SUPER_ADMIN->value,
                UserRole::EDITOR->value
            )
        );

        return $actions
            ->disable(Action::DETAIL)
            ->setPermissions([
                Action::EDIT => $expression,
                Action::DELETE => $expression,
                Action::BATCH_DELETE => $expression
            ])
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel(t('create.post', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $action) => $action->setLabel(t('create_and_add.post.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $action) => $action->setLabel(t('save_and_continue.editing.label', [], 'EasyAdminBundle')))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $action) => $action->setLabel(t('save.label', [], 'EasyAdminBundle')));
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
            ->setColumns('col-sm-6 col-md-5');

        yield ImageField::new('thumbnail', t('thumbnail.label', [], 'forms'))
            ->setBasePath(PathService::POSTS_THUMBNAIL_BASE_PATH)
            ->setUploadDir(PathService::POSTS_THUMBNAIL_UPLOAD_DIR)
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setFormTypeOption('allow_delete', false)
            ->setFileConstraints(
                new Image(
                    detectCorrupted: true,
                    maxSize: self::THUMBNAIL_MAX_FILE_SIZE,
                    mimeTypes: [
                        MimeType::JPEG->value,
                        MimeType::PNG->value,
                        MimeType::WEBP->value,
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
                                MimeType::WEBP->extensions()
                            )
                        ),
                        '%size%' => self::THUMBNAIL_MAX_FILE_SIZE
                    ],
                    'forms'
                )
            )
            ->setRequired($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT && $this->isThumbnailEmpty())
            ->setColumns(10);

        yield TextField::new('title', t('title.label', [], 'forms'))
            ->setColumns(10);

        yield FormField::addTab(t('post_content.label', [], 'forms'))
            ->setHelp(t('post_content.tab.help.message', ['%min_post_length_limit%' => Post::MIN_POST_LENGTH_LIMIT], 'forms'))
            ->addCssClass('custom-max-width');
        yield CkeditorField::new('content', false)
            ->setFormTypeOption('row_attr', [
                'data-editor-config-type' => self::EDITOR_CONFIG_TYPE,
                'data-min-post-length-limit' => Post::MIN_POST_LENGTH_LIMIT,
                'data-current-post-id' => $this->setDataCurrentPostId($pageName),
            ]);

        yield IntegerField::new('commentsCount', t('comments.label', [], 'forms'))
            ->onlyOnIndex()
            ->setTextAlign('center');

        yield FormField::addTab(t('comments.label', [], 'forms'))
            ->addCssClass('custom-max-width')
            ->onlyWhenUpdating();
        yield CollectionField::new('comments', false)
            ->setEntryType(PostCommentsFormType::class)
            ->onlyWhenUpdating()
            ->setColumns(10)
            ->addJsFiles(
                Asset::new('../assets/typescript/easyadmin/PostCommentsCollectionCustomiser.ts')
            );

        yield FormField::addTab(t('status.label', [], 'forms'));
        yield ChoiceField::new('status')
            ->setChoices($this->statusChoices())
            ->renderAsBadges([
                PostStatus::DRAFTED->value => 'primary',
                PostStatus::READY_FOR_REVIEW->value => 'warning',
                PostStatus::PUBLISHED->value => 'success',
                PostStatus::REJECTED->value => 'danger',
            ])
            ->setColumns('col-sm-6 col-md-5');
    }

    private function isUpdatedAtNull(): bool
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof Post) {
            return false;
        }

        return $entityInstance->getUpdatedAt() === null;
    }

    private function isThumbnailEmpty(): bool
    {
        $entityInstance = $this->getContext()->getEntity()->getInstance();

        if (!$entityInstance instanceof Post) {
            return false;
        }

        return empty($entityInstance->getThumbnail());
    }

    public function setDataCurrentPostId(string $pageName): int
    {
        if ($pageName === Crud::PAGE_NEW) {
            $lastPost = $this->postRepository->findOneBy([], ['id' => 'DESC']);

            return $lastPost === null ? 1 : $lastPost->getId() + 1;
        }

        if ($pageName === Crud::PAGE_EDIT) {
            $entityInstance = $this->getContext()->getEntity()->getInstance();

            if (!$entityInstance instanceof Post) {
                return false;
            }

            return $entityInstance->getId();
        }

        return 0;
    }

    private function statusChoices(): array
    {
        $statusChoices = [];

        if ($this->isGranted(UserRole::SUPER_ADMIN->value)) {
            array_push(
                $statusChoices,
                [
                    PostStatus::DRAFTED->label($this->translator) => PostStatus::DRAFTED,
                    PostStatus::READY_FOR_REVIEW->label($this->translator) => PostStatus::READY_FOR_REVIEW,
                    PostStatus::PUBLISHED->label($this->translator) => PostStatus::PUBLISHED,
                    PostStatus::REJECTED->label($this->translator) => PostStatus::REJECTED
                ]
            );
        } else {
            array_push(
                $statusChoices,
                [
                    PostStatus::DRAFTED->label($this->translator) => PostStatus::DRAFTED,
                    PostStatus::READY_FOR_REVIEW->label($this->translator) => PostStatus::READY_FOR_REVIEW
                ]
            );
        }

        return $statusChoices;
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
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted(UserRole::SUPER_ADMIN->value)) {
            $queryBuilder->where('entity.user = :user')
                ->setParameter('user', $this->getUser());
        }

        return $queryBuilder;
    }
}

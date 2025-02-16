<?php

namespace App\Form\Admin\Field;

use App\Enum\EditorType;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use App\Exception\InvalidCkeditor5FieldArgumentException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CKEditor5Field implements FieldInterface
{
    use FieldTrait;

    private const string DATA_CONTROLLER = 'data-controller';
    private const string DATA_EDITOR_TYPE = 'data-editor-type';
    private const string DATA_PAGE_NAME = 'data-page-name';
    private const string DATA_MIN_LENGTH_LIMIT = 'data-min-length-limit';
    private const string DATA_UPLOAD_DIR = 'data-upload-dir';

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextareaType::class)
            ->addCssClass('field-ckeditor5')
            ->setHtmlAttributes([
                self::DATA_CONTROLLER => 'ckeditor5',
                self::DATA_EDITOR_TYPE => EditorType::STARTER->value
            ])
            ->setDefaultColumns(12)
            ->onlyOnForms()
            ->addHtmlContentsToBody()
        ;
    }

    public function useFeatureRichEditor(
        bool $useFeatureRichEditor = true,
        ?string $pageName = null,
        ?int $minLengthLimit = null,
        ?string $uploadDir = null
    ): self {
        if (false === $useFeatureRichEditor) {
            return $this->setHtmlAttribute(self::DATA_EDITOR_TYPE, EditorType::STARTER->value);
        }

        if (null === $pageName) {
            throw InvalidCkeditor5FieldArgumentException::missingParameter('pageName', self::class);
        }

        if (null === $minLengthLimit) {
            throw InvalidCkeditor5FieldArgumentException::missingParameter('minLengthLimit', self::class);
        }

        if (null === $uploadDir) {
            throw InvalidCkeditor5FieldArgumentException::missingParameter('uploadDir', self::class);
        }

        return $this->setHtmlAttributes([
            self::DATA_EDITOR_TYPE => EditorType::FEATURE_RICH->value,
            self::DATA_PAGE_NAME => $pageName,
            self::DATA_MIN_LENGTH_LIMIT => $minLengthLimit,
            self::DATA_UPLOAD_DIR => $uploadDir
        ]);
    }
}

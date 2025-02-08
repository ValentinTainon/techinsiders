<?php

namespace App\Form\Admin\Field;

use App\Enum\EditorType;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class Ckeditor5Field implements FieldInterface
{
    use FieldTrait {
        FieldTrait::getAsDto as private traitGetAsDto;
    }

    public const string OPTION_EDITOR_TYPE = 'editorType';
    public const string OPTION_PAGE_NAME = 'pageName';
    public const string OPTION_MIN_POST_LENGTH_LIMIT = 'minPostLengthLimit';

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextareaType::class)
            ->setFormTypeOption('block_name', 'custom_content')
            ->addCssClass('field-ckeditor')
            ->setDefaultColumns(12)
            ->onlyOnForms()
        ;
    }

    public function setEditorType(string $editorType): self
    {
        return $this->setCustomOption(self::OPTION_EDITOR_TYPE, $editorType);
    }

    public function setPageName(string $pageName): self
    {
        return $this->setCustomOption(self::OPTION_PAGE_NAME, $pageName);
    }

    public function setMinPostLengthLimit(int $minPostLengthLimit): self
    {
        return $this->setCustomOption(self::OPTION_MIN_POST_LENGTH_LIMIT, $minPostLengthLimit);
    }

    public function getAsDto(): FieldDto
    {
        $editorType = $this->dto->getCustomOption(self::OPTION_EDITOR_TYPE);

        if (null === $editorType) {
            throw new \RuntimeException('The custom option "' . self::OPTION_EDITOR_TYPE . '" is mandatory for "' . self::class . '".');
        }

        if (EditorType::FEATURE_RICH->value === $editorType) {
            if (null === $this->dto->getCustomOption(self::OPTION_PAGE_NAME)) {
                throw new \RuntimeException('The custom option "' . self::OPTION_PAGE_NAME . '" is mandatory when the custom option "' . self::OPTION_EDITOR_TYPE . '" is set to "' . EditorType::FEATURE_RICH->value . '".');
            }
            if (null === $this->dto->getCustomOption(self::OPTION_MIN_POST_LENGTH_LIMIT)) {
                throw new \RuntimeException('The custom option "' . self::OPTION_MIN_POST_LENGTH_LIMIT . '" is mandatory when the custom option "' . self::OPTION_EDITOR_TYPE . '" is set to "' . EditorType::FEATURE_RICH->value . '".');
            }
        }

        return $this->traitGetAsDto();
    }
}

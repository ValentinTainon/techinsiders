<?php

namespace App\Form\Field;

use App\Enum\EditorType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Exception\InvalidCkeditor5FieldArgumentException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class CKEditor5Type extends AbstractType
{
    private const string EDITOR_NAME = 'ckeditor5';

    // Options
    public const string USE_FEATURE_RICH_EDITOR_OPTION = 'use_feature_rich_editor';
    public const string MIN_CHARACTERS_OPTION = 'min_characters';
    public const string UPLOAD_DIR_OPTION = 'upload_dir';
    public const string READ_ONLY_OPTION = 'read_only';

    // Data attributes
    private const string DATA_CONTROLLER = 'data-controller';
    public const string DATA_EDITOR_TYPE = 'data-editor-type';
    private const string DATA_MIN_CHARACTERS = 'data-min-characters';
    private const string DATA_UPLOAD_DIR = 'data-upload-dir';
    private const string DATA_READ_ONLY = 'data-read-only';

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $rowAttr = $view->vars['row_attr'];
        $rowAttr['class'] = ($rowAttr['class'] ?? '') . ' field-ckeditor5';

        $attr = $view->vars['attr'];
        $attr[self::DATA_CONTROLLER]  = self::EDITOR_NAME;
        $attr[self::DATA_EDITOR_TYPE] = EditorType::STARTER->value;
        $attr[self::DATA_READ_ONLY]   = true === $options[self::READ_ONLY_OPTION] ? 'true' : 'false';

        if (true === $options[self::USE_FEATURE_RICH_EDITOR_OPTION]) {
            if (null === $options[self::MIN_CHARACTERS_OPTION]) {
                throw InvalidCkeditor5FieldArgumentException::missingOption(self::MIN_CHARACTERS_OPTION, self::class);
            }
            if (null === $options[self::UPLOAD_DIR_OPTION]) {
                throw InvalidCkeditor5FieldArgumentException::missingOption(self::UPLOAD_DIR_OPTION, self::class);
            }

            $attr[self::DATA_EDITOR_TYPE]    = EditorType::FEATURE_RICH->value;
            $attr[self::DATA_MIN_CHARACTERS] = $options[self::MIN_CHARACTERS_OPTION];
            $attr[self::DATA_UPLOAD_DIR]     = $options[self::UPLOAD_DIR_OPTION];
        } else {
            if (null !== $options[self::MIN_CHARACTERS_OPTION]) {
                throw InvalidCkeditor5FieldArgumentException::uselessOption(self::MIN_CHARACTERS_OPTION, self::class);
            }
            if (null !== $options[self::UPLOAD_DIR_OPTION]) {
                throw InvalidCkeditor5FieldArgumentException::uselessOption(self::UPLOAD_DIR_OPTION, self::class);
            }
        }

        $view->vars['row_attr']['class'] = "{$rowAttr['class']} {$attr[self::DATA_EDITOR_TYPE]}-editor";
        $view->vars['attr']              = $attr;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::USE_FEATURE_RICH_EDITOR_OPTION => false,
            self::MIN_CHARACTERS_OPTION          => null,
            self::UPLOAD_DIR_OPTION              => null,
            self::READ_ONLY_OPTION               => false,
        ]);

        $resolver->setAllowedTypes(self::USE_FEATURE_RICH_EDITOR_OPTION, ['bool']);
        $resolver->setAllowedTypes(self::MIN_CHARACTERS_OPTION, ['int', 'null']);
        $resolver->setAllowedTypes(self::UPLOAD_DIR_OPTION, ['string', 'null']);
        $resolver->setAllowedTypes(self::READ_ONLY_OPTION, ['bool']);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return self::EDITOR_NAME;
    }
}

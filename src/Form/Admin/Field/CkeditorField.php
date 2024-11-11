<?php

namespace App\Form\Admin\Field;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CkeditorField implements FieldInterface
{
    use FieldTrait;

    public const string EDITOR_CONFIG_TYPE = 'feature-rich';

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
            ->addFormTheme('bundles/EasyAdminBundle/crud/field/ckeditor.html.twig')
            ->addCssClass('field-ckeditor')
            ->addCssFiles(
                Asset::new('../assets/styles/ckeditor/default.css'),
                Asset::new('../assets/styles/ckeditor/dark-mode.css'),
                Asset::new('../assets/styles/ckeditor/word-count.css')
            )
            ->setFormTypeOption('row_attr', [
                'data-editor-config-type' => self::EDITOR_CONFIG_TYPE,
                'data-min-post-length-limit' => Post::MIN_POST_LENGTH_LIMIT,
            ])
            ->setDefaultColumns(12)
            ->onlyOnForms()
        ;
    }
}

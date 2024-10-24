<?php

namespace App\Form\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CkeditorField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextareaType::class)
            ->setFormTypeOptions(['block_name' => 'custom_content'])
            ->addCssClass('field-ckeditor')
            ->addCssFiles(
                Asset::new('../assets/styles/ckeditor/default.css'),
                Asset::new('../assets/styles/ckeditor/dark-mode.css'),
                Asset::new('../assets/styles/ckeditor/word-count.css')
            )
            ->setDefaultColumns(10)
            ->onlyOnForms()
        ;
    }
}

<?php

namespace App\Form\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class Ckeditor5Field implements FieldInterface
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
            ->setFormTypeOption('block_name', 'custom_content')
            ->addCssClass('field-ckeditor')
            ->setDefaultColumns(12)
            ->onlyOnForms()
        ;
    }
}

<?php

namespace App\Form\Field\Admin;

use App\Form\Field\CKEditor5Type;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CKEditor5Field implements FieldInterface
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
            ->setFormType(CKEditor5Type::class)
            ->addFormTheme('ckeditor5/ckeditor5_theme.html.twig')
            ->setDefaultColumns(12)
            ->onlyOnForms()
        ;
    }
}

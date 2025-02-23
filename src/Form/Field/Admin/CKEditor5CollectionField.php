<?php

namespace App\Form\Field\Admin;

use App\Form\Field\Admin\CKEditor5CollectionEntryType;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CKEditor5CollectionField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): CollectionField
    {
        return CollectionField::new($propertyName, $label)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->addCssClass('field-ckeditor5-collection')
            ->setFormTypeOption('attr', ['data-controller' => 'ckeditor5-collection'])
            ->setEntryType(CKEditor5CollectionEntryType::class)
            ->onlyWhenUpdating()
            ->setDefaultColumns(10)
        ;
    }
}

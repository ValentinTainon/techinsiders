<?php

namespace App\Form\Admin\Field;

use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class PasswordField implements FieldInterface
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
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'options' => [
                    'row_attr' => [
                        'class' => 'field-password'
                    ],
                    'attr' => [
                        'autocomplete' => 'new-password'
                    ],
                ],
                'first_options' => [
                    'label' => t('password.label', [], 'forms')
                ],
                'second_options' => [
                    'label' => t('repeat.password.label', [], 'forms')
                ]
            ])
            ->addJsFiles(Asset::new('../assets/typescript/easyadmin/PasswordFieldCustomiser.ts'))
            ->onlyOnForms();
    }
}

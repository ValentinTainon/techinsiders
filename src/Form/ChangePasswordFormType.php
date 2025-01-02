<?php

namespace App\Form;

use function Symfony\Component\Translation\t;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'row_attr' => [
                        'class' => 'form-group field-password'
                    ],
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control'
                    ],
                    'toggle' => true,
                    'visible_label' => null,
                    'hidden_label' => null,
                ],
                'first_options' => [
                    'label' => t('new.password.label', [], 'forms')
                ],
                'second_options' => [
                    'label' => t('repeat.new.password.label', [], 'forms')
                ],
                'invalid_message' => t('password.constraint.repeat.invalid_message')
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('reset.password.label', [], 'forms'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['reset_password']
        ]);
    }
}

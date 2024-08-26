<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => t('email.label', [], 'forms'),
                'attr' => ['autocomplete' => 'email'],
                'help' => t('reset.password.request.help.message', [], 'forms')
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('next.label', [], 'forms')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

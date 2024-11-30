<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NoSuspiciousCharacters;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => t('username.label', [], 'forms')
            ])
            ->add('email', EmailType::class, [
                'label' => t('email.label', [], 'forms')
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password'
                    ],
                ],
                'first_options' => [
                    'label' => t('password.label', [], 'forms')
                ],
                'second_options' => [
                    'label' => t('repeat.password.label', [], 'forms')
                ],
                'invalid_message' => t('password.constraint.repeat.invalid_message')
            ])
            ->add('motivations', TextareaType::class, [
                'label' => t('motivations.label', [], 'forms'),
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => t('field.constraint.not_blank.message')
                    ]),
                    new Length([
                        'min' => 100,
                        'minMessage' => t('field.constraint.length.min_message'),
                        'max' => 1000,
                        'maxMessage' => t('field.constraint.length.max_message')
                    ]),
                    new NoSuspiciousCharacters()
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => t('agree_terms.label', [], 'forms'),
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => t('agree_terms.constraint.is_true.message')
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('sign_up.label', [], 'forms'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}

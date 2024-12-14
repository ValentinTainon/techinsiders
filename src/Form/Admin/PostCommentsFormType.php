<?php

namespace App\Form\Admin;

use App\Enum\UserRole;
use App\Entity\Comment;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostCommentsFormType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class, [
            'label' => false,
            'attr' => [
                'required' => true,
            ]
        ])->addEventListener(
            FormEvents::PRE_SET_DATA,
            [$this, 'onPreSetData']
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $comment = $event->getData();

        if ($comment === null) {
            return;
        }

        if ($form->has('content')) {
            $form->remove('content');
        }

        $form->add('content', TextareaType::class, [
            'label' => false,
            'row_attr' => [
                'data-allow-delete-item' => $this->isAllowedToHandleItem($comment) ? 'true' : 'false',
            ],
            'attr' => [
                'readonly' => !$this->isAllowedToHandleItem($comment),
                'required' => true,
            ],
        ]);
    }

    private function isAllowedToHandleItem($comment): bool
    {
        return $this->security->isGranted(UserRole::SUPER_ADMIN->value) || $this->security->getUser() === $comment->getUser();
    }
}

<?php

namespace App\Form\Field\Admin;

use App\Enum\UserRole;
use App\Entity\Comment;
use App\Form\Field\CKEditor5Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CKEditor5CollectionEntryType extends AbstractType
{
    public function __construct(private Security $security) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', CKEditor5Type::class, [
                'label' => false,
            ])
            ->addEventListener(
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

        if (!$comment) return;

        if ($form->has('content')) {
            $form->remove('content');
        }

        $form->add('content', CKEditor5Type::class, [
            'label' => false,
            CKEditor5Type::READ_ONLY_OPTION => !$this->isAllowedToHandleItem($comment),
        ]);
    }

    private function isAllowedToHandleItem($comment): bool
    {
        return $this->security->isGranted(UserRole::SUPER_ADMIN->value) || $this->security->getUser() === $comment->getUser();
    }
}

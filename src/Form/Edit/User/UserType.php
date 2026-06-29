<?php

namespace App\Form\Edit\User;

use App\Dto\Users\User;
use App\Dto\Users\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => $this->translator->trans('users.form.username', [], 'users'),
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('users.form.username.not_blank', [], 'users')])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('users.form.email', [], 'users'),
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('users.form.email.not_blank', [], 'users')]),
                    new Email(['message' => $this->translator->trans('users.form.email.invalid', [], 'users')])
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->translator->trans('users.form.password', [], 'users'),
                'required' => false,
                'help' => $this->translator->trans('users.form.password.help', [], 'users')
            ])
            ->add('groups', ChoiceType::class, [
                'choices' => $options['groups'] ?? [],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
                'label' => $this->translator->trans('users.form.groups', [], 'users')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'groups' => [],
            'translation_domain' => 'users',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_edit';
    }
}

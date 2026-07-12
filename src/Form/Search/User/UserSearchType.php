<?php

namespace App\Form\Search\User;

use App\Dto\Users\UserSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'users.listing.form.name', 'required' => false])
            ->add(
                'group',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $options['groups'],
                    'multiple' => true,
                    'attr' => [
                        'class' => 'select2'
                    ],
                    'label' => 'users.listing.form.group'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserSearch::class,
            'groups' => [],
            'csrf_protection' => false,
            'translation_domain' => 'users'
        ]);
    }

    public function getBlockPrefix(): string 
    {
        return 'user_search';
    }
}
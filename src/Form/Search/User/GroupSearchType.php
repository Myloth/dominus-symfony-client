<?php

namespace App\Form\Search\User;

use App\Dto\Users\GroupSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


class GroupSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'groups.listing.form.name'])
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices' => array_flip($options['roles']),
                    'multiple' => true,
                    'attr' => [
                        'class' => 'select2'
                    ],
                    'label' => 'groups.listing.form.roleCode'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupSearch::class,
            'roles' => [],
            'csrf_protection' => false,
            'translation_domain' => 'users'
        ]);
    }

    public function getBlockPrefix(): string 
    {
        return 'group_search';
    }
}
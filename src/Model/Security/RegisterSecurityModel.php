<?php

namespace App\Model\Security;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterSecurityModel extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 255,
                    ]),
                    new Assert\Email([
                        'message' => 'The email {{ value }} is not a valid email.'
                    ]) 
                ]
            ])
            ->add('password', null, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 6,
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => User::class,
                    'fields' => 'email',
                ]),
            ],
        ]);
    }
}

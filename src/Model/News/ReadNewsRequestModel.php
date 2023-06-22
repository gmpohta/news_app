<?php

namespace App\Model\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReadNewsRequestModel extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', CollectionType::class, [
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('userEmail', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('limit', IntegerType::class, [])
            ->add('offset', IntegerType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'csrf_protection' => false
        ]);
    }
}

<?php

namespace App\Model\News;

use App\Entity\News;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;


class PatchNewsModel extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [])
            ->add('body', null, [])
            /*->addEventListener(

                'constraints' => [new Callback([$this, 'validate'])]
            
            )*/
        ;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (empty($context->getName()) && empty($context->getBody()))
            $context->buildViolation('All parameters cannot be empty at the same time.')
                ->addViolation();

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => News::class,
                    'fields' => 'name',
                ]),
            ],
        ]);
    }
}

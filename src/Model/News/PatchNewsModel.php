<?php

namespace App\Model\News;

use App\Entity\News;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PatchNewsModel extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'constraints' => [
                    new Assert\Callback([
                        'callback' => [$this, 'validateNotEmpyQuery'],
                    ]),
                ],
            ])
            ->add('body', null, [])
        ;
    }

    public function validateNotEmpyQuery(?string $value, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot()->getData();

        if (empty($form->getName()) && empty($form->getBody()))
            $context->buildViolation('All parameters cannot be empty at the same time.')
                ->addViolation();
    }

    public function configureOptions(OptionsResolver $resolver): void
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

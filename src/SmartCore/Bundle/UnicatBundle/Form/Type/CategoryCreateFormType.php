<?php

namespace SmartCore\Bundle\UnicatBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class CategoryCreateFormType extends CategoryFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('is_enabled')
            ->remove('is_inheritance')
            ->remove('meta')
            ->remove('properties')
        ;
    }

    public function getName()
    {
        return 'smart_unicat_repository_' . $this->repository->getName() . '_category_create';
    }
}

<?php

namespace SmartCore\Bundle\Unicat2Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StructureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',      null, ['attr'  => ['class' => 'focused']])
            ->add('title_form', null, ['label' => 'Title in forms'])
            ->add('name')
            ->add('entries', 'choice', [
                'choices' => ['single' => 'single', 'multi' => 'multi']
            ])
            ->add('is_required')
            ->add('is_default_inheritance')
            ->add('position')
            ->add('properties')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SmartCore\Bundle\Unicat2Bundle\Entity\UnicatStructure',
        ]);
    }

    public function getName()
    {
        return 'smart_unicat2_structure';
    }
}

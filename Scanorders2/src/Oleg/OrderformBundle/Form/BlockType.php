<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('name')
//            ->add('accession')
//        ;
        
        $builder->add( 'name', 'text', array(
                'label'=>'* Block:', 
                'max_length'=>'3', 
                'required'=>true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Block'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_blocktype';
    }
}

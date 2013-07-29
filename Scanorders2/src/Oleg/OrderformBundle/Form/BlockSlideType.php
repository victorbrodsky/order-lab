<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class BlockSlideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'name', 'choice', array(
                'label'=>' ', 
                'max_length'=>'3', 
                'choices' => $helper->getBlock(),
                'required'=> true,
                'data' => 0,
        ));
        
        $builder->add('slide', 'collection', array(
            'type' => new SlideType(),
            'allow_add' => true,
            'label' => "Slide Entity:",
            //'by_reference' => false,
            //'prototype' => true,
            //'prototype_name' => '__name__',
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

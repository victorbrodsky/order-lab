<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Oleg\OrderformBundle\Helper\FormHelper;

class ResearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
//        $helper = new FormHelper();
        
        $builder->add( 'project', 'text', array(
                'label'=>'Research Project Title:',
                'max_length'=>'500',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'settitle', 'text', array(
            'label'=>'Research Set Title:',
            'max_length'=>'500',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'principal', 'text', array(
            'label'=>'Principal Investigator:',
            'max_length'=>'500',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Research'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_researchtype';
    }
}

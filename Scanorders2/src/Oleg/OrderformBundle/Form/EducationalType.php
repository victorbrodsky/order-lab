<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Oleg\OrderformBundle\Helper\FormHelper;

class EducationalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
//        $helper = new FormHelper();
        
        $builder->add( 'goal', 'text', array(
                'label'=>'Goal:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'course', 'text', array(
            'label'=>'Course Title:',
            'max_length'=>'500',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'lesson', 'text', array(
            'label' => 'Lesson Title:',
            'max_length'=>'500',
            'required'=>false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Educational'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_educationaltype';
    }
}

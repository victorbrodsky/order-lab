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
                'max_length'=>'200',
                'required'=> false,
        ));
        
        $builder->add( 'course', 'text', array(
            'label'=>'Course:',
            'max_length'=>'200',
            'required'=> false,
        ));

        $builder->add( 'lesson', 'text', array(
            'label' => 'Lesson:',
            'max_length'=>200,
            'required'=>false,
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

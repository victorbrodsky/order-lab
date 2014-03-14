<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Oleg\OrderformBundle\Helper\FormHelper;

class EducationalType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

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

        $builder->add( 'director', null, array(
            'label'=>'Course Director:',
            //'max_length'=>'500',
            'required'=> false,
            'attr' => array('class' => 'combobox combobox-width'),
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

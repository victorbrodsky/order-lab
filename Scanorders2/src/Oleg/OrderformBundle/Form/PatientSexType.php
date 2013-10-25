<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PatientSexType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'field', 'choice', array(
            'label'=>'Sex',
            'max_length'=>20,
            'required'=>true,
            'choices' => array("Female"=>"Female", "Male"=>"Male", "None"=>"None"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type')
        ));

        $builder->add('sexothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientSex'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientSex',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_sextype';
    }
}

<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcedurePatsexType extends AbstractType
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
            'label'=>"Patient's Sex (at the time of encounter)",
            'choices' => array("Female"=>"Female", "Male"=>"Male", "Unspecified"=>"Unspecified"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type proceduresex-field')
        ));

        $builder->add('procedurepatsexothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedurePatsex',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedurePatsex',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedurepatsex';
    }
}

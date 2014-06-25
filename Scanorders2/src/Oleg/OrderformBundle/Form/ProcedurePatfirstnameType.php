<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcedurePatfirstnameType extends AbstractType
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

        $builder->add( 'field', 'text', array(
            'label'=>"Patient's First Name (at the time of encounter)",
            'required' => false,
            'attr' => array('class' => 'form-control form-control-modif procedure-firstName')
        ));

//        $builder->add( 'firstName', 'text', array(
//            'label'=>"Patient's First Name (at the time of encounter)",
//            'required' => false,
//            'attr' => array('class' => 'form-control form-control-modif procedure-firstName')
//        ));
//
//        $builder->add( 'middleName', 'text', array(
//            'label'=>"Patient's Middle Name (at the time of encounter)",
//            'required' => false,
//            'attr' => array('class' => 'form-control form-control-modif procedure-middleName')
//        ));

        $builder->add('procedurepatfirstnameothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedurePatfirstname',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedurePatfirstname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedurepatfirstname';
    }
}

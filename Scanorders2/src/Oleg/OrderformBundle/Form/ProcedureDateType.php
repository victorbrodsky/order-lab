<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcedureDateType extends AbstractType
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

        $builder->add('field', 'date', array(
            'label' => "Procedure Date",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patientdob-mask procedure-date', 'style'=>'margin-top: 0;'),
        ));

        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            //echo "procedure time: flag datastructure=".$this->params['datastructure']."<br>";
            $builder->add('time', 'time', array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'Procedure Time:',
                //'attr' => array('class' => 'form-control'),
            ));
        }

        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureDate',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureDate',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_proceduredatetype';
    }
}

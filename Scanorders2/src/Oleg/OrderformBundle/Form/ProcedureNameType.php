<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcedureNameType extends AbstractType
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

//        $builder->add( 'field', 'choice', array(
//            'label'=>'Sex',
//            'max_length'=>20,
//            'required'=>true,
//            'choices' => array("Female"=>"Female", "Male"=>"Male", "None"=>"None"),
//            'multiple' => false,
//            'expanded' => true,
//            'attr' => array('class' => 'horizontal_type')
//        ));
        if($this->params['type'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'edit' ) {
            $attr = array('class' => 'ajax-combobox-procedure', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'combobox combobox-width');    //show
        }
        $builder->add('field', 'custom_selector', array(
            'label' => 'Procedure Type',
            'required' => false,
            'attr' => $attr,
            'classtype' => 'procedureType'
        ));

        $builder->add('procedureothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_nametype';
    }
}

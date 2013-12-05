<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PartNameType extends AbstractType
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

        if( $this->params['type'] == 'singleorder') {
            $label = false;
        } else {
            $label = 'Part Name';
        }

        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-partname keyfield', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'form-control form-control-modif');    //show
        }
        $builder->add('field', 'custom_selector', array(
            'label' => $label,
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'partname'
        ));

        $builder->add('partnameothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
            'label' => false
        ));


//        if($this->params['type'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'edit' ) {
//            $attr = array('class' => 'ajax-combobox-partname keyfield', 'type' => 'hidden');    //new
//        } else {
//            $attr = array('class' => 'combobox combobox-width');    //show
//        }
//        $builder->add('field', 'custom_selector', array(
//            'label' => 'Part Name',
//            'required' => false,
//            'attr' => $attr,
//            'classtype' => 'partname'
//        ));
//
//        $builder->add('partothers', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
//            'label' => false
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partnametype';
    }
}

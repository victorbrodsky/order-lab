<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class PartDiseaseTypeType extends AbstractType
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
            'label'=>'Type of Disease',
            //'required'=>false,
            'choices' => array("Neoplastic"=>"Neoplastic", "Non-Neoplastic"=>"Non-Neoplastic", "None"=>"None"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type diseaseType'), //'required' => '0', 'disabled'
            //'data' => 'Male',
        ));

        $builder->add( 'origin', 'choice', array(
            'label'=>'Origin:',
            'required'=>false,
            'choices' => array("Primary"=>"Primary", "Metastatic"=>"Metastatic"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type'),
        ));

//        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'edit' ) {
//            $attr = array('class' => 'ajax-combobox-organ', 'type' => 'hidden');    //new
//        } else {
//            $attr = array('class' => 'form-control form-control-modif');    //show
//        }

        $attr = array('class' => 'ajax-combobox-organ', 'type' => 'hidden');

        $builder->add('primaryOrgan', 'custom_selector', array(
            'label' => 'Primary Site of Origin:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'sourceOrgan'
        ));

        $builder->add('partothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partdiseasetypetype';
    }
}

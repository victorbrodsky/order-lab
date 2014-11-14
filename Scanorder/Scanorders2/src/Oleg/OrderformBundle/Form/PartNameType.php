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

        if( $this->params['cicle'] != 'show' && $this->params['type'] == 'One-Slide Scan Order' && $this->params['cicle'] != 'amend' && $this->params['cicle'] != 'edit' ) {
            $label = false;
        } else {
            $label = 'Part Name';
        }

        $attr = array('class' => 'ajax-combobox ajax-combobox-partname keyfield partname-mask', 'type' => 'hidden' );

        $builder->add('field', 'custom_selector', array(
            'label' => $label,
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'partname'
        ));

        $builder->add('partnameothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

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

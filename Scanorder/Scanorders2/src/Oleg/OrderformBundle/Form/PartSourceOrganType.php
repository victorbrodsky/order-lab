<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class PartSourceOrganType extends AbstractType
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

        $attr = array('class' => 'ajax-combobox ajax-combobox-organ', 'type' => 'hidden');

        $builder->add('field', 'custom_selector', array(
            'label' => 'Source Organ',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'sourceOrgan'
        ));


        $builder->add('partothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartSourceOrgan',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartSourceOrgan',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partsourceorgantype';
    }
}

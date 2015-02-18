<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ScanOrderType extends AbstractType
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

        if( $this->params['cycle'] == 'show' ) {
            //echo "entity service";
            $builder->add('service', 'entity', array(
                'label' => 'Service:',
                'required'=> false,
                'multiple' => false,
                'class' => 'OlegUserdirectoryBundle:Service',
                //'choices' => $this->params['services'],
                'attr' => array('class' => 'combobox combobox-width')
            ));
        } else {
            //service. User should be able to add institution to administrative or appointment titles
            $builder->add('service', 'employees_custom_selector', array(
                'label' => "Service:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
                'classtype' => 'service'
            ));
        }
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ScanOrder'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_scanordertype';
    }
}

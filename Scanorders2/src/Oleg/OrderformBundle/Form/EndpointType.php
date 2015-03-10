<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class EndpointType extends AbstractType
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

        ////////////// Location //////////////////////
        //use Endpoint object: destination - location

        $label = "Location:";

        if( array_key_exists('label', $this->params) ) {
            $label = $this->params['label'];
        }

        $destinationLocationsOptions = array(
            'label' => $label,
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
            'classtype' => 'location',
        );

        //locations default and preferred choices
        if( $this->params['cycle'] == 'new' && array_key_exists('destinationLocation', $this->params) ) {
            $destinationLocation = $this->params['destinationLocation'];
            $destinationLocationsOptions['data'] = $destinationLocation['data']->getId();
        }

        if( $this->params['cycle'] == 'show' ) {
            $builder->add('location', 'entity', array(
                'label' => $label,
                'required'=> false,
                'multiple' => false,
                'class' => 'OlegUserdirectoryBundle:Location',
                'attr' => array('class' => 'combobox combobox-width')
            ));
        } else {
            $builder->add('location', 'employees_custom_selector', $destinationLocationsOptions);
        }
        ////////////// EOF Location //////////////////////




        ////////////// System //////////////////////

        ////////////// EOF System //////////////////////
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Endpoint'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_endpointtype';
    }
}

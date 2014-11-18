<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Location;

class BuildingType extends AbstractType
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

//        $builder->add('geo', new GeoLocationType(), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
//            'label' => false,
//            'required' => false,
//        ));

//        $builder->add('building', 'employees_custom_selector', array(
//            'label' => 'Building:',
//            'attr' => array('class' => 'ajax-combobox-building', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'building'
//        ));


        $builder->add('name',null,array(
            'label'=>'Building Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('abbreviation',null,array(
            'label'=>'Building Abbreviation:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('geoLocation', new GeoLocationType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\BuildingList',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_building';
    }
}

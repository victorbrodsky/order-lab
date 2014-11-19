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

        $standAloneLocation = false;
        if( strpos($this->params['cicle'],'_standalone') !== false && strpos($this->params['cicle'],'new') === false ) {
            $standAloneLocation = true;
        }

        //add user and list properties for stand alone location managemenet by LocationController
        if( $standAloneLocation ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cicle'] = $this->params['cicle'];
            $params['standalone'] = true;
            $mapper['className'] = "BuildingList";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            $builder->add('list', new ListType($params, $mapper), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\BuildingList',
                'label' => false
            ));
        }

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

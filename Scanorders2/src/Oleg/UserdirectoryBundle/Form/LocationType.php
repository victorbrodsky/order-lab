<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class LocationType extends AbstractType
{

//    protected $params;
//    protected $entity;
//
//    public function __construct( $params=null, $entity = null )
//    {
//        $this->params = $params;
//        $this->entity = $entity;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add('id','hidden',array(
            'label'=>false,
        ));

        $builder->add('name',null,array(
            'label'=>"Location's Name:",
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('phone',null,array(
            'label'=>'Phone Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('pager',null,array(
            'label'=>'Pager Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('mobile',null,array(
            'label'=>'Mobile Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('fax',null,array(
            'label'=>'Fax:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('email',null,array(
            'label'=>'E-Mail:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('room',null,array(
            'label'=>'Room Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('street',null,array(
            'label'=>'Street Address:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('city',null,array(
            'label'=>'City:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('state',null,array(
            'label'=>'State:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('zip',null,array(
            'label'=>'Zip Code:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('associatedCode',null,array(
            'label'=>'Associated NYPH Code:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('assistant','entity',array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=>"Assistant's Name:",
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => false,
            //'empty_value' => false,
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_location';
    }
}

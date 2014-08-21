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
            'label'=>'Name:',
            'attr' => array('class'=>'form-control')
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

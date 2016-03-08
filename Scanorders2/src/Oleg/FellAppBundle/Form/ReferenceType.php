<?php

namespace Oleg\FellAppBundle\Form;

use Oleg\UserdirectoryBundle\Entity\Identifier;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ReferenceType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', null, array(
            'label' => 'Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('title', null, array(
            'label' => 'Title:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('geoLocation', new GeoLocationType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

        $builder->add('institution', 'employees_custom_selector', array(
            'label' => 'Institution:',
            'attr' => array('class' => 'ajax-combobox-traininginstitution', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'traininginstitution'
        ));


        //Reference Letters
        $builder->add('documents', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Reference Letter(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        $builder->add('email', 'email', array(
            'label' => 'E-Mail:',
            'attr' => array('class'=>'form-control')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\FellAppBundle\Entity\Reference',
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_reference';
    }
}

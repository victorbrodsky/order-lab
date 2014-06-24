<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class SlideReturnRequestType extends AbstractType
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

        $builder->add('returnSlide', 'custom_selector', array(
            'label' => 'Return Slides to:',
            'attr' => array('class' => 'ajax-combobox-return', 'type' => 'hidden'),
            'required'=>true,
            'classtype' => 'returnSlide'
        ));

        //$builder->add( 'provider', new ProviderType($this->params,$this->entity), array('label'=>'Submitter:') );

        $builder->add('urgency', 'custom_selector', array(
            'label' => 'Urgency:',
            'attr' => array('class' => 'ajax-combobox-urgency', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'urgency'
        ));

        $builder->add('slide', 'collection', array(
            'type' => new SlideSimpleType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slides__',
        ));

        $builder->add( 'provider', new ProviderType(), array('label'=>'Submitter:') );

        $builder->add('proxyuser', 'entity', array(
            'class' => 'OlegOrderformBundle:User',
            'label'=>'Ordering Provider:',
            'required' => false,
            //'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles OR u=:user')
                        ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                },
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SlideReturnRequest'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidereturnrequesttype';
    }
}

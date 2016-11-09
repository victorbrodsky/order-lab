<?php

namespace Oleg\UserdirectoryBundle\Form\FormNode;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class FormNodeType extends AbstractType
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

//        $builder->add('id','hidden',array('label'=>false));

//        $builder->add( 'name', 'text', array(
//            'label'=>$this->params['label'].' Title:',   //'Admnistrative Title:',
//            'required'=>false,
//            'attr' => array('class' => 'form-control')
//        ));



    }


//    public function addFormNodes( $form, $holder, $params ) {
//
//        $rootFormNode = $holder->getFormNode();
//        if( !$rootFormNode ) {
//            return $form;
//        }
//
//        $form = $this->addFormNodeRecursively($form,$rootFormNode,$params);
//
//        return $form;
//    }
//
//
//    public function addFormNodeRecursively( $form, $formNode, $params ) {
//
//        $children = $formNode->getChildren();
//        if( $children ) {
//
//            $form->add('children', 'collection', array(
//                'type' => new FormNodeType($params,$formNode),
//                'label' => false,
//                'required' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__formnodechildren__',
//            ));
//
//        } else {
//            $form->add('formNode');
//        }
//
//    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\FormNode',
            //'csrf_protection' => false
            //'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_formnodetype';
    }
}

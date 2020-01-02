<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\UserdirectoryBundle\Form\FormNode;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

//Used as a base in MessageCategoryFormNodeType
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
//            $form->add('children', CollectionType::class, array(
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

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_formnodetype';
    }
}

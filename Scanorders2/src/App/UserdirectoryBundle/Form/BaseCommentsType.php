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

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Entity\PrivateComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class BaseCommentsType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'id', HiddenType::class, array(
            'label'=>false,
            'required'=>false,
            'attr' => array('class' => 'comment-field-id')
        ));

        $builder->add( 'comment', TextareaType::class, array(
            'label'=>'Comment:',
            'disabled' => $this->params['disabled'],
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

        if( $this->params['fullClassName'] == "Oleg\UserdirectoryBundle\Entity\PrivateComment" ) {
            $baseAttr = new PrivateComment();
            $builder->add('status', ChoiceType::class, array(
                'disabled' => ($this->params['roleAdmin'] ? false : true),
//                'choices'   => array(
//                    $baseAttr::STATUS_UNVERIFIED => $baseAttr->getStatusStrByStatus($baseAttr::STATUS_UNVERIFIED),
//                    $baseAttr::STATUS_VERIFIED => $baseAttr->getStatusStrByStatus($baseAttr::STATUS_VERIFIED)
//                ),
                'choices' => array(
                    $baseAttr->getStatusStrByStatus($baseAttr::STATUS_UNVERIFIED) => $baseAttr::STATUS_UNVERIFIED,
                    $baseAttr->getStatusStrByStatus($baseAttr::STATUS_VERIFIED) => $baseAttr::STATUS_VERIFIED
                ),
                //'choices_as_values' => true,
                'invalid_message' => 'organizationalGroup invalid value',
                'label' => "Status:",
                'required' => true,
                'attr' => array('class' => 'combobox combobox-width'),
            ));
        }


//        $builder->add('commentType', 'employees_custom_selector', array(
//            'label' => 'Comment Category:',
//            'attr' => array('class' => 'ajax-combobox-commenttype', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'commentType'
//        ));
//
//
//        $builder->add('commentSubType', 'employees_custom_selector', array(
//            'label' => 'Comment Name:',
//            'attr' => array('class' => 'ajax-combobox-commentsubtype', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'commentSubType'
//        ));


        $builder->add('documents', CollectionType::class, array(
            //'type' => new DocumentType($this->params),
            'entry_type' => DocumentType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
			$mapper = array(
                        'prefix' => "Oleg",
                        'className' => "CommentTypeList",
                        'bundleName' => "UserdirectoryBundle",
                        'organizationalGroupType' => "CommentGroupType"
                    );
            if( $title ) {
                $commentType = $title->getCommentType();
                if( $commentType ) {                  
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:CommentTypeList')->getLevelLabels($commentType,$mapper) . ":";
                }
            }
			if( !$label ) {
                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:CommentTypeList')->getLevelLabels(null,$mapper) . ":";
            }

            $form->add('commentType', CustomSelectorType::class, array( //'employees_custom_selector'
                'label' => $label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'CommentTypeList'
                ),
                'classtype' => 'commenttype'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////





    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_'.$this->params['formname'];
    }
}

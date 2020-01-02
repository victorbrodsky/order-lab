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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class ListType extends AbstractType
{

    protected $params;

    protected $mapper;

    protected $addwhere = "";

    protected $types = array(
                            "default"=>"default",
                            "user-added"=>"user-added",
                            "disabled"=>"disabled",
                            "draft"=>"draft",
                            "hidden"=>"hidden"
                        );

    public function formConstructor( $params=null, $mapper=null )
    {
        $this->params = $params;
        $this->mapper = $mapper;

        if( array_key_exists('id', $this->params) ) {
            $this->addwhere = " AND list.id != ".$this->params['id'];
        }
        //echo "addwhere=".$this->addwhere."<br>";

        if( $this->mapper['className'] == 'AccessionType' || $this->mapper['className'] == 'accessiontype' ) {
            $this->types['TMA'] = 'TMA';
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_mapper']);

        $builder->add('orderinlist',null,array(
            'label'=>'Display Order:',
            'required' => true,
            'attr' => array('class'=>'form-control')
        ));

        if( !array_key_exists('standalone', $this->params) || $this->params['standalone'] == false ) {
            $builder->add('name',null,array(
                'label'=>'Name:',
                'attr' => array('class'=>'form-control')
            ));
        }

        $builder->add('abbreviation',null,array(
            'label' => 'Abbreviation:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('shortname',null,array(
            'label' => 'Short Name:',
            'attr' => array('class' => 'form-control')
        ));

        //description
        $descriptionLabel = 'Description:';
        if( array_key_exists('labels', $this->mapper) && $this->mapper['labels'] ) {
            if( array_key_exists('description', $this->mapper['labels']) && $this->mapper['labels']['description'] ) {
                $descriptionLabel = $this->mapper['labels']['description'];
            }
        }
        $builder->add('description', TextareaType::class, array(
            'label' => $descriptionLabel,
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('type',ChoiceType::class,array( //flipped
            'label'=>'Type:',
            'choices' => $this->types,
            //'choices_as_values' => true,
            'required' => true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width select2-list-type')
        ));

        $builder->add('creator',null,array(
            'label'=>'Creator:',
            'required'=>true,
            'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
        ));

        $builder->add('createdate', DateType::class, array(
            'label' => 'Creation Date:',
            'widget' => 'single_text',
            'required' => true,
            //'disabled' => true,
            'format' => 'MM/dd/yyyy, H:mm:ss',
            'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
            'attr' => array('class' => 'form-control', 'readonly'=>true),
        ));


//        if( array_key_exists('cycle', $this->params) && $this->params['cycle'] != 'new' ) {
//
//                //echo "cycle=".$this->params['cycle']."<br>";
//
////                $builder->add('updatedby',null,array(
////                    'label'=>'Updated by:',
////                    'required'=>false,
////                    'disabled'=>true,
////                    'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
////                ));
//
////                $builder->add( 'updatedon', 'date', array(
////                    'label'=>'Updated on:',
////                    'widget' => 'single_text',
////                    'required'=>false,
////                    'disabled'=>true,
////                    'format' => 'MM/dd/yyyy, H:mm:ss',
////                    'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
////                    'attr' => array('class' => 'form-control'),
////                ));
//
//        }


        $builder->add('synonyms', EntityType::class, array(
            'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
            'label' => 'Synonyms:',
            //'disabled' => true,
            'required' => false,
            'multiple' => true,
            //'by_reference' => false,
            'attr' => array('class' => 'combobox combobox-width select2-list-synonyms'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where( "list.type != :disabletype AND list.type != :drafttype AND list.type != :hiddentype" . $this->addwhere )
                    ->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft','hiddentype'=>'hidden') );
            },
        ));

        $builder->add('original', EntityType::class, array(
            'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
            'label'=>'Original (Canonical) Synonymous Term:',
            'required' => false,
            'multiple' => false,
            //'by_reference' => false,
            'attr' => array('class' => 'combobox combobox-width select2-list-original'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list');
                    //->where( "list.type = :type" . $this->addwhere )
                    //->setParameter( 'type','default' );
            },
        ));

        $builder->add('linkToListId',null,array(
            'label' => 'Link to List ID:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('objectType',null,array(
            'label' => 'Object Type:',
            'attr' => array('class' => 'combobox')
        ));

        $builder->add('entityNamespace',null,array(
            'label' => 'List Object Namespace:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('entityName',null,array(
            'label' => 'List Object Name:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('entityId',null,array(
            'label' => 'List Object ID:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('version',null,array(
            'label' => 'Version:',
            'attr' => array('class' => 'form-control')
        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true,
            'form_custom_value' => null,
            'form_custom_value_mapper' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_listtype';
    }
}

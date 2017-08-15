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

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoggerFilterType extends AbstractType
{

    protected $params;

//    private $hideObjectType;
//    private $hideObjectId;
//    private $hideUser;
//    private $hideEventType;
//    private $hideIp;
//    private $hideRoles;

    public function __construct( $params=null )
    {
        $this->params = $params;

//        if( array_key_exists('hideObjectType', $params) ) {
//            $this->hideObjectType = $params['hideObjectType'];
//        } else {
//            $this->hideObjectType = false;
//        }
//
//        if( array_key_exists('hideObjectId', $params) ) {
//            $this->hideObjectId = $params['hideObjectId'];
//        } else {
//            $this->hideObjectId = false;
//        }
//
//        if( array_key_exists('hideUser', $params) ) {
//            $this->hideUser = $params['hideUser'];
//        } else {
//            $this->hideUser = false;
//        }
//
//        if( array_key_exists('hideEventType', $params) ) {
//            $this->hideEventType = $params['hideEventType'];
//        } else {
//            $this->hideEventType = false;
//        }
//
//        if( array_key_exists('hideIp', $params) ) {
//            $this->hideIp = $params['hideIp'];
//        } else {
//            $this->hideIp = false;
//        }
//
//        if( array_key_exists('hideRoles', $params) ) {
//            $this->hideRoles = $params['hideRoles'];
//        } else {
//            $this->hideRoles = false;
//        }
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('user', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            //'read_only' => $this->hideUser,
            'property' => 'getUserNameStr',
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'), //,'style' => 'display:none'
            'choices' => $this->params['filterUsers'],
        ));

        //Event Type
        $builder->add('eventType', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:EventTypeList',
            //'placeholder' => 'Fellowship Type',
            //'read_only' => $this->hideEventType,
            'property' => 'name',
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.name", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

//        $builder->add('filter', 'choice', array(
//            'label' => false,
//            'required'=> false,
//            //'multiple' => false,
//            'choices' => $this->params['fellTypes'],
//            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty-filter'),
//        ));

        $builder->add('search', 'text', array(
            //'placeholder' => 'Search',
            'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));


        $builder->add('startdate', 'datetime', array(
            'label' => false, //'Start Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy H:m',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'Start Date/Time')
        ));

        $builder->add('enddate', 'datetime', array(
            'label' => false, //'End Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy H:m',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'End Date/Time')
        ));

        $builder->add('ip', 'text', array(
            //'placeholder' => 'Search',
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

        $builder->add('roles', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Roles',
            'property' => 'alias',
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.name", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

//        $builder->add('objectName', 'text', array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

        //objectType
        $builder->add('objectType', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:EventObjectTypeList',
            //'read_only' => $this->hideObjectType,
            //'placeholder' => 'Fellowship Type',
            'property' => 'name',
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.name", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        $builder->add('objectId', 'text', array(
            //'read_only' => $this->hideObjectId,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

//        //Capacity
//        if( $this->params['sitename'] == "calllog" ) {
//            $capacities = array(
//                "Submitter" => "Submitter",
//                "Attending" => "Attending"
//            );
//            $builder->add('capacity', 'choice', array(
//                'label' => false,
//                'required'=> false,
//                //'multiple' => false,
//                'choices' => $capacities,
//                'attr' => array('class' => 'combobox', 'placeholder' => 'Capacity'),
//            ));
//        }
        $this->addOptionalFields($builder);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

    public function addOptionalFields( $builder ) {
        return null;
    }
}

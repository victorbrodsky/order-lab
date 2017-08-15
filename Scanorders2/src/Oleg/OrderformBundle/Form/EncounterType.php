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

namespace Oleg\OrderformBundle\Form;

use Oleg\OrderformBundle\Form\EncounterAttendingPhysicianType;
use Oleg\OrderformBundle\Form\EncounterReferringProviderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EncounterType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('alias', $this->params) ) {
            $this->params['alias'] = true;
        }

        if( !array_key_exists('attendingPhysicians-readonly', $this->params) ) {
            $this->params['attendingPhysicians-readonly'] = true;
        }
        if( !array_key_exists('referringProviders-readonly', $this->params) ) {
            $this->params['referringProviders-readonly'] = true;
        }

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //children: if X=3, show only the first 3 levels (patient + encounter + procedure)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 3 ) {
            $builder->add('procedure', 'collection', array(
                'type' => new ProcedureType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedure__',
            ));
        }


//        $builder->add('name', 'collection', array(
//            'type' => new EncounterNameType($this->params, $this->entity),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Encounter Type:",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encountername__',
//        ));

        $builder->add('date', 'collection', array(
            'type' => new EncounterDateType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterdate__',
        ));

        $builder->add('patsuffix', 'collection', array(
            'type' => new EncounterPatsuffixType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatsuffix__',
        ));
        $builder->add('patlastname', 'collection', array(
            'type' => new EncounterPatlastnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatlastname__',
        ));
        $builder->add('patfirstname', 'collection', array(
            'type' => new EncounterPatfirstnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatfirstname__',
        ));
        $builder->add('patmiddlename', 'collection', array(
            'type' => new EncounterPatmiddlenameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatmiddlename__',
        ));

        $builder->add('patsex', 'collection', array(
            'type' => new EncounterPatsexType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatsex__',
        ));

        $attr = array(
            'class'=>'form-control encounterage-field patientage-mask element-with-tooltip-always',
            'data-toggle'=>"tooltip",
            'title'=>"To change the calculated age at the time of the encounter, please edit the Date of Birth (DOB) value and/or the Encounter Date value."
        );
        $gen_attr = array(
            'label'=>"Patient's Age (at the time of encounter):",
            'class'=>'Oleg\OrderformBundle\Entity\EncounterPatage',
            'type'=>'text');
        $builder->add('patage', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Patient's Age (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatage__',
        ));

        //pathistory'
        $attr = array('class'=>'textarea form-control encounterhistory-field');
        $gen_attr = array('label'=>"Clinical History (at the time of encounter):",'class'=>'Oleg\OrderformBundle\Entity\EncounterPathistory','type'=>null);
        $builder->add('pathistory', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical History (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpathistory__',
        ));



        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

//            $builder->add('keytype', 'entity', array(
//                'class' => 'OlegOrderformBundle:EncounterType',
//                'label' => 'Encounter Type:',
//                'required' => true,
//                'data' => 1,
//                'attr' => array('style'=>'display:none;'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setMaxResults(1);
//
//                    },
//            ));

            //number and source
            $builder->add('number', 'collection', array(
                'type' => new EncounterNumberType($this->params, $this->entity),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounternumber__',
            ));

            $builder->add('location', 'collection', array(
                'type' => new EncounterLocationType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterlocation__',
            ));

//            $sources = array('WCMC Epic Ambulatory EMR','Written or oral referral');
//            $params = array('name'=>'Encounter','dataClass'=>'Oleg\OrderformBundle\Entity\EncounterOrder','typename'=>'encounterorder','sources'=>$sources);
//            $builder->add('order', 'collection', array(
//                'type' => new GeneralOrderType($params, null),
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__encounterorder__',
//            ));

            $builder->add('inpatientinfo', 'collection', array(
                'type' => new EncounterInpatientinfoType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterinpatientinfo__',
            ));

            $builder->add('provider', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => 'Provider:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->leftJoin("u.infos", "infos")
                        ->leftJoin("u.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                        ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                        ->orderBy("infos.displayName","ASC");
//                        return $er->createQueryBuilder('u')
//                            ->where('u.roles LIKE :roles OR u=:user')
//                            ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                    },
            ));

        }


        //extra data-structure fields for datastructure-patient
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure-patient' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('provider', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => 'Provider:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->leftJoin("u.infos", "infos")
                        ->leftJoin("u.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                        ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                        ->orderBy("infos.displayName","ASC");
//                    return $er->createQueryBuilder('u')
//                        ->where('u.roles LIKE :roles OR u=:user')
//                        ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                },
            ));

            $builder->add( 'encounterStatus', 'entity', array(
                'class' => 'OlegOrderformBundle:EncounterStatusList',
                //'property' => 'name',
                'label'=>'Encounter Status:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add('encounterInfoTypes', 'collection', array(
                'type' => new EncounterInfoTypeType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterinfotypes__',
            ));

            //Referring Provider for calllog new entry
            $builder->add('referringProviders', 'collection', array(
                'type' => new EncounterReferringProviderType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterreferringprovider__',
            ));

            $builder->add('attendingPhysicians', 'collection', array(
                'type' => new EncounterAttendingPhysicianType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterattendingphysician__',
            ));

        }


        //messages
        if( array_key_exists('datastructure',$this->params) && ($this->params['datastructure'] == 'datastructure' || $this->params['datastructure'] == 'datastructure-patient') ) {
            $builder->add('message', 'collection', array(
                'type' => new MessageObjectType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encountermessage__',
            ));
        }
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Encounter'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encountertype';
    }
}

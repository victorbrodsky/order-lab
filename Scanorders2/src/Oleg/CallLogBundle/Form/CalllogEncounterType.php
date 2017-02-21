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

namespace Oleg\CallLogBundle\Form;

use Oleg\OrderformBundle\Form\EncounterAttendingPhysicianType;
use Oleg\OrderformBundle\Form\EncounterInfoTypeType;
use Oleg\OrderformBundle\Form\EncounterLocationType;
use Oleg\OrderformBundle\Form\EncounterPatfirstnameType;
use Oleg\OrderformBundle\Form\EncounterPatlastnameType;
use Oleg\OrderformBundle\Form\EncounterPatmiddlenameType;
use Oleg\OrderformBundle\Form\EncounterPatsexType;
use Oleg\OrderformBundle\Form\EncounterPatsuffixType;
use Oleg\OrderformBundle\Form\EncounterReferringProviderType;
use Oleg\OrderformBundle\Form\GenericFieldType;

use Oleg\UserdirectoryBundle\Form\TrackerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CalllogEncounterType extends AbstractType
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
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        $builder->add('status', 'hidden');

        $builder->add('date', 'collection', array(
            'type' => new CalllogEncounterDateType($this->params, null),
            'read_only' => $this->params['readonlyEncounter'],
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterdate__',
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $encounter = $event->getData();
            $form = $event->getForm();

            //hide alias for invalid encounter
            if( $encounter ) {
                $status = $encounter->getStatus();
                //echo "status=".$status."<br>";
                if( $status == 'invalid' ) {
                    $this->params['alias'] = false;
                } else {
                    $this->params['alias'] = true;
                }
            }

            $form->add('patsuffix', 'collection', array(
                'type' => new EncounterPatsuffixType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatsuffix__',
            ));
            $form->add('patlastname', 'collection', array(
                'type' => new EncounterPatlastnameType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatlastname__',
            ));
            $form->add('patfirstname', 'collection', array(
                'type' => new EncounterPatfirstnameType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatfirstname__',
            ));
            $form->add('patmiddlename', 'collection', array(
                'type' => new EncounterPatmiddlenameType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatmiddlename__',
            ));

            $form->add('patsex', 'collection', array(
                'type' => new EncounterPatsexType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatsex__',
            ));

        });

//        $attr = array('class'=>'form-control encounterage-field patientage-mask');
//        $gen_attr = array('label'=>"Patient's Age (at the time of encounter):",'class'=>'Oleg\OrderformBundle\Entity\EncounterPatage','type'=>'text');
//        $builder->add('patage', 'collection', array(
//            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Patient's Age (at the time of encounter):",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterpatage__',
//        ));

        //pathistory'
//        $attr = array('class'=>'textarea form-control encounterhistory-field');
//        $gen_attr = array('label'=>"Clinical History (at the time of encounter):",'class'=>'Oleg\OrderformBundle\Entity\EncounterPathistory','type'=>null);
//        $builder->add('pathistory', 'collection', array(
//            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Clinical History (at the time of encounter):",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterpathistory__',
//        ));

        //number and source
        $builder->add('number', 'collection', array(
            'type' => new CalllogEncounterNumberType($this->params, $this->entity),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounternumber__',
        ));

//        $builder->add('location', 'collection', array(
//            'type' => new EncounterLocationType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterlocation__',
//        ));

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
//
//        $builder->add('inpatientinfo', 'collection', array(
//            'type' => new EncounterInpatientinfoType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterinpatientinfo__',
//        ));

        //echo "get provider <br>";
        $builder->add('provider', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => 'Provider:',
            'required' => false,
            'read_only' => $this->params['readonlyEncounter'],
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = 0 OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                    //->where('u.roles LIKE :roles OR u=:user')
                    //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));


        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
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

        $builder->add('encounterInfoTypes', 'collection', array(
            'type' => new EncounterInfoTypeType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterinfotypes__',
        ));

        $builder->add('patientDob', 'date', array(
            'label' => "Date of Birth:",
            'widget' => 'single_text',
            'required' => false,
            //'mapped' => false,
            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patient-dob-date'), //'style'=>'margin-top: 0;'
        ));

        if( $this->params['cycle'] != 'show' ) {
            //echo "show locationName <br>";
            $builder->add('locationName', 'employees_custom_selector', array(
                'label' => "Location Name:",
                'mapped' => false,
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-locationName', 'type' => 'hidden'),
                'classtype' => 'locationName'
            ));
//            $builder->add('locationName', 'text', array(
//                'label' => "Location Name:",
//                'mapped' => false,
//                //'required' => false,
//                //'attr' => array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
//                //'classtype' => 'location'
//            ));
        }
        $builder->add('tracker', new TrackerType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Tracker',
            'label' => false,
        ));

//        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
//            $encounter = $event->getData();
//            $form = $event->getForm();
//
//            //echo "set encounter name=".$encounter['locationName']."<br>";
//            //print_r($encounter);
//
//            //exit('PRE_SUBMIT');
//            if( isset($encounter['locationName']) ) {
//                echo "set encounter name=".$encounter['locationName']."<br>";
//                $encounter->setName($encounter['locationName']);
//                //exit('PRE_SUBMIT');
//            }
//        });

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

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Encounter',
            //'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encountertype';
    }
}

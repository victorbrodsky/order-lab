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

use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
//use Oleg\UserdirectoryBundle\Form\TrackerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CalllogEncounterType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('alias', $this->params) ) {
            $this->params['alias'] = true;
        }

        if( !array_key_exists('readonlyEncounter', $this->params) ) {
            $this->params['readonlyEncounter'] = false;
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $builder->add('id', HiddenType::class);

        $builder->add('status', HiddenType::class);

        $builder->add('date', CollectionType::class, array(
            'entry_type' => CalllogEncounterDateType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            //'disabled' => $this->params['readonlyEncounter'],
            'attr' => array('readonly' => $this->params['readonlyEncounter']),
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

            //hide alias for dummy encounter
            if( $encounter ) {
                $status = $encounter->getStatus();
                if( $status == 'invalid' || $status == 'dummy' || $this->params['readonlyEncounter'] ) {
                    $this->params['alias'] = false;
                } else {
                    $this->params['alias'] = true;
                }
            }

            $form->add('patsuffix', CollectionType::class, array(
                'entry_type' => EncounterPatsuffixType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                //'disabled' => $this->params['readonlyEncounter'],
                'attr' => array('readonly' => $this->params['readonlyEncounter']),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatsuffix__',
            ));
            $form->add('patlastname', CollectionType::class, array(
                'entry_type' => EncounterPatlastnameType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                //'disabled' => $this->params['readonlyEncounter'],
                'attr' => array('readonly' => $this->params['readonlyEncounter']),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatlastname__',
            ));
            $form->add('patfirstname', CollectionType::class, array(
                'entry_type' => EncounterPatfirstnameType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                //'disabled' => $this->params['readonlyEncounter'],
                'attr' => array('readonly' => $this->params['readonlyEncounter']),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatfirstname__',
            ));
            $form->add('patmiddlename', CollectionType::class, array(
                'entry_type' => EncounterPatmiddlenameType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                //'disabled' => $this->params['readonlyEncounter'],
                'attr' => array('readonly' => $this->params['readonlyEncounter']),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterpatmiddlename__',
            ));

            $form->add('patsex', CollectionType::class, array(
                'entry_type' => EncounterPatsexType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                //'disabled' => $this->params['readonlyEncounter'],
                'attr' => array('readonly' => $this->params['readonlyEncounter']),
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
//        $builder->add('patage', CollectionType::class, array(
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
//        $builder->add('pathistory', CollectionType::class, array(
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
        $builder->add('number', CollectionType::class, array(
            'entry_type' => CalllogEncounterNumberType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounternumber__',
        ));

        //TODO: select box showing new and previous encounters
//        $builder->add('previousEncounters', ChoiceType::class, array(
//            'label' => "New or Previous Encounter",
//            'choices' => $this->params['previousEncounters'],
//            'required' => false,
//            'mapped' => false,
//            'attr' => array('class' => 'combobox', 'placeholder' => "Previous Encounters")
//        ));
        if(0) {
            $builder->add('previousEncounters', EntityType::class, array(
                'class' => 'OlegOrderformBundle:Encounter',
                'label' => 'New or Previous Encounter:',
                'required' => false,
                'mapped' => false,
                'multiple' => false,
                //'data' => $this->params['previousEncounters'],
                'choices' => $this->params['previousEncounters'],
                'choice_label' => 'obtainEncounterNumber',
                'attr' => array('class' => 'combobox', 'placeholder' => "New or Previous Encounters"),
            ));
        }

//        $builder->add('location', CollectionType::class, array(
//            'type' => new EncounterLocationType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterlocation__',
//        ));

//            $sources = array('WCM Epic Ambulatory EMR','Written or oral referral');
//            $params = array('name'=>'Encounter','dataClass'=>'Oleg\OrderformBundle\Entity\EncounterOrder','typename'=>'encounterorder','sources'=>$sources);
//            $builder->add('order', CollectionType::class, array(
//                'type' => new GeneralOrderType($params, null),
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__encounterorder__',
//            ));
//
//        $builder->add('inpatientinfo', CollectionType::class, array(
//            'type' => new EncounterInpatientinfoType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encounterinpatientinfo__',
//        ));

//        if( $this->params['readonlyEncounter'] ) {
//            echo "readonlyEncounter true <br>";
//        } else {
//            echo "readonlyEncounter false <br>";
//        }
        //$this->params['readonlyEncounter'] = false;

        //Provider
        $providerAttr = array('class' => 'combobox combobox-width');
        if( $this->params['readonlyEncounter'] ) {
            $providerAttr['readonly'] = true;
        }
        $builder->add('provider', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => 'Provider:',
            'required' => false,
            //'disabled' => $this->params['readonlyEncounter'],
            'attr' => $providerAttr,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->leftJoin("u.infos", "infos")
                    ->leftJoin("u.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                    ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                    ->orderBy("infos.displayName","ASC");
                    //->where('u.roles LIKE :roles OR u=:user')
                    //->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user']));
            },
        ));


        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('message', CollectionType::class, array(
                'entry_type' => MessageObjectType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                    'form_custom_value_entity' => null
                ),
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
        $builder->add('referringProviders', CollectionType::class, array(
            'entry_type' => EncounterReferringProviderType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            //'disabled' => $this->params['readonlyEncounter'],
            'attr' => array('readonly' => $this->params['readonlyEncounter']),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterreferringprovider__',
        ));

        $builder->add('attendingPhysicians', CollectionType::class, array(
            'entry_type' => EncounterAttendingPhysicianType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            //'disabled' => $this->params['readonlyEncounter'],
            'attr' => array('readonly' => $this->params['readonlyEncounter']),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterattendingphysician__',
        ));

        $builder->add('encounterInfoTypes', CollectionType::class, array(
            'entry_type' => EncounterInfoTypeType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterinfotypes__',
        ));

        //$this->params['readonlyEncounter'] = true;
        $builder->add('patientDob', DateType::class, array(
            'label' => "Date of Birth:",
            'widget' => 'single_text',
            'required' => false,
            //'disabled' => $this->params['readonlyEncounter'],
            //'mapped' => false,
            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patient-dob-date', 'readonly' => $this->params['readonlyEncounter']), //'style'=>'margin-top: 0;'
        ));

        if( $this->params['cycle'] != 'show' ) {
            //echo "show locationName <br>";
            $locationNameAttr = array('class' => 'combobox combobox-width ajax-combobox-locationName', 'type' => 'hidden');
            if( $this->params['readonlyEncounter'] ) {
                $locationNameAttr['readonly'] = true;
            }
            $builder->add('locationName', CustomSelectorType::class, array(
                'label' => "Location Name:",
                //'disabled' => $this->params['readonlyEncounter'],
                'mapped' => false,
                'required' => false,
                //'attr' => array('class' => 'combobox combobox-width ajax-combobox-locationName', 'type' => 'hidden', 'readonly' => $this->params['readonlyEncounter']),
                'attr' => $locationNameAttr,
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

        //TrackerType($this->params)
        $builder->add('tracker', CalllogTrackerType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Tracker',
            //'disabled' => $this->params['readonlyEncounter'],
            'attr' => array('readonly' => $this->params['readonlyEncounter']),
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

        $builder->add( 'encounterStatus', EntityType::class, array(
            'class' => 'OlegOrderformBundle:EncounterStatusList',
            //'choice_label' => 'name',
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
            'form_custom_value' => null,
            'form_custom_value_entity' => null
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_encountertype';
    }
}

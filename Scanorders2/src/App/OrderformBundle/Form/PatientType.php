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

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\TrackerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PatientType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        //echo "patient: type=".$this->params['type']."<br>";

        $flag = false;
        if( $this->params['type'] != 'One-Slide Scan Order' && ($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') ) {
            //$flag = true;
        }

        $readonly = false;
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure-patient' ) {
            $readonly = true;
        }

        $builder->add('mrn', CollectionType::class, array(
            'entry_type' => PatientMrnType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            //'disabled' => $readonly,
            'attr' => array('readonly'=>$readonly),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientmrn__',
        ));


        $builder->add('dob', CollectionType::class, array(
            'entry_type' => PatientDobType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            //'disabled' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientdob__',
        ));


        $attr = array('class'=>'textarea form-control patient-clinicalhistory-field');
        $gen_attr = array('label'=>'Clinical Summary:','class'=>'Oleg\OrderformBundle\Entity\PatientClinicalHistory','type'=>null);
        $builder->add('clinicalHistory', CollectionType::class, array(
            //GenericFieldType($this->params, null, $gen_attr, $attr),
            'entry_type' => GenericFieldType::class,
            'entry_options' => array(
                'data_class' => $gen_attr['class'],
                'form_custom_value' => $this->params,
                'form_custom_value_genAttr' => $gen_attr,
                'form_custom_value_attr' => $attr
            ),
            //'disabled' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical Summary:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientclinicalhistory__',
        ));

        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 2 ) {
            $builder->add('encounter', CollectionType::class, array(
                'entry_type' => EncounterType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounter__',
            ));
        }

        //////////// use these fields only for data reviewer and for view ///////////////
        if( 0 ) {

            $attr = array('class'=>'form-control patientname-field', 'disabled' => 'disabled');
            $gen_attr = array('label'=>'Name','class'=>'Oleg\OrderformBundle\Entity\PatientName','type'=>null);
            $builder->add('lastname', CollectionType::class, array(
                //GenericFieldType($this->params, null, $gen_attr, $attr),
                'entry_type' => GenericFieldType::class,
                'entry_options' => array(
                    'data_class' => $gen_attr['class'],
                    'form_custom_value' => $this->params,
                    'form_custom_value_genAttr' => $gen_attr,
                    'form_custom_value_attr' => $attr
                ),
                //'disabled' => $flag,
                'attr' => array('readonly'=>$flag),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Name:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientlastname__',
            ));

            $builder->add('sex', CollectionType::class, array(
                'entry_type' => PatientSexType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                //'disabled' => $flag,
                'attr' => array('readonly'=>$flag),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientsex__',
            ));

        }
        //////////// EOF use these fields only for data reviewer and for view ///////////////


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('race', CollectionType::class, array(
                'entry_type' => PatientRaceType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                //'disabled' => $flag,
                'attr' => array('readonly'=>$flag),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientrace__',
            ));

            $builder->add('deceased', CollectionType::class, array(
                'entry_type' => PatientDeceasedType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                //'disabled' => $flag,
                'attr' => array('readonly'=>$flag),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientdeceased__',
            ));

//            $builder->add('contactinfo', CollectionType::class, array(
//                'type' => new PatientContactinfoType($this->params, null),
//                'disabled' => $flag,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__patientcontactinfo__',
//            ));
            $builder->add('tracker', TrackerType::class, array(
                'form_custom_value' => $this->params,
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Tracker',
                'label' => false,
            ));

            $builder->add('type', CollectionType::class, array(
                'entry_type' => PatientTypeType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                //'disabled' => $flag,
                'attr' => array('readonly'=>$flag),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patienttype__',
            ));

        }

        if( array_key_exists('tracker',$this->params) && $this->params['tracker'] == 'tracker' ) {
            //echo "add tracker <br>";
            $builder->add('tracker', TrackerType::class, array(
                'form_custom_value' => $this->params,
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Tracker',
                'label' => false,
            ));
        }

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
                'prototype_name' => '__patientmessage__',
            ));
        }


        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure-patient' ) {
//            $builder->add('patientRecordStatus', 'entity', array(
//                'class' => 'OlegOrderformBundle:PatientRecordStatusList',
//                //'choice_label' => 'name',
//                'label' => 'Patient Record Status:',
//                'required' => false,
//                'multiple' => false,
//                'attr' => array('class' => 'combobox combobox-width'),
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist", "ASC")
//                        ->setParameters(array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//            ));

            $builder->add('lifeForm', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:LifeFormList',
                'choice_label' => 'name',
                'label' => "Life Form:",
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added'
                        ));
                },
            ));

            /////////////////////////////////////// patientRecordStatus ///////////////////////////////////////
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $patient = $event->getData();
                $form = $event->getForm();

                $statusParams = array(
                    'class' => 'OlegOrderformBundle:PatientRecordStatusList',
                    //'choice_label' => 'name',
                    'label' => 'Patient Record Status:',
                    'required' => false,
                    'multiple' => false,
                    //'empty_data' => $defaultStatus,
                    'attr' => array('class' => 'combobox combobox-width'),
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->where("list.type = :typedef OR list.type = :typeadd")
                            ->orderBy("list.orderinlist", "ASC")
                            ->setParameters(array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                            ));
                    },
                );

                //$defaultStatus = null;
                if( $patient ) {
                    $patientRecordStatus = $patient->getPatientRecordStatus();
                    if( !$patientRecordStatus ) {
                        $defaultStatus = $this->params['em']->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
                        if( $defaultStatus ) {
                            //echo "show default status=".$defaultStatus."<br>";
                            $statusParams['data'] = $defaultStatus;
                        }
                    }
                }

                $form->add('patientRecordStatus', EntityType::class, $statusParams);

            });
            /////////////////////////////////////// EOF patientRecordStatus ///////////////////////////////////////
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Patient',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_patienttype';
    }
}

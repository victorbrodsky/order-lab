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

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Form\FormNode\FormNodeType;
use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Oleg\UserdirectoryBundle\Form\FormNode\MessageCategoryFormNodeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;



//This form type is used strictly only for scan order: message (message) form has scan order
//This form includes patient hierarchy form.
//Originally it was made the way that message has scanorder.
//All other order's form should have aggregated message type form: order form has message form.
class CalllogMessageType extends AbstractType
{

    protected $entity;
    protected $params;
    
//    public function __construct( $type = null, $service = null, $entity = null )
    //params: type: single or clinical, educational, research
    //params: cycle: new, edit, show
    //params: service: pathology service
    //params: entity: entity itself
    public function formConstructor( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        if( !array_key_exists('type', $this->params) ) {
            $this->params['type'] = 'Unknown Order';
        }

        if( !array_key_exists('message.proxyuser.label', $this->params) ) {
            $this->params['message.proxyuser.label'] = 'Ordering Provider(s):';
        }

    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

//        echo "message params=";
        //echo "type=".$this->params['type']."<br>";
        //echo "cycle=".$this->params['cycle']."<br>";
//        echo "<br>";

        //$builder->add( 'oid' , 'hidden', array('attr'=>array('class'=>'message-id')) );

        if ($this->params['cycle'] == 'show') {
            $builder->add('id', null, array(
                'label' => 'Message ID:',
                //'disabled' => true,
                'required' => true,
                'attr' => array('class' => 'form-control', 'readonly'=>true)
            ));
        }

//        if( $this->params['cycle'] == 'edit' || $this->params['cycle'] == 'amend' ) {
//            $builder->add('id', null, array( //'hidden'
//                'label' => 'Message ID:',
//                'disabled' => true,
//                'required' => true,
//                'attr' => array('class' => 'form-control')
//            ));
//        }

        $patient = $this->entity->getPatient()->first();
        //echo "calllog patient id=".$patient->getId()."<br>";

        //echo "message type: show patient <br>";
        $builder->add('patient', CollectionType::class, array(
            'entry_type' => CalllogPatientType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
                'form_custom_value_entity' => $patient
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patient__',
        ));

        //if (count($this->entity->getPatient()) == 0) {
            $builder->add('encounter', CollectionType::class, array(
                'entry_type' => CalllogEncounterType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                    'form_custom_value_entity' => $this->entity
                ),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,//" ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounter__',
            ));
        //}

        if( $this->params['previousEncounters'] ) {
            if (0) {
                $builder->add('previousEncounters', EntityType::class, array(
                    'class' => 'OlegOrderformBundle:Encounter',
                    'label' => 'New or Previous Encounter:',
                    'required' => true,
                    'mapped' => false,
                    'multiple' => false,
                    //'data' => $this->params['previousEncounters'],
                    'choices' => $this->params['previousEncounters'],
                    'choice_label' => 'obtainEncounterNumber', //'obtainEncounterNumberOnlyAndDate', //'obtainEncounterNumber',
                    'attr' => array('class' => 'combobox combobox-previous-encounters', 'placeholder' => "New or Previous Encounters"),
                ));
//                $builder->add('previousEncounterId', HiddenType::class, array(
//                    'label' => false,
//                    'mapped' => false,
//                    'attr' => array('class' => 'message-previousEncounterId')
//                ));
            }
            if (0) {
                $builder->add('previousEncounters', ChoiceType::class, array(
                    //'class' => 'OlegOrderformBundle:Encounter',
                    'label' => 'New or Previous Encounter:',
                    'required' => true,
                    'mapped' => false,
                    'multiple' => false,
                    //'data' => $this->params['previousEncounters'],
                    'choices' => $this->params['previousEncounters'],
                    'choice_label' => 'obtainEncounterNumber', //'obtainEncounterNumberOnlyAndDate', //'obtainEncounterNumber',
                    'attr' => array('class' => 'combobox combobox-previous-encounters', 'placeholder' => "New or Previous Encounters"),
                ));
//                $builder->add('previousEncounterId', HiddenType::class, array(
//                    'label' => false,
//                    'mapped' => false,
//                    'attr' => array('class' => 'message-previousEncounterId')
//                ));
            }
        }

        $builder->add('previousEncounterId', HiddenType::class, array(
            'label' => false,
            'mapped' => false,
            'attr' => array('class' => 'message-previousEncounterId')
        ));

        /////////////////////////////////////// messageCategory ///////////////////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $message = $event->getData();
            $form = $event->getForm();
            $messageCategory = null;

            $label = null;
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "MessageCategory",
                'bundleName' => "OrderformBundle",
                'organizationalGroupType' => "MessageTypeClassifiers"
            );
            if ($message) {
                $messageCategory = $message->getMessageCategory();
                if ($messageCategory) {
                    $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels($messageCategory, $mapper);
                }
            }
            if (!$label) {
                $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels(null, $mapper);
            }

            if( $label ) {
                $label = $label . ":";
            }

            //echo "show defaultInstitution label=".$label."<br>";

            $form->add('messageCategory', CustomSelectorType::class, array(
                'label' => $label,
                'required' => false,
                //'read_only' => true, //this depracted and replaced by readonly in attr
                //'disabled' => true, //this disabled all children
                'attr' => array(
                    'readonly' => true,
                    'class' => 'ajax-combobox-compositetree combobox-without-add combobox-compositetree-postfix-level combobox-compositetree-read-only-exclusion ajax-combobox-messageCategory', //combobox-compositetree-readonly-parent
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'OrderformBundle',
                    'data-compositetree-classname' => 'MessageCategory',
                    'data-label-prefix' => '',
                    //'data-readonly-parent-level' => '2', //readonly all children from level 2 up (including this level)
                    'data-read-only-exclusion-after-level' => '2', //readonly will be disable for all levels after indicated level
                    'data-label-postfix-value-level' => '<span style="color:red">*</span>', //postfix after level
                    'data-label-postfix-level' => '3', //postfix after level "Message Group"
                ),
                'classtype' => 'messageCategory'
            ));

            //add form node fields
            //$form = $this->addFormNodes($form,$messageCategory,$this->params);

        });
        /////////////////////////////////////// EOF messageCategory ///////////////////////////////////////


        $builder->add('version', null, array(
            'label' => 'Message Version:',
            //'disabled' => true,
            'required' => true,
            'attr' => array('class' => 'form-control', 'readonly'=>true)
        ));

        $builder->add('messageTitle', null, array(
            'label' => 'Form Title:',
            //'disabled' => true,
            'required' => false,
            'attr' => array('class' => 'form-control', 'readonly'=>true)
        ));

        if( $this->entity->getMessageStatus()->getName()."" != "Draft" || ($this->params['cycle'] != "edit" && $this->params['cycle'] != "amend" ) ) {
            //echo "status=".$this->entity->getMessageStatus()->getName().""."<br>";
            //echo "show amendmentReason<br>";
            $builder->add('amendmentReason', ScanCustomSelectorType::class, array(
                'label' => 'Amendment Reason:',
                'required' => false,
                'attr' => array('class' => 'ajax-combobox-amendmentReason', 'type' => 'hidden'),
                'classtype' => 'amendmentReason'
            ));
        }

        if( $this->params['cycle'] != "new" ) {
            $builder->add('messageStatus', EntityType::class, array(
                'class' => 'OlegOrderformBundle:MessageStatusList',
                //'choice_label' => 'name',
                'label' => 'Message Status:',
                'required' => false,
                //'disabled' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        ////////////////////////// Specific Orders //////////////////////////
        $builder->add('calllogEntryMessage', CalllogEntryMessageType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CalllogEntryMessage',
            'form_custom_value' => $this->params,
            'form_custom_value_entity' => null,
            'label' => false,
        ));
        ////////////////////////// EOF Specific Orders //////////////////////////


//        $builder->add('addPatientToList', 'checkbox', array(
//            'label' => 'Add patient to the list:',
//            'mapped' => false,
//            'required' => false,
//            'attr' => array('class' => 'form-control')
//        ));
//
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $message = $event->getData();
//            $form = $event->getForm();
//
//            $label = 'List Title:';
//
//            $patientLists = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->findAll();
//            if( count($patientLists) > 0 ) {
//                $patientListId = $patientLists[0]->getId();
//            } else {
//                $patientListId = null;
//            }
//
//            if( $this->params['cycle'] != "new" && $message ) {
//                $calllogEntryMessage = $message->getCalllogEntryMessage();
//                if( $message->getId() && $message->getCalllogEntryMessage() ) {
//                    $patientListHierarchyNode = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->findBy(array(
//                        'entityNamespace' => $calllogEntryMessage->getEntityNamespace(),
//                        'entityName' => $calllogEntryMessage->getEntityName(),
//                        'entityId' => $calllogEntryMessage->getEntityId(),
//                    ));
//                    if( $patientListHierarchyNode ) {
//                        $patientListId = $patientListHierarchyNode->getId();
//                    }
//                }
//            }
//
//            $form->add('patientListTitle', 'employees_custom_selector', array(
//                'label' => $label,
//                'mapped' => false,
//                'required' => true,
//                'data' => $patientListId,
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree show-as-single-node ajax-combobox-patientListTitle', //show-as-single-node data-compositetree-exclusion-all-others
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'OrderformBundle',
//                    'data-compositetree-classname' => 'PatientListHierarchy',
//                    'data-label-prefix' => '',
//                    'data-compositetree-types' => 'default,user-added',
//                ),
//                'classtype' => 'patientListTitle'
//            ));
//        });


        //Institutional PHI Scope
//        if( 0 ) {
//            if (array_key_exists('institutions', $this->params)) {
//                $institutions = $this->params['institutions'];
//            } else {
//                $institutions = null;
//            }
//            //foreach( $institutions as $inst ) {
//            //    echo "form inst=".$inst."<br>";
//            //}
//            $builder->add('institution', 'entity', array(
//                'label' => 'Order data visible to members of (Institutional PHI Scope):',
//                'choice_label' => 'getNodeNameWithRoot',
//                'required' => true,
//                'multiple' => false,
//                'empty_value' => false,
//                'class' => 'OlegUserdirectoryBundle:Institution',
//                'choices' => $institutions,
//                'attr' => array('class' => 'combobox combobox-width combobox-institution')
//            ));
//        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Message',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_calllogformbundle_messagetype';
    }




    //BELOW NOT USED
    public function addFormNodes( $form, $formHolder, $params ) {

        if( !$formHolder ) {
            return $form;
        }

        $rootFormNode = $formHolder->getFormNode();
        if( !$rootFormNode ) {
            return $form;
        }

        $form = $this->addFormNodeRecursively($form,$rootFormNode,$params);

        return $form;
    }


    public function addFormNodeRecursively( $form, $formNode, $params ) {

        //echo "formNode=".$formNode."<br>";
        $children = $formNode->getChildren();
        if( $children ) {

            foreach( $children as $childFormNode ) {
                $this->addFormNodeByType($form,$childFormNode,$params);
                $this->addFormNodeRecursively($form,$childFormNode,$params);
            }

        } else {
            $this->addFormNodeByType($form,$formNode,$params);
        }

    }

    public function addFormNodeByType( $form, $formNode, $params ) {

        $formNodeType = $formNode->getObjectType()."";
        //echo "formNodeType=".$formNodeType."<br>";

        if( $formNodeType == "Form" ) {
            echo "added Form <br>";
            $form->add('formFormNode',null,array(
                'label' => $formNode->getName()."",
                'mapped' => false
            ));
        }

        if( $formNodeType == "Form Section" ) {
            echo "added Section <br>";
            $form->add('sectionFormNode',null,array(
                'label' => $formNode->getName()."",
                'mapped' => false
            ));
        }

        if( $formNodeType == "Form Field - Free Text" ) {
            echo "added text <br>";
            $form->add('formNode', TextType::class, array(
                'label' => $formNode->getName()."",
                'mapped' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
        }

    }

}

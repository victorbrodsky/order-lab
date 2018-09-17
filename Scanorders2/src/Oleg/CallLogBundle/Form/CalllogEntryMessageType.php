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
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Form\FormNode\FormNodeType;
use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Oleg\UserdirectoryBundle\Form\FormNode\MessageCategoryFormNodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;


class CalllogEntryMessageType extends AbstractType
{

    protected $entity;
    protected $params;

    public function formConstructor( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $builder->add('addPatientToList', CheckboxType::class, array(
            'label' => 'Add patient to the list:',
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $message = $event->getData();
            $form = $event->getForm();

            $label = 'List Title:';

//            $form->add('patientLists', 'employees_custom_selector', array(
//                'label' => $label,
//                'required' => true,
//                //'data' => $patientListId,
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree show-as-single-node ajax-combobox-patientList', //show-as-single-node data-compositetree-exclusion-all-others
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'OrderformBundle',
//                    'data-compositetree-classname' => 'PatientListHierarchy',
//                    'data-label-prefix' => '',
//                    'data-compositetree-types' => 'default,user-added',
//                ),
//                'classtype' => 'patientList'
//            ));

            $form->add('patientLists', CustomSelectorType::class, array(
                'label' => $label,
                'required' => false,
                'attr' => array('class' => 'ajax-combobox-patientlists', 'type' => 'hidden'),
                //'multiple' => true,
                'classtype' => 'patientLists'
            ));

        });

        //POST_SUBMIT hierarchy tree processing for newly added element
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData(); //CalllogEntryMessage

            //$event->setData($data);

            $patientLists = $data->getPatientLists();
            $this->processPatientList($patientLists);

            //exit();
        });


        $builder->add('entryTags', null, array(
            //'class' => 'OlegOrderformBundle:CalllogEntryTagsList',
            'label' => "Call Log Entry Tag(s):",
            'required' => false,
            'multiple' => true,
            //'data' => $this->params['mrntype'],
            'attr' => array('class' => 'combobox', 'placeholder' => "Call Log Entry Tag(s)"),
        ));

        $builder->add('timeSpentMinutes', TextType::class, array(
            'label' => "Amount of Time Spent in Minutes:",
            'required' => false,
            //'data' => $this->params['mrntype'],
            'attr' => array('class' => 'form-control digit-mask-seven'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CalllogEntryMessage',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_calllogformbundle_calllogentrymessagetype';
    }


    public function processPatientList( $patientLists ) {

        $calllogUtil = $this->params['container']->get('calllog_util');
        $defaultPatientLists = $calllogUtil->getDefaultPatientLists();

        //get level, org group, parent from the first element
        $level = null;
        $orgGroupType = null;
        $parent = null;
        foreach( $defaultPatientLists as $patientList ) {
            if( $patientList && $patientList->getLevel() && $patientList->getParent() ) {
                $level = $patientList->getLevel();
                $parent = $patientList->getParent();
                $orgGroupType = $patientList->getOrganizationalGroupType();
                break;
            }
        }

        //echo "level=$level; orgGroupType=$orgGroupType; parent=$parent<br>";
        //if( $level || $orgGroupType || $parent ) { //if these parameters are not set, then still create a new node in the PatientList hierarchy. Then manually set the tree.

        foreach( $patientLists as $patientList ) {
            if ($patientList) {
                if ($level) {
                    $patientList->setLevel($level);
                }
                if ($orgGroupType) {
                    $patientList->setOrganizationalGroupType($orgGroupType);
                }
                if ($parent) {
                    $parent->addChild($patientList);
                }

                $this->params['em']->persist($patientList);
                //$this->params['em']->flush($patientList);
            }
        }//foreach

        //}//if

        return $patientLists;
    }





    //BELOW NOT USED
//    public function addFormNodes( $form, $formHolder, $params ) {
//
//        if( !$formHolder ) {
//            return $form;
//        }
//
//        $rootFormNode = $formHolder->getFormNode();
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
//        //echo "formNode=".$formNode."<br>";
//        $children = $formNode->getChildren();
//        if( $children ) {
//
//            foreach( $children as $childFormNode ) {
//                $this->addFormNodeByType($form,$childFormNode,$params);
//                $this->addFormNodeRecursively($form,$childFormNode,$params);
//            }
//
//        } else {
//            $this->addFormNodeByType($form,$formNode,$params);
//        }
//
//    }
//
//    public function addFormNodeByType( $form, $formNode, $params ) {
//
//        $formNodeType = $formNode->getObjectType()."";
//        //echo "formNodeType=".$formNodeType."<br>";
//
//        if( $formNodeType == "Form" ) {
//            echo "added Form <br>";
//            $form->add('formFormNode',null,array(
//                'label' => $formNode->getName()."",
//                'mapped' => false
//            ));
//        }
//
//        if( $formNodeType == "Form Section" ) {
//            echo "added Section <br>";
//            $form->add('sectionFormNode',null,array(
//                'label' => $formNode->getName()."",
//                'mapped' => false
//            ));
//        }
//
//        if( $formNodeType == "Form Field - Free Text" ) {
//            echo "added text <br>";
//            $form->add('formNode','text',array(
//                'label' => $formNode->getName()."",
//                'mapped' => false,
//                'attr' => array('class' => 'form-control textarea')
//            ));
//        }
//
//    }
//
//    //DO NOT USED
//    public function processPatientList_PRE_SUBMIT( $patientListsArr ) {
//
//        $newPatientListsIds = array();
//        $newPatientListsStr = array();
//        foreach( $patientListsArr as $patientList ) {
//            //echo "ID=".$patientList->getId().": patientList=".$patientList."<br>";
//            if (strval($patientList) != strval(intval($patientList))) {
//                //echo "string <br>";
//                $newPatientListsStr[] = $patientList;
//            } else {
//                //echo "integer <br>";
//                $newPatientListsIds[] = $patientList;
//            }
//        }
//
//        //get level, org group, parent from the first element
//        $level = null;
//        $orgGroupType = null;
//        $parent = null;
//        if (count($newPatientListsIds) > 0) {
//            $firstPatientListId = $newPatientListsIds[0];
//            $firstPatientList = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->find($firstPatientListId);
//            if ($firstPatientList) {
//                $level = $firstPatientList->getLevel();
//                $orgGroupType = $firstPatientList->getOrganizationalGroupType();
//                $parent = $firstPatientList->getParent();
//            }
//        }
//
//        if ($level || $orgGroupType || $parent) {
//            $userSecUtil = $this->params['container']->get('user_security_utility');
//            foreach ($newPatientListsStr as $newPatientListStr) {
//                $newPatientList = $userSecUtil->getObjectByNameTransformer($this->params['user'], $newPatientListStr, 'OrderformBundle', 'PatientListHierarchy');
//                if ($newPatientList) {
//                    if ($level) {
//                        $newPatientList->setLevel($level);
//                    }
//                    if ($orgGroupType) {
//                        $newPatientList->setOrganizationalGroupType($orgGroupType);
//                    }
//                    if ($parent) {
//                        $parent->addChild($newPatientList);
//                    }
//
//                    $this->params['em']->persist($newPatientList);
//                    $this->params['em']->flush($newPatientList);
//
//                    if ($newPatientList->getId()) {
//                        $newPatientListsIds[] = $newPatientList->getId();
//                    }
//                }
//            }
//        }
//
//        return $newPatientListsIds;
//    }

}

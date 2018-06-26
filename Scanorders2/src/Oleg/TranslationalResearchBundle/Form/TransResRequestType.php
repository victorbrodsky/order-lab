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

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransResRequestType extends AbstractType
{

    protected $transresRequest;
    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;

        $this->transresRequest = $params['transresRequest'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['cycle'] != 'new' ) {

            if( $this->params['admin'] ) {
                $disabled = false;
            } else {
                $disabled = true;
            }

            $builder->add('billingState', ChoiceType::class, array(
                'label' => 'Billing Status:',
                'required' => false,
                'disabled' => $disabled,
                'choices' => $this->params['billingStateChoiceArr'],
                'attr' => array('class' => 'combobox'),
            ));

            $builder->add('progressState', ChoiceType::class, array(
                'label' => 'Progress Status:',
                'required' => false,
                'disabled' => $disabled,
                'choices' => $this->params['progressStateChoiceArr'],
                'attr' => array('class' => 'combobox'),
            ));

//            $builder->add('approvalDate', DateType::class, array(
//                'widget' => 'single_text',
//                'label' => "Approval Date:",
//                'disabled' => true,
//                'format' => 'MM/dd/yyyy',
//                'attr' => array('class' => 'datepicker form-control'),
//                'required' => false,
//            ));
        }

        if(
            $this->params['cycle'] != 'new' &&
            $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') &&
            $this->transresRequest->getCreateDate()
        ) {
            $builder->add('createDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Create Date:",
                'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control', 'readonly' => true),
                'required' => false,
            ));

            $builder->add('submitter', null, array(
                'label' => "Created By:",
                'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly' => true)
            ));
        }

        if( $this->params['availableProjects'] && count($this->params['availableProjects']) > 0 ) {
            //echo "2availableProjects count=".count($this->params['availableProjects'])."<br>";
            $builder->add('project', EntityType::class, array(
                'class' => 'OlegTranslationalResearchBundle:Project',
                'choice_label' => 'getProjectInfoNameChoice',
                'choices' => $this->params['availableProjects'],
                'label' => 'Project:',
                //'disabled' => ($this->params['admin'] ? false : true),
                //'disabled' => true,
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width tarnsresrequest-project')
            ));
        }

        if(0) {
            if ($this->params['cycle'] != 'show') {
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

                    if ($label) {
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
                            'data-label-postfix-level' => '4', //postfix after level "Issue"
                        ),
                        'classtype' => 'messageCategory'
                    ));


                    //add form node fields
                    //$form = $this->addFormNodes($form,$messageCategory,$this->params);

                });
                /////////////////////////////////////// EOF messageCategory ///////////////////////////////////////
            }//if
        }


        //////////////// fields /////////////////////////
        $builder->add('fundedAccountNumber',null, array(
            'label' => $this->params['fundedNumberLabel'],
            'required' => false,
            'attr' => array('class' => 'form-control tarnsresrequest-fundedAccountNumber'),
        ));

//        $builder->add('supportStartDate', DateType::class, array(
//            'widget' => 'single_text',
//            'label' => "Support Start Date:",
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control'),
//            'required' => false,
//        ));

        //Request's "Support End Date" is pre-populated by Project's "IRB Expiration Date" and it is not editable, but visible on the view page only.
        if( $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
            ($this->params['cycle'] != 'new' && $this->params['cycle'] != 'edit')
        ) {
//            if( $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                $disabledSupportEndDate = false;
//            } else {
//                $disabledSupportEndDate = true;
//            }
            $builder->add('supportEndDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Support End Date:",
                'format' => 'MM/dd/yyyy',
                //'disabled' => $disabledSupportEndDate,
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }

        $builder->add('contact', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Billing Contact:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        //availableProjects
        if( $this->params['availableProjects'] === null ) {
            $disabledPi = true;
        } else {
            $disabledPi = false;
        }
        $builder->add('principalInvestigators', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Principal Investigator(s) for the project:",
            'required' => false,
            'disabled' => $disabledPi,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('products', CollectionType::class, array(
            'entry_type' => ProductType::class,
            'entry_options' => array(
                //'data_class' => 'Oleg\TranslationalResearchBundle\Entity\Product',
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__products__',
        ));

        $builder->add('datalocker',HiddenType::class, array(
            "mapped" => false,
            'attr' => array('class' => 'transres-datalocker-field')
        ));

        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Document(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('comment', null, array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        //////////////// EOF fields /////////////////////////


        if( $this->params['saveAsDraft'] === true ) {
            $builder->add('saveAsDraft', SubmitType::class, array(
                'label' => 'Save Request as Draft',
                'attr' => array('class' => 'btn btn-warning', 'onclick'=>'transresValidateHandsonTable();') //'onclick'=>'transresValidateHandsonTable();'
            ));
        }
        if( $this->params['saveAsUpdate'] === true ) {
            $builder->add('saveAsUpdate', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class'=>'btn btn-warning', 'onclick'=>'transresValidateHandsonTable();') //'onclick'=>'transresValidateHandsonTable();'
            ));
        }
        if( $this->params['saveAsComplete'] === true ) {
            $builder->add('saveAsComplete', SubmitType::class, array(
                'label' => 'Complete Submission',
                'attr' => array('class'=>'btn btn-warning', 'onclick'=>'return transresValidateRequest();') //'general-data-confirm'=>"Are you sure you want to submit this Working Request?"
            ));
            //, 'onsubmit'=>'transresValidateRequest();'
            //'onclick'=>'transresValidateHandsonTable();',
        }
        if( $this->params['updateRequest'] === true ) {
            $builder->add('updateRequest', SubmitType::class, array(
                'label' => 'Update Request',
                'attr' => array('class'=>'btn btn-warning', 'onclick'=>'transresValidateHandsonTable();')
            ));
        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\TransResRequest',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_request';
    }


}

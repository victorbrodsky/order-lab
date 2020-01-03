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

namespace App\UserdirectoryBundle\Form;


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\UserdirectoryBundle\Entity\Training;

class TrainingType extends AbstractType
{

    protected $params;
    protected $entity;

    protected $hasRoleSimpleView;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        $this->hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $this->hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $builder->add('id',HiddenType::class,array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));

        //status
        $baseUserAttr = new Training();
        $statusAttr = array('class' => 'combobox combobox-width');
        if( $this->params['disabled'] ) {
            $statusAttr['readonly'] = true;
        }
        $builder->add('status', ChoiceType::class, array(   //flipped
            //'disabled' => ($this->params['disabled'] ? true : false),
//            'choices' => array(
//                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
//                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
//            ),
            'choices' => array(
                $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED) => $baseUserAttr::STATUS_UNVERIFIED,
                $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED) => $baseUserAttr::STATUS_VERIFIED
            ),
            //'choices_as_values' => true,
            'invalid_message' => 'invalid value: training status',
            'label' => "Status:",
            'required' => true,
            'attr' => $statusAttr,  //array('class' => 'combobox combobox-width'),
        ));


        $builder->add('startDate', DateType::class, array(
            'label' => 'Start Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        //If value ="Graduated" display the title of the field "Completion Date" as "Graduation Date" and hide this field and title
        if( $this->params['cycle'] == 'show' ) {

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $training = $event->getData();
                $form = $event->getForm();

                $completionReason = NULL;

                if( $training ) {
                    $completionReason = $training->getCompletionReason();
                }

                if( $completionReason == "Graduated" ) {

                    if( !$this->hasRoleSimpleView ) {
                        $form->add('completionDate', DateType::class, array(
                            'label' => 'Graduation Date:',
                            'widget' => 'single_text',
                            'required' => false,
                            'format' => 'MM/dd/yyyy',
                            'attr' => array('class' => 'datepicker form-control'),
                        ));
                    }

                } else {

                    if( !$this->hasRoleSimpleView ) {
                        $form->add('completionDate', DateType::class, array(
                            'label' => 'Completion Date:',
                            'widget' => 'single_text',
                            'required' => false,
                            'format' => 'MM/dd/yyyy',
                            'attr' => array('class' => 'datepicker form-control'),
                        ));

                        $form->add('completionReason', null, array(
                            'label' => 'Completion Reason:',
                            'attr' => array('class' => 'combobox combobox-width')
                        ));
                    }

                }

            });

        } else {

            if( !$this->hasRoleSimpleView ) {
                $builder->add('completionDate', DateType::class, array(
                    'label' => 'Completion Date:',
                    'widget' => 'single_text',
                    'required' => false,
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'datepicker form-control'),
                ));

                $builder->add('completionReason', null, array(
                    'label' => 'Completion Reason:',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
            }

        }

        $builder->add('degree', null, array(
            'label' => 'Degree:',
            'attr' => array('class'=>'combobox combobox-width ajax-combobox-trainingdegree')
        ));

        $builder->add('appendDegreeToName', CheckboxType::class, array(
            'label'     => 'Append degree to name:',
            'attr' => array('class'=>'training-field-appenddegreetoname'),
            'required'  => false,
        ));

        if( !$this->hasRoleSimpleView ) {
            $builder->add('majors', CustomSelectorType::class, array(
                'label' => 'Major:',
                'attr' => array('class' => 'ajax-combobox-trainingmajors', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'trainingmajors'
            ));

            $builder->add('minors', CustomSelectorType::class, array(
                'label' => 'Minor:',
                'attr' => array('class' => 'ajax-combobox-trainingminors', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'trainingminors'
            ));

            $builder->add('honors', CustomSelectorType::class, array(
                'label' => 'Honors:',
                'attr' => array('class' => 'ajax-combobox-traininghonors', 'type' => 'hidden'),
                'required' => false,
                //'multiple' => true,
                'classtype' => 'traininghonors'
            ));

            $builder->add('institution', CustomSelectorType::class, array(
                'label' => 'Educational Institution:',
                'attr' => array('class' => 'ajax-combobox-traininginstitution', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'traininginstitution'
            ));

            $builder->add('fellowshipTitle', CustomSelectorType::class, array(
                'label' => 'Professional Fellowship Title:',
                'attr' => array('class' => 'ajax-combobox-trainingfellowshiptitle', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'trainingfellowshiptitle'
            ));
            $builder->add('appendFellowshipTitleToName', CheckboxType::class, array(
                'label'     => 'Append professional fellowship to name:',
                'required'  => false,
            ));
        }

        //residencySpecialty
        $builder->add('residencySpecialty', CustomSelectorType::class, array(
            'label' => 'Residency Specialty:',
            'attr' => array('class' => 'ajax-combobox-residencyspecialty', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'residencyspecialty'
        ));
        //fellowshipSubspecialty
        $tooltip = "To select a Fellowship, please choose the the Residency Specialty category first.";
        $builder->add('fellowshipSubspecialty', CustomSelectorType::class, array(
            'label' => "Fellowship Subspecialty:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-fellowshipsubspecialty', 'type' => 'hidden', 'title'=>$tooltip), //'data-toggle'=>'tooltip'
            'classtype' => 'fellowshipsubspecialty'
        ));






    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Training',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_training';
    }
}

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

//            $builder->add('state',ChoiceType::class, array(
//                'label' => 'State:',
//                'required' => false,
//                'disabled' => $this->params['disabledState'],
//                'choices' => $this->params['stateChoiceArr'],
//                'attr' => array('class' => 'combobox'),
//            ));

//            $builder->add('approvalDate', DateType::class, array(
//                'widget' => 'single_text',
//                'label' => "Approval Date:",
//                'disabled' => true,
//                'format' => 'MM/dd/yyyy',
//                'attr' => array('class' => 'datepicker form-control'),
//                'required' => false,
//            ));
        }

        if( $this->transresRequest->getCreateDate() ) {
            $builder->add('createDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Create Date:",
                'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control', 'readonly'=>true),
                'required' => false,
            ));

            $builder->add('submitter', null, array(
                'label' => "Created By:",
                'disabled' => true,
                'attr' => array('class'=>'combobox combobox-width', 'readonly'=>true)
            ));
        }

        if( $this->params['cycle'] != 'show' ) {
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

        if( $this->params['saveAsDraft'] === true ) {
            $builder->add('saveAsDraft', SubmitType::class, array(
                'label' => 'Save Request as Draft',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
        if( $this->params['saveAsComplete'] === true ) {
            $builder->add('saveAsComplete', SubmitType::class, array(
                'label' => 'Complete Submission',
                'attr' => array('class'=>'btn btn-warning')
            ));
        }
        if( $this->params['updateRequest'] === true ) {
            $builder->add('updateRequest', SubmitType::class, array(
                'label' => 'Update Request',
                'attr' => array('class'=>'btn btn-warning')
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

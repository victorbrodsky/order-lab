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

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CalllogSiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //keytypemrn
        $builder->add('keytypemrn', EntityType::class, array(
            'class' => 'OlegOrderformBundle:MrnType',
            'choice_label' => 'name',
            'label' => 'MRN Type:',
            'required'=> false,
            //'multiple' => false,
            'attr' => array('class'=>'combobox'),
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

        $builder->add('city', null, array(
            'label' => 'City:',
            'required' => false,
            'attr' => array('class' => 'combobox'),
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

        //state
//        $stateArray = array(
//            'class' => 'OlegUserdirectoryBundle:States',
//            //'choice_label' => 'name',
//            'label'=>'State:',
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width geo-field-state'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        );
//        if( $this->params['cycle'] == 'new_standalone' ) {
//            $stateArray['data'] = $this->params['em']->getRepository('OlegUserdirectoryBundle:States')->findOneByName('New York');
//        }
//        $builder->add( 'state', EntityType::class, $stateArray);
        $builder->add('state', null, array(
            'label' => "State:",
            'required' => false,
            'attr' => array('class' => 'combobox'),
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

        //country
//        $countryArray = array(
//            'class' => 'OlegUserdirectoryBundle:Countries',
//            'choice_label' => 'name',
//            'label'=>'Country:',
//            'required'=> false,
//            'multiple' => false,
//            //'preferred_choices' => $preferredCountries,
//            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        );
//        $countryArray['preferred_choices'] = $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findByName(array('United States'));
//        if( $this->params['cycle'] == 'new_standalone' ) {
//            $countryArray['data'] = $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName('United States');
//        }
//        $builder->add( 'country', EntityType::class, $countryArray);
        $builder->add('country', null, array(
            'label' => "Country:",
            'required' => false,
            'attr' => array('class' => 'combobox'),
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

        //TODO:
//        $builder->add('useCache',null,array(
//            'label'=>'Use cached values to display entry content preview in lists:',
//            'attr' => array('class'=>'form-control')
//        ));
        $builder->add('useCache',CheckboxType::class, array(
            'label' => 'Use cached values to display entry content preview in lists:',
            //'mapped' => false,
            'required' => false,
            //'data' => true,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('county', null, array(
            'label' => 'County:',
            'attr' => array('class' => 'form-control geo-field-county')
        ));

        $builder->add('zip',null,array(
            'label'=>'Zip Code:',
            'attr' => array('class'=>'form-control geo-field-zip')
        ));

        $tzUtil = new TimeZoneUtil();
        $builder->add('timezone', ChoiceType::class, array(
            'label' => false,
            'choices' => $tzUtil->tz_list(),
            'choices_as_values' => true,
            'required' => true,
            //'data' => $this->params['timezoneDefault'],
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

        /////////////////////////////////////// messageCategory ///////////////////////////////////////
        if(0) {
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
                    //'disabled' => false, //this disabled all children
                    'attr' => array(
                        //'readonly' => true,
                        //'class' => 'ajax-combobox-compositetree combobox-without-add combobox-compositetree-postfix-level combobox-compositetree-read-only-exclusion ajax-combobox-messageCategory', //combobox-compositetree-readonly-parent
                        'class' => 'ajax-combobox-compositetree combobox-without-add', //combobox-compositetree-readonly-parent
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'OrderformBundle',
                        'data-compositetree-classname' => 'MessageCategory',
                        //'data-label-prefix' => 'Default ',
                        //'data-readonly-parent-level' => '2', //readonly all children from level 2 up (including this level)
                        //'data-read-only-exclusion-after-level' => '2', //readonly will be disable for all levels after indicated level
                        //'data-label-postfix-value-level' => '<span style="color:red">*</span>', //postfix after level
                        //'data-label-postfix-level' => '4', //postfix after level "Issue"
                    ),
                    'classtype' => 'messageCategory'
                ));


                //add form node fields
                //$form = $this->addFormNodes($form,$messageCategory,$this->params);

            });
            /////////////////////////////////////// EOF messageCategory ///////////////////////////////////////
        } else {
            $builder->add('messageCategory', null, array(
                'label' => 'Message Group:',
                'required' => false,
                'choice_label' => 'getTreeNameReverse',
                'attr' => array('class' => 'combobox'),
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

        if(0) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $message = $event->getData();
                $form = $event->getForm();
                $messageCategory = null;

                $label = null;
                $mapper = array(
                    'prefix' => "Oleg",
                    'className' => "PatientListHierarchy",
                    'bundleName' => "OrderformBundle",
                    'organizationalGroupType' => "MessageTypeClassifiers"
                );
                if ($message) {
                    $messageCategory = $message->getMessageCategory();
                    if ($messageCategory) {
                        $label = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->getLevelLabels($messageCategory, $mapper);
                    }
                }
                if (!$label) {
                    $label = $this->params['em']->getRepository('OlegOrderformBundle:PatientListHierarchy')->getLevelLabels(null, $mapper);
                }

                if ($label) {
                    $label = $label . ":";
                }

                //echo "show defaultInstitution label=".$label."<br>";

                $form->add('patientList', CustomSelectorType::class, array(
                    'label' => $label,
                    'required' => false,
                    'attr' => array(
                        //'class' => 'ajax-combobox-compositetree combobox-without-add combobox-compositetree-postfix-level combobox-compositetree-read-only-exclusion ajax-combobox-messageCategory', //combobox-compositetree-readonly-parent
                        'class' => 'ajax-combobox-compositetree combobox-without-add', //combobox-compositetree-readonly-parent
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'OrderformBundle',
                        'data-compositetree-classname' => 'PatientListHierarchy',
                    ),
                    'classtype' => 'patientList'
                ));

            });
        } else {
            $builder->add('patientList', null, array(
                'label' => "Patient List:",
                'choice_label' => 'getTreeNameReverse',//'getTreeName',
                'required' => false,
                'attr' => array('class' => 'combobox'),
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

        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\CallLogBundle\Entity\CalllogSiteParameter',
            'form_custom_value' => null,
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_calllogbundle_calllogsiteparameter';
    }
}

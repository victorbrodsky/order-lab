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

namespace App\VacReqBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\VacReqBundle\Form\VacReqRequestBusinessType;


class VacReqFloatingDayType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //enable status radio only for admin or for reviewer
        $readOnly = true;
        if( $this->params['roleAdmin'] ||
            ($this->params['review'] == true && $this->params['roleCarryOverApprover']) ) {
            $readOnly = false;
        }

//        $builder->add('status', ChoiceType::class, array( //flipped
//            //'disabled' => $readOnly,    //($this->params['roleAdmin'] ? false : true),
////                'choices' => array(
////                    //'pending' => 'Pending',
////                    'approved' => 'Approved',
////                    'rejected' => 'Rejected'
////                ),
//            'choices' => array(
//                //'pending' => 'Pending',
//                'Approved' => 'approved',
//                'Rejected' => 'rejected'
//            ),
//            //'choices_as_values' => true,
//            'label' => false,   //"Status:",
//            'expanded' => true,
//            'multiple' => false,
//            'required' => true,
//            //'data' => 'pending',
//            'attr' => array('class' => 'horizontal_type_wide', 'readonly'=>$readOnly), //horizontal_type
//        ));


        if( $this->params['cycle'] == 'show' ) {
            //approver
            $builder->add('approver', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Approver:",
                'required' => false,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true),
            ));
        }

        if( $this->params['cycle'] != 'show' && !$this->params['review'] ) {
            $userAttr = array('class' => 'combobox combobox-width');
            if( $this->params['review'] ) {
                $userAttr['readonly'] = true;
            }
            $builder->add('submitter', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Request Submitter:",
                'required' => true,
                'multiple' => false,
                //'choice_label' => 'name',
                'attr' => $userAttr,    //array('class' => 'combobox combobox-width'),
                //'disabled' => $readOnly,   //($this->params['review'] ? true : false),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ));

            $builder->add('user', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Person Away:",
                //'required' => true,
                'multiple' => false,
                //'choice_label' => 'name',
                'attr' => $userAttr,    //array('class' => 'combobox combobox-width'),
                //'disabled' => $readOnly,   //($this->params['review'] ? true : false),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ));
        }

        $builder->add('phone', null, array(
            'label' => "Phone Number for the person away:",
            'data' => "+1 123 456-7890",
            'attr' => array('class' => 'form-control vacreq-phone'),
            //'disabled' => ($this->params['review'] ? true : false)
        ));

        //floatingDayType
//        $builder->add('floatingDayType', ChoiceType::class, array(
//            'label' => "Floating Day:",
//            'choices' => array("Juneteenth"=>"Juneteenth"),
//            'required' => false,
//            'data' => "Juneteenth",
//            'mapped' => false,
//            'attr' => array('class' => 'combobox'),
//            //'disabled' => ($this->params['review'] ? true : false)
//        ));
        $builder->add('floatingDayType', EntityType::class, array(
            'class' => 'AppVacReqBundle:VacReqFloatingTypeList',
            'label' => "Floating Day:",
            'required' => false,
            'multiple' => false,
            'data' => $this->params['defaultFloatingDayType'],
            'mapped' => false,
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
        
        //worked
        $builder->add('worked', CheckboxType::class, array(
            'label' => 'I have worked or plan to work on',
            'mapped' => false,
            'required' => false,
            'attr' => array('class' => 'floatingday-worked'),
        ));

        //organizationalInstitutions
        $requiredInst = false;
        if( count($this->params['organizationalInstitutions']) == 1 ) {
            //echo "set org inst <br>";
            $requiredInst = true;
        }

        //floatingDate
        $builder->add('floatingDate', DateType::class, array(
            'label' => "The floating day I am requesting for this fiscal year is:",
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'mapped' => false,
            'attr' => array('class' => 'form-control datetimepicker floatingDate', 'placeholder' => 'Floating Date', 'title'=>'The floating day I am requesting', 'data-toggle'=>'tooltip')
        ));

//        echo "organizationalInstitutions count=".count($this->params['organizationalInstitutions'])."<br>";
//        foreach( $this->params['organizationalInstitutions'] as $tentativeInstitution ) {
//            echo "tentativeInstitution=".$tentativeInstitution."<br>";
//        }

        //$requiredInst = true;
        $institutionAttr = array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group');
        if( $this->params['review'] ) {
            $institutionAttr['readonly'] = true;
        }
        $builder->add('institution', ChoiceType::class, array( //flipped
            'label' => "Organizational Group:",
            'required' => $requiredInst,
            'attr' => $institutionAttr, //array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group'),
            'choices' => $this->params['organizationalInstitutions'],
            //'choices_as_values' => true,
            //'disabled' => ($this->params['review'] ? true : false)
        ));
        $builder->get('institution')
            ->addModelTransformer(new CallbackTransformer(
                //original from DB to form: institutionObject to institutionId
                    function($originalInstitution) {
                        //echo "originalInstitution=".$originalInstitution."<br>";
                        if( is_object($originalInstitution) && $originalInstitution->getId() ) { //object
                            return $originalInstitution->getId();
                        }
                        return $originalInstitution; //id
                    },
                    //reverse from form to DB: institutionId to institutionObject
                    function($submittedInstitutionObject) {
                        //echo "submittedInstitutionObject=".$submittedInstitutionObject."<br>";
                        if( $submittedInstitutionObject ) { //id
                            $institutionObject = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                            return $institutionObject;
                        }
                        return null;
                    }
                )
            );

        //tentativeInstitution
        if( $this->params['tentativeInstitutions'] && count($this->params['tentativeInstitutions']) > 0 ) {

            //$readonlyTentativeInstitution = ($this->params['review'] ? true : false);

            $requiredTentInst = false;
            if (count($this->params['tentativeInstitutions']) == 1) {
                //echo "set org inst <br>";
                $requiredTentInst = true;
            } else {
                //$readonlyTentativeInstitution = false;
            }
            //$requiredTentInst = true;
            $tentativeInstitutionAttr = array('class' => 'combobox combobox-width vacreq-tentativeInstitution', 'placeholder' => 'Organizational Group');
            if( $this->params['review'] ) {
                $tentativeInstitutionAttr['readonly'] = true;
            }
            $builder->add('tentativeInstitution', ChoiceType::class, array( //flipped
                'label' => "Tentative Approval:",
                'required' => $requiredTentInst,
                'attr' => $tentativeInstitutionAttr, //array('class' => 'combobox combobox-width vacreq-tentativeInstitution', 'placeholder' => 'Organizational Group'),
                'choices' => $this->params['tentativeInstitutions'],
                //'choices_as_values' => true,
                //'disabled' => ($this->params['review'] ? true : false)
            ));
            $builder->get('tentativeInstitution')
                ->addModelTransformer(new CallbackTransformer(
                    //original from DB to form: institutionObject to institutionId
                        function ($originalInstitution) {
                            //echo "originalInstitution=".$originalInstitution."<br>";
                            if (is_object($originalInstitution) && $originalInstitution->getId()) { //object
                                return $originalInstitution->getId();
                            }
                            return $originalInstitution; //id
                        },
                        //reverse from form to DB: institutionId to institutionObject
                        function ($submittedInstitutionObject) {
                            //echo "submittedInstitutionObject=".$submittedInstitutionObject."<br>";
                            if ($submittedInstitutionObject) { //id
                                $institutionObject = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                                return $institutionObject;
                            }
                            return null;
                        }
                    )
                );
        }//if tentativeInstitutions

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\VacReqBundle\Entity\VacReqRequest',
            'form_custom_value' => null,
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_vacreqbundle_request';
    }


}

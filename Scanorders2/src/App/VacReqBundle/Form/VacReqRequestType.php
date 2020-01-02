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

namespace Oleg\VacReqBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\VacReqBundle\Form\VacReqRequestBusinessType;


class VacReqRequestType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //common fields for all request types
        $this->addCommonFields($builder);

        if( $this->params['requestType']->getAbbreviation() == "business-vacation" ) {
            $this->addBusinessVacationFields($builder);
        }

        if( $this->params['requestType']->getAbbreviation() == "carryover" ) {
            $this->addCarryOverFields($builder);
        }


        //show final status and tentative status only for carryover requests
        if( $this->params['requestType']->getAbbreviation() == "carryover" && ($this->params['cycle'] == 'review' || $this->params['cycle'] == 'show') ) {

            //enable tentativeStatus radio only when review and not roleCarryOverApprover
//            $tentativereadOnly = true;
//            if( $this->params['review'] === true && ($this->params['roleAdmin'] || !$this->params['roleCarryOverApprover']) ) {
//                $tentativereadOnly = false;
//            }
            $tentativereadOnly = true;
            if( $this->params['roleAdmin'] ||
                ($this->params['review'] == true && $this->params['roleApprover'] && !$this->params['roleCarryOverApprover'])
            ) {
                $tentativereadOnly = false;
            }

            $builder->add('tentativeStatus', ChoiceType::class, array( //flipped
                //'disabled' => $tentativereadOnly,    //($this->params['roleAdmin'] ? false : true),
//                'choices' => array(
//                    //'pending' => 'Pending',
//                    'approved' => 'Approved',
//                    'rejected' => 'Rejected'
//                ),
                'choices' => array(
                    //'pending' => 'Pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected'
                ),
                //'choices_as_values' => true,
                'label' => false,   //"Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                'attr' => array('class' => 'horizontal_type_wide', 'readonly'=>$tentativereadOnly), //horizontal_type
            ));

            //enable status radio only for admin or for reviewer
            $readOnly = true;
            if( $this->params['roleAdmin'] ||
                ($this->params['review'] == true && $this->params['roleCarryOverApprover']) ) {
                $readOnly = false;
            }

            $builder->add('status', ChoiceType::class, array( //flipped
                //'disabled' => $readOnly,    //($this->params['roleAdmin'] ? false : true),
//                'choices' => array(
//                    //'pending' => 'Pending',
//                    'approved' => 'Approved',
//                    'rejected' => 'Rejected'
//                ),
                'choices' => array(
                    //'pending' => 'Pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected'
                ),
                //'choices_as_values' => true,
                'label' => false,   //"Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                'attr' => array('class' => 'horizontal_type_wide', 'readonly'=>$readOnly), //horizontal_type
            ));

        }

        if( $this->params['requestType']->getAbbreviation() == "carryover" ) {
            $builder->add('comment', TextareaType::class, array(
                'label' => 'Comment:',
                //'disabled' => $readOnly,
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequest',
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_vacreqbundle_request';
    }

    public function addBusinessVacationFields( $builder ) {
        $builder->add('phone', null, array(
            'label' => "Phone Number for the person away:",
            'attr' => array('class' => 'form-control vacreq-phone'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        //Business Travel
        $builder->add('requestBusiness', VacReqRequestBusinessType::class, array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestBusiness',
            'form_custom_value' => $this->params,
            'label' => false,
            'required' => false,
        ));

        //Business Travel
        $builder->add('requestVacation', VacReqRequestVacationType::class, array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestVacation',
            'form_custom_value' => $this->params,
            'label' => false,
            'required' => false,
        ));


        if( $this->params['cycle'] != 'show' && !$this->params['review'] ) {

            //enabled ($readOnly = false) for admin only
            $readOnly = true;
            if( $this->params['roleAdmin'] ) {
                $readOnly = false;
            }

            $builder->add('submitter', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Request Submitter:",
                'required' => true,
                'multiple' => false,
                //'choice_label' => 'name',
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true),
                //'disabled' => true,    //$readOnly,   //($this->params['review'] ? true : false),
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


        //Emergency info
        $attrArr = array();
        if( $this->params['review'] ) {
            $attrArr['disabled'] = 'disabled';
            //$attrArr['readonly'] = true;
        }

        $attrArr['class'] = 'vacreq-availableViaEmail';
        $builder->add('availableViaEmail', null, array(
            'label' => "Available via E-Mail:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaEmail'),
            //'disabled' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableEmail', null, array(
            'label' => "E-Mail address while away on this trip:",
            'attr' => array('class' => 'form-control vacreq-availableEmail'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        $attrArr['class'] = 'vacreq-availableViaCellPhone';
        $builder->add('availableViaCellPhone', null, array(
            'label' => "Available via Cell Phone:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaCellPhone'),
            //'disabled' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableCellPhone', null, array(
            'label' => "Cell Phone number while away on this trip:",
            'attr' => array('class' => 'form-control vacreq-availableCellPhone'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        $attrArr['class'] = 'vacreq-availableViaOther';
        $builder->add('availableViaOther', null, array(
            'label' => "Available via another method:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableViaOther', 'disabled'=>$disableCheckbox),
            //'disabled' => ($this->params['review'] ? true : false)
        ));
        $builder->add('availableOther', null, array(
            'label' => "Other:",
            'attr' => array('class' => 'form-control vacreq-availableOther'),
            'disabled' => ($this->params['review'] ? true : false),
        ));

        $attrArr['class'] = 'vacreq-availableNone';
        $builder->add('availableNone', null, array(
            'label' => "Not Available:",
            'attr' => $attrArr, //array('class' => 'vacreq-availableNone'),
            //'disabled' => ($this->params['review'] ? true : false)
        ));


        $builder->add('firstDayBackInOffice', DateType::class, array(
            'label' => 'First Day Back in Office:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date vacreq-firstDayBackInOffice'),
            'disabled' => ($this->params['review'] ? true : false)
        ));
    }


    public function addCarryOverFields( $builder ) {

        $builder->add('sourceYear', ChoiceType::class, array( //flipped
            'label' => "Source Academic Year:",
            'attr' => array('class' => 'combobox combobox-width vacreq-sourceYear'),
            'choices' => $this->params['sourceYearRanges'],
            //'choices_as_values' => true,
        ));

        $builder->add('destinationYear', ChoiceType::class, array( //flipped
            'label' => "Destination Academic Year:",
            'attr' => array('class' => 'combobox combobox-width vacreq-destinationYear'),
            'choices' => $this->params['destinationYearRanges'],
            //'choices_as_values' => true,
        ));

        $builder->add('carryOverDays', null, array(
            'label' => "Number of days to carry over:",
            'attr' => array('class' => 'form-control vacreq-carryOverDays'),
        ));

    }


    public function addCommonFields( $builder ) {

        if( $this->params['cycle'] == 'show' ) {
            //approver
            $builder->add('approver', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:User',
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
            $builder->add('user', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Person Away:",
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
        }


        //organizationalInstitutions
        $requiredInst = false;
        if( count($this->params['organizationalInstitutions']) == 1 ) {
            //echo "set org inst <br>";
            $requiredInst = true;
        }

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
                            $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
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
                                $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                                return $institutionObject;
                            }
                            return null;
                        }
                    )
                );
        }//if tentativeInstitutions

    }

}

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

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VacReqFilterType extends AbstractType
{

    private $params;


    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //visible only for my request and incoming requests for SUPERVISOR users
        if( $this->params['routeName'] == 'vacreq_myrequests' || $this->params['supervisor'] || $this->params['approverRole'] ) {
            $builder->add('requestType', 'entity', array(
                'class' => 'OlegVacReqBundle:VacReqRequestTypeList',
                'choice_label' => 'name',
                'label' => false,
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Request Type'),
                //'choices' => $this->params['filterUsers'],
            ));
        }

        if ($this->params['filterShowUser']) {
            $builder->add('user', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'choice_label' => 'getUserNameStr',
                'label' => false,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Person Away - Name or CWID)'),
                'choices' => $this->params['filterUsers'],
            ));

            $builder->add('submitter', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'choice_label' => 'getUserNameStr',
                'label' => false,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Submitter - Name or CWID'),
                'choices' => $this->params['filterUsers'],
            ));
        }

        $this->addGroup($builder);

        if( $this->params['requestTypeAbbreviation'] == "business-vacation" ) {
            $builder->add('academicYear', 'datetime', array(
                'label' => false,
                'widget' => 'single_text',
                'required' => false,
                'format' => 'yyyy',
                'attr' => array('class' => 'datepicker-only-year form-control', 'placeholder' => 'Academic Year', 'title' => $this->params['academicYearTooltip'], 'data-toggle' => 'tooltip'),
            ));
        }

//        $builder->add('cwid', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

//        $builder->add('search', 'text', array(
//            //'placeholder' => 'Search',
//            'max_length' => 200,
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));

        $builder->add('startdate', 'date', array(
            'label' => false, //'Start Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'Start Date', 'title'=>'Start Date of Request Submission', 'data-toggle'=>'tooltip')
        ));

        $builder->add('enddate', 'date', array(
            'label' => false, //'End Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'End Date', 'title'=>'End Date of Request Submission', 'data-toggle'=>'tooltip')
        ));

//        $builder->add('year', 'text', array(
//            'required' => false,
//            'label' => false,
//            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field', 'placeholder' => 'Year'),
//        ));

        if( $this->params['requestTypeAbbreviation'] == "business-vacation" ) {
            $builder->add('vacationRequest', 'checkbox', array(
                'label' => 'Vacation Requests',
                'required' => false,
            ));
            $builder->add('businessRequest', 'checkbox', array(
                'label' => 'Business Travel Requests',
                'required' => false,
            ));
        }

//        $builder->add('completed', 'checkbox', array(
//            'label' => 'Completed Requests',
//            'required' => false,
//        ));
        $builder->add('pending', 'checkbox', array(
            'label' => 'Pending Requests',
            'required' => false,
        ));
        $builder->add('approved', 'checkbox', array(
            'label' => 'Approved Requests',
            'required' => false,
        ));
        $builder->add('rejected', 'checkbox', array(
            'label' => 'Rejected Requests',
            'required' => false,
        ));

        //cancellation request
        $builder->add('cancellationRequest', 'checkbox', array(
            'label' => 'Requested Cancellations',
            'required' => false,
        ));
        $builder->add('cancellationRequestApproved', 'checkbox', array(
            'label' => 'Approved Cancellations',
            'required' => false,
        ));
        $builder->add('cancellationRequestRejected', 'checkbox', array(
            'label' => 'Rejected Cancellations',
            'required' => false,
        ));



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

    public function addGroup($builder) {

        if( count($this->params['organizationalInstitutions']) > 1 || $this->params['supervisor'] ) {

//            echo "show group selector!!!!!! <br>";
//            echo "<pre>";
//            print_r($this->params['organizationalInstitutions']);
//            echo "</pre>";

            if( count($this->params['organizationalInstitutions']) == 1 ) {
                $required = true;
            } else {
                $required = false;
            }

            //Institutional Group name - ApproverName
            $builder->add('organizationalInstitutions', ChoiceType::class, array( //flipped
                'label' => false,   //"Organizational Group:",
                'required' => $required,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Organizational Group'),
                'choices' => $this->params['organizationalInstitutions'],
                'choices_as_values' => true,
            ));
            $builder->get('organizationalInstitutions')
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

        }//if

    }//addGroup
}

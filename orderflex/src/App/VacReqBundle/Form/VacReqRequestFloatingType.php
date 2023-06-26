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



use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\VacReqBundle\Entity\VacReqFloatingTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqFloatingTypeList


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\VacReqBundle\Form\VacReqRequestBusinessType;


class VacReqRequestFloatingType extends AbstractType
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
        if( $this->params['roleAdmin'] || $this->params['review'] == true ) {
            $readOnly = false;
        }

//        $readOnly = true;
//        $readOnly = false;
//        if( $readOnly == true ) {
//            echo "readOnly true <br>";
//        }
//        if( $readOnly == false ) {
//            echo "readOnly false <br>";
//        }

        if( $this->params['cycle'] != 'new' ) {
            $builder->add('status', ChoiceType::class, array( //flipped
                'disabled' => $readOnly,    //($this->params['roleAdmin'] ? false : true),
                'choices' => array(
                    'Pending' => 'pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected'
                ),
                'label' => "Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                //'attr' => array('class' => 'horizontal_type_wide', 'readonly' => $readOnly), //horizontal_type
                'attr' => array('class' => 'horizontal_type_wide'), //horizontal_type
            ));

            $builder->add('approverComment', TextareaType::class, array(
                'label' => "Approver Comment:",
                'required' => false,
                'disabled' => $readOnly,
                'attr' => array('class' => 'textarea form-control'), //'readonly' => $readOnly
            ));
        }

        if( $this->params['review'] == true ) {
            //echo "show submit review button <br>";
            $builder->add('save', SubmitType::class, array(
                'label' => "Submit Review",
                'attr' => array('class' => 'btn btn-warning')
            ));
        }

        if( $this->params['cycle'] == 'show' ) {
            //approver
            $builder->add('approver', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                'class' => User::class,
                'label' => "Approver:",
                'required' => false,
                //'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true),
            ));
        }

        if( $this->params['cycle'] != 'show' && !$this->params['review'] ) {
            //$userAttr = array('class' => 'combobox combobox-width');
            //if( $this->params['review'] ) {
            //    $userAttr['readonly'] = true;
            //}
            $builder->add('submitter', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                'class' => User::class,
                'label' => "Request Submitter:",
                'required' => true,
                'multiple' => false,
                //'choice_label' => 'name',
                //'attr' => $userAttr,    //array('class' => 'combobox combobox-width'),
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>true),
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

            $userAttr = array('class' => 'combobox combobox-width');
            if( $this->params['review'] ) {
                $userAttr['readonly'] = true;
            }
            $builder->add('user', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                'class' => User::class,
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                            $institutionObject = $this->params['em']->getRepository(Institution::class)->find($submittedInstitutionObject);
                            return $institutionObject;
                        }
                        return null;
                    }
                )
            );


        //Only Floating day fields
        $this->floatingDayFields($builder);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\VacReqBundle\Entity\VacReqRequestFloating',
            'form_custom_value' => null,
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_vacreqbundle_requestfloating';
    }

    public function floatingDayFields( $builder ) {
        $disable = false;
        if( $this->params['review'] == true ) {
            $disable = true;
        }

        $builder->add('phone', null, array(
            'label' => "Phone Number for the person away:",
            'data' => "+1 123 456-7890",
            'disabled' => $disable,
            'attr' => array('class' => 'form-control vacreq-phone'),
        ));

        $builder->add('floatingType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqFloatingTypeList'] by [VacReqFloatingTypeList::class]
            'class' => VacReqFloatingTypeList::class,
            'label' => "Floating Day:",
            'required' => false,
            'multiple' => false,
            'data' => $this->params['defaultFloatingType'],
            'disabled' => $disable,
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

        $builder->add('work', CheckboxType::class, array(
            'label' => 'This person has worked or plans to work on',
            'required' => false,
            'disabled' => $disable,
            'attr' => array('class' => 'floatingday-work'),
        ));

        //use $floatingRestrictDateRange to limit to the current academic year
        $builder->add('floatingDay', DateType::class, array(
            'label' => "The floating day requested for this fiscal year is:",
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'disabled' => $disable,
            'attr' => array(
                'class' => 'form-control datetimepicker floatingDay',
                'placeholder' => 'Floating Date',
                'title'=>'The floating day I am requesting', 'data-toggle'=>'tooltip',
                'data-calendarstartdate' => $this->params['calendarStartDate'],
                'data-calendarenddate' => $this->params['calendarEndDate']
            )
        ));
        
    }

}

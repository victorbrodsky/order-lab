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

namespace App\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        foreach($this->params["projectSpecialtyAllowedArr"] as $specialty){
//            echo "specialty=".$specialty."<br>";
//        }

        $projectSpecialtyAllowedArr = array();
        foreach($this->params["projectSpecialtyAllowedArr"] as $spec) {
            $projectSpecialtyAllowedArr[] = $spec;
        }

        if( count($this->params["projectSpecialtyAllowedArr"]) == 1 ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        $builder->add('projectSpecialty', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:SpecialtyList',
            //'choice_label' => 'name',
            'label' => false,
            'disabled' => $disabled,
            'required'=> false,
            'multiple' => true,
            'choices' => $this->params["projectSpecialtyAllowedArr"],
            'data' => $projectSpecialtyAllowedArr,
            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
        ));

        $builder->add('state',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['stateChoiceArr'],
            'data' => $this->params['defaultStatesArr'],
            'attr' => array('class' => 'combobox'),
        ));

        $builder->add('principalInvestigators', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> false,    //"Principal Investigator(s):",
            'required'=> false,
            'multiple' => true,
            'choices' => $this->params['transresUsers'],
            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                    ->leftJoin("list.infos", "infos")
//                    ->orderBy("infos.displayName","ASC");
//            },
        ));

        $builder->add('associatedUsers', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> false,  
            'required'=> false,
            'multiple' => true,
            'choices' => $this->params['transresUsers'],
            'attr' => array('class'=>'combobox combobox-width')
        ));

        $builder->add('submitter', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> false,
            'required'=> false,
            'multiple' => false,
            'choices' => $this->params['transresUsers'],
            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                    ->leftJoin("list.infos", "infos")
//                    ->orderBy("infos.displayName","ASC");
//            },
        ));

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Submission Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Submission Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('searchId', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Project Request ID'),
        ));
        $builder->add('searchTitle', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Title'),
        ));
        $builder->add('searchIrbNumber', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>$this->params['humanAnimalNameBracket'].' Number'),
        ));

        $builder->add('fundingNumber', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Fund Number'),
        ));

        $builder->add('fundingType',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => false,
            'choices' => array(
                'Funded' => 'Funded',
                'Non-Funded' => 'Non-Funded'
            ),
            'data' => $this->params['fundingType'],
            'attr' => array('class' => 'combobox', 'placeholder'=>'Funded vs Non-Funded'),
        ));

        $builder->add('searchProjectType', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:ProjectTypeList',
            'label'=> false,
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width submit-on-enter-field', 'placeholder'=>'Project Type'),
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

        $builder->add('exportId', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'External Legacy ID'),
        ));

        $builder->add('reviewers', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> false,    //"Principal Investigator(s):",
            'required'=> false,
            'multiple' => true,
            'choices' => $this->params['transresUsers'],
            'attr' => array('class'=>'combobox combobox-width', 'placeholder'=>'Reviewer(s)'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                    ->leftJoin("list.infos", "infos")
//                    ->orderBy("infos.displayName","ASC");
//            },
        ));


        $builder->add('humanTissue',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => false,
            'choices' => array(
                'Involves Human Tissue' => 'Involves Human Tissue',
                'Does Not Involve Human Tissue' => 'Does Not Involve Human Tissue'
            ),
            'attr' => array('class' => 'combobox', 'placeholder'=>'Human Tissue'),
        ));

        $builder->add('exemptIrbApproval',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => false,
            'choices' => array(
                'Exempt from '.$this->params['humanName'].' Approval' => 'exempt-from-irb-approval',
                'Not Exempt from '.$this->params['humanName'].' Approval' => 'not-exempt-from-irb-approval'
            ),
            'attr' => array('class' => 'combobox', 'placeholder'=>$this->params['humanName'].' Approval'),
        ));

        $builder->add('fromExpectedCompletionDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Expected Completion Date'),
        ));
        $builder->add('toExpectedCompletionDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Expected Completion Date'),
        ));

        $builder->add('fromImplicitExpDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'data' => $this->params['fromImplicitExpDate'],
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From '.$this->params['humanAnimalNameSlash'].' Expiration Date'),
        ));
        $builder->add('toImplicitExpDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'data' => $this->params['toImplicitExpDate'],
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To '.$this->params['humanAnimalNameSlash'].' Expiration Date'),
        ));

        $builder->add('briefDescription', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Brief Description'),
        ));

        if (
            //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
            //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
            $this->params['trpAdminOrTech']
        ) {
            if(0) {
                $builder->add('priceList', EntityType::class, array(
                    'class' => 'AppTranslationalResearchBundle:PriceTypeList',
                    'label' => false,
                    'required' => false,
                    'multiple' => true,
                    'attr' => array('class' => 'combobox combobox-width submit-on-enter-field', 'placeholder' => 'Price List'),
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
            } else {
                $builder->add('priceList',ChoiceType::class, array(
                    'label' => false,
                    'required' => true,
                    'multiple' => false,
                    'choices' => $this->params['transresPricesList'],
                    'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder'=>'Price List'),
                ));
            }
        }

        if (
            //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
            //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
            $this->params['trpAdminOrTech']
        ) {
            $overBudgetArr = array();
            $overBudgetArr['All'] = 'all';
            $overBudgetArr['Over Budget or With No Budget'] = 'over-budget-with-no-budget';
            $overBudgetArr['Over Budget'] = 'over-budget';
            $overBudgetArr['With No Budget'] = 'with-no-budget';
            $builder->add('overBudget', ChoiceType::class, array(
                'label' => false,
                'required' => false,
                'multiple' => false,
                'choices' => $overBudgetArr,
                //'data' => 'all',
                'data' => $this->params['overBudget'],
                'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder' => 'Over Budget Filter'),
            ));
        }

        $builder->add('expectedExpirationDateChoices', ChoiceType::class, array(
            'label' => false,
            'required' => true,
            'multiple' => false,
            'choices' => $this->params['expectedExpirationDateChoices'],
            'attr' => array('class'=>'combobox submit-on-enter-field', 'placeholder' => 'Expiration'),
        ));

//        $builder->add('showMatchingAndTotal', ChoiceType::class, array(
//            'label' => false,
//            'required' => true,
//            'multiple' => false,
//            'choices' => array(
//                "Without Matching and Total (Faster)" => "WithoutTotal",
//                'With Matching and Total (Slower)' => 'WithTotal'
//            ),
//            'attr' => array('class' => 'combobox'),
//        ));

//        $builder->add('preroute', HiddenType::class, array( //TextType HiddenType
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control'),
//        ));

//        $builder->add('completed', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'Completed',
//        ));
//
//        $builder->add('review', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'Review',
//        ));
//
//        $builder->add('missinginfo', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'Requested additional information',
//        ));
//
//        $builder->add('approved', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'Approved',
//        ));
//
//        $builder->add('closed', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'Closed',
//        ));
        
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}

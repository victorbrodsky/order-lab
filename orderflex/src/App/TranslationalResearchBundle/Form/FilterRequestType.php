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
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterRequestType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $userChoiceLabel = "getUserNameStr";
        //$userChoiceLabel = "getId";

        $projectChoiceLabel = "getProjectInfoNameWithPIsChoice";
        //$projectChoiceLabel = "getId";

        $categoriesChoiceLabel = "getOptimalAbbreviationName";
        //$categoriesChoiceLabel = "getId";

        if (count($this->params['transresUsers']) > 0) {

            $builder->add('submitter', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Reviewer Delegate:",
                'required' => false,
                'multiple' => false,
                'choices' => $this->params['transresUsers'],
                'choice_label' => $userChoiceLabel,
                'attr' => array('class' => 'combobox combobox-width',),
            ));

            $builder->add('billingContact', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => false,
                'required' => false,
                'multiple' => false,
                'choices' => $this->params['transresUsers'],
                'choice_label' => $userChoiceLabel,
                'attr' => array('class' => 'combobox combobox-width'),
            ));

            $builder->add('principalInvestigators', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => false,
                'required' => false,
                'multiple' => true,
                'choices' => $this->params['transresUsers'],
                'choice_label' => $userChoiceLabel,
                'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
//                    ->leftJoin("list.infos", "infos")
//                    ->orderBy("infos.displayName", "ASC");
//            },
            ));

        } else {

            $builder->add('submitter', CustomSelectorType::class, array(
                //'label' => 'Building:',
                'attr' => array('class' => 'combobox combobox-without-add ajax-combobox-submitter', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'genericuser'
            ));

            $builder->add('billingContact', CustomSelectorType::class, array(
                //'label' => 'Building:',
                'attr' => array('class' => 'combobox combobox-without-add ajax-combobox-billingcontact', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'genericuser'
            ));

            $builder->add('principalInvestigators', CustomSelectorType::class, array(
                //'label' => 'Building:',
                'attr' => array('class' => 'combobox combobox-without-add ajax-combobox-pis', 'type' => 'hidden'),
                'required' => false,
                //'multiple' => true,
                'classtype' => 'genericusers'
            ));

            //shown only to users with Site Administrator, Technologist, Platform Administrator, and Deputy Platform Administrator" roles
            if( $this->params['showCompletedByUser'] ) {
                $builder->add('completedBy', CustomSelectorType::class, array(
                    'attr' => array('class' => 'combobox combobox-without-add ajax-combobox-completedby', 'type' => 'hidden'),
                    'required' => false,
                    'classtype' => 'genericuser'
                ));
            }
        }

        $builder->add('comment', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Comment Text'),
        ));

        if(1) {
            $builder->add('categories', EntityType::class, array(
                'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
                'label' => false,
                'choice_label' => $categoriesChoiceLabel, //"getOptimalAbbreviationName",
                'required' => false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width'),
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

        $builder->add('progressState',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['progressStateArr'],
            //'data' => $this->params['progressStateDefault'],
            'attr' => array('class' => 'combobox'),
        ));

        $builder->add('billingState',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['billingStateArr'],
            'attr' => array('class' => 'combobox'),
        ));

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Submission Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Submission Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

//        $builder->add('accountNumber', TextType::class, array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by IRB number'),
//        ));

        $projectSpecialtyAllowedArr = array();
        foreach($this->params["projectSpecialtyAllowedArr"] as $spec) {
            $projectSpecialtyAllowedArr[] = $spec;
        }

        if( count($projectSpecialtyAllowedArr) == 1 ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        $builder->add('projectSpecialty', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:SpecialtyList',
            'label' => false,   //'Project Specialty',
            'required'=> false,
            'multiple' => true,
            'disabled' => $disabled,
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

        $builder->add('fundingNumber', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by Fund Number'),
        ));

        $builder->add('fundingType',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => false,
            'choices' => array(
                'Funded (With Fund Number)' => 'Funded',
                'Non-Funded (No Fund Number)' => 'Non-Funded'
            ),
            'attr' => array('class' => 'combobox', 'placeholder'=>'Funded vs Non-Funded'),
        ));

        $builder->add('externalId', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'External ID'),
        ));

        $builder->add('requestId', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Work Request ID'),
        ));

        $builder->add('sampleName', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Deliverable Barcode ID'),
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

        if( count($this->params['availableProjects']) > 0 ) {
            if ($this->params['routeName'] == "translationalresearch_request_index") {
                //echo "Use data projects <br>";
                $builder->add('project', EntityType::class, array(
                    'class' => 'AppTranslationalResearchBundle:Project',
                    //'choice_label' => "getProjectInfoNameChoice",          //Without PIs
                    'choice_label' => $projectChoiceLabel, //"getProjectInfoNameWithPIsChoice",     //With PIs
                    'required' => false,
                    'label' => false,
                    'data' => $this->params['project'],
                    'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Project"),
                ));
            } else {
//            $builder->add('project', EntityType::class, array(
//                'class' => 'AppTranslationalResearchBundle:Project',
//                'choice_label' => "getProjectInfoName",
//                'required' => false,
//                'label' => false,
//                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Project"),
//            ));
                //echo "Use all projects <br>";
                $builder->add('project', EntityType::class, array(
                    'class' => 'AppTranslationalResearchBundle:Project',
                    //'choice_label' => "getProjectInfoNameChoice",        //Without PIs
                    'choice_label' => $projectChoiceLabel, //"getProjectInfoNameWithPIsChoice",   //With PIs - this option causes ~135 additional DB queries (~number of existing projects)
                    'choices' => $this->params['availableProjects'],
                    //'disabled' => ($this->params['admin'] ? false : true),
                    //'disabled' => true,
                    'required' => false,
                    'multiple' => false,
                    'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Project")
                ));
            }
        } else {
            $builder->add('projectSearch', TextType::class, array(
                'required' => false,
                //'attr' => array('class' => 'form-control typeahead', 'placeholder' => "Project", 'style' => 'font-size: 14px; width: 380px;' ),
                'attr' => array('class' => 'form-control typeahead', 'placeholder' => "Project", 'style' => 'font-size: 14px; width: 320px;' ),
            ));
        }

        if (
            $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $builder->add('priceList',ChoiceType::class, array(
                'label' => false,
                'required' => true,
                'multiple' => false,
                'choices' => $this->params['transresPricesList'],
                'attr' => array('class' => 'combobox submit-on-enter-field', 'placeholder'=>'Price List'),
            ));
        }
        
        $builder->add('workQueues', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:WorkQueueList',
            'label' => false,
            //'choice_label' => $categoriesChoiceLabel, //"getOptimalAbbreviationName",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }
}

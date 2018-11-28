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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterDashboardType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params["projectSpecialty"] ) {
//            $builder->add('projectSpecialty', EntityType::class, array(
//                'class' => 'OlegTranslationalResearchBundle:SpecialtyList',
//                //'choice_label' => 'name',
//                'label' => false,   //'Project Specialty',
//                //'disabled' => ($this->params['admin'] ? false : true),
//                'required' => false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Specialty'),
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist", "ASC")
//                        ->setParameters(array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//            ));
            $builder->add('projectSpecialty', ChoiceType::class, array(
                'label' => false,
                'choices' => $this->params['projectSpecialties'],
                'required' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => "Specialty")
            ));
        }

//        $builder->add('state',ChoiceType::class, array(
//            'label' => false,
//            'required' => false,
//            'multiple' => true,
//            'choices' => $this->params['stateChoiceArr'],
//            'attr' => array('class' => 'combobox'),
//        ));

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            //'data' => new \DateTime(),  //$this->params['startDate'],
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Submission Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            //'data' => $this->params['endDate'],
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Submission Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

        if( $this->params['compareType'] ) {
            $builder->add('compareType', ChoiceType::class, array(
                'label' => false,
                'choices' => array(
                    "work request submission date" => "work request submission date",
                    "last invoice generation date" => "last invoice generation date",
                    "date when status changed to 'Paid in Full'" => "date when status changed to paid in full"
                ),
                'required' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => "Compare Type")
            ));
        }

        if( $this->params['showLimited'] ) {
            $builder->add('showLimited', CheckboxType::class, array(
                'label' => "Show only the top ten", //"Show the full data set on each graph",  //"Show only the top ten",
                'required' => false,
                //'attr' => array('class' => 'form-control checkbox')
            ));
        }

        if( isset($this->params['chartType']) && $this->params['chartType'] ) {
            $builder->add('chartType', ChoiceType::class, array(
                'label' => false,
                'choices' => $this->params['chartTypes'],
                'multiple' => true,
                'required' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => "Chart Type")
            ));
        }

        if( isset($this->params['category']) && $this->params['category'] ) {
            $builder->add('category', EntityType::class, array(
                'class' => 'OlegTranslationalResearchBundle:RequestCategoryTypeList',
                'label' => false,
                'choice_label' => "getOptimalAbbreviationName",
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width', 'placeholder'=>'Products/Services'),
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

//        $builder->add('searchId', TextType::class, array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by ID'),
//        ));
//        $builder->add('searchTitle', TextType::class, array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by Title'),
//        ));
//        $builder->add('searchIrbNumber', TextType::class, array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by IRB number'),
//        ));
//
//        $builder->add('fundingNumber', TextType::class, array(
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Search by Funding Number'),
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

    public function getBlockPrefix()
    {
        return 'filter';
    }
}

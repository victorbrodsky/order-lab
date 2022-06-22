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

namespace App\DashboardBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterDashboardType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( isset($this->params['chartTypesShow']) == false ) {
            $this->params['chartTypesShow'] = NULL;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //dump($this->params['projectSpecialties']);
        //exit('111');
        //$this->params['projectSpecialties'] = array(1,2,3);
//        if( !is_array($this->params["projectSpecialties"]) ) {
//            dump($this->params['projectSpecialties']);
//            exit('not array');
//        }

        //Symfony\Bridge\Twig\Extension\twig_is_selected_choice(): Argument #2 ($selectedValue) must be of type array|string, null given
        if( $this->params["projectSpecialty"] && is_array($this->params["projectSpecialties"] ) ) {
            //dump($this->params['projectSpecialties']);
            //exit('111');
            $builder->add('projectSpecialty', ChoiceType::class, array(
                'label' => false,
                'choices' => $this->params['projectSpecialties'],
                'required' => false,
                'attr' => array('class' => 'combobox', 'placeholder' => "Specialty")
            ));
        }

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            //'data' => new \DateTime(),  //$this->params['startDate'],
            'data' => $this->params['startDate'],
            'attr' => array('class'=>'datepicker form-control', 'placeholder'=>'From Submission Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'data' => $this->params['endDate'],
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
                'label' => "Hide remaining total", //"Show only the top N", //"Show the full data set on each graph"
                'required' => false,
                'attr' => array('class' => 'form-control checkbox')
            ));

            //dropdown listing number from 1 to 50 and the words "Show all" as the top choice
            $quantityLimitArr = array();
            $quantityLimitArr["Show all"] = "Show all";
            for($quantityLimit = 1; $quantityLimit <= 50; $quantityLimit++) {
                //echo "The number is: $x <br>";
                $quantityLimitArr[$quantityLimit] = $quantityLimit."";
            }
            $builder->add('quantityLimit', ChoiceType::class, array(
                'label' => "Quantity limit",
                'choices' => $quantityLimitArr,
                'multiple' => false,
                //'expanded' => true,
                'required' => false,
                'empty_data' => '10',
                //'data' => '10',
                'attr' => array('class' => 'combobox', 'placeholder' => "Quantity limit")
            ));
        }

        if( isset($this->params['chartType']) && $this->params['chartType'] ) {
            //dump($this->params['chartTypes']);
            //dump($this->params['chartTypesShow']);
            //exit('111');
            $builder->add('chartType', ChoiceType::class, array(
                'label' => false,
                'choices' => $this->params['chartTypes'],
                'multiple' => true,
                'required' => false,
                'data' => $this->params['chartTypesShow'],
                'attr' => array('class' => 'combobox', 'placeholder' => "Chart Type")
            ));
        }

        if( isset($this->params['category']) && $this->params['category'] ) {
            //productservice
            $builder->add('category', EntityType::class, array(
                'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
                'label' => "Products/Services", //false,
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

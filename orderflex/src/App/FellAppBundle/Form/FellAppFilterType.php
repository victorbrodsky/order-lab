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

namespace App\FellAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FellAppFilterType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //date as DateTime object
//        $builder->add('startDates', DateTimeType::class, array(
//            'label' => false, //'Start Date',
//            'widget' => 'single_text',
//            //'placeholder' => 'Start Date',
//            'required' => false,
//            //'format' => 'MM/dd/yyyy',
//            'format' => 'yyyy',
//            //'attr' => array('class' => 'datepicker-only-year form-control'),
//            'attr' => array('class'=>'datepicker-only-year datepicker-multidate form-control', 'title'=>'Start Year', 'data-toggle'=>'tooltip'),
//            //'attr' => array('class'=>'datepicker datepicker-multidate form-control', 'title'=>'Start Year', 'data-toggle'=>'tooltip'),
//        ));

        //date as string
        $builder->add('startDates', TextType::class, array(
            'label' => false, //'Start Date',
            //'placeholder' => 'Start Date',
            'required' => false,
            //'data' => $startDates, //$this->params['defaultStartDates'],
            'attr' => array('class' => 'datepicker-only-year datepicker-multidate form-control', 'title' => 'Start Year', 'data-toggle' => 'tooltip'),
        ));

//        $builder->add('filter', 'entity', array(
//            'class' => 'AppUserdirectoryBundle:FellowshipSubspecialty',
//            //'placeholder' => 'Fellowship Type',
//            'choice_label' => 'name',
//            'label' => false,
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class' => 'combobox fellapp-fellowshipSubspecialty-filter'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//        ));

        //fellowshipSubspecialty
        if ($this->params['fellTypes'] && count($this->params['fellTypes']) > 0) {
            $builder->add('filter', ChoiceType::class, array( //flipped
                'label' => false,
                'required' => false,
                //'multiple' => false,
                'choices' => $this->params['fellTypes'], //flipped
                //'choices_as_values' => true,
                'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty-filter'),
            ));
        }

        //globalFellowshipSpecialty
        if ($this->params['globalFellTypes'] && count($this->params['globalFellTypes']) > 0) {
            $builder->add('globalfilter', ChoiceType::class, array( //flipped
                'label' => false,
                'required' => false,
                //'multiple' => false,
                'choices' => $this->params['globalFellTypes'], //flipped
                //'choices_as_values' => true,
                'attr' => array('class' => 'combobox combobox-width fellapp-globalFellowshipSpecialty-filter'),
            ));
        }

        $builder->add('search', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length'=>200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

        $builder->add('draft', CheckboxType::class, array(
            'required' => false,
            'label' => 'Draft',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('withdrawn', CheckboxType::class, array(
            'required' => false,
            'label' => 'Withdrawn',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('hidden', CheckboxType::class, array(
            'required' => false,
            'label' => 'Hidden',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('archived', CheckboxType::class, array(
            'required' => false,
            'label' => 'Archived',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('complete', CheckboxType::class, array(
            'required' => false,
            'label' => 'Complete',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('interviewee', CheckboxType::class, array(
            'required' => false,
            'label' => 'Interviewee',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('active', CheckboxType::class, array(
            'required' => false,
            'label' => 'Active',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('declined', CheckboxType::class, array(
            'required' => false,
            'label' => 'Declined',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('reject', CheckboxType::class, array(
            'required' => false,
            'label' => 'Rejected',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

//        $builder->add('onhold', CheckboxType::class, array(
//            'required'=>false,
//            'label' => 'On Hold',
//            'attr' => array('class' => 'filter-status-checkbox'),
//        ));

        $builder->add('priority', CheckboxType::class, array(
            'required'=>false,
            'label' => 'Priority',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('priorityinterviewee', CheckboxType::class, array(
            'required'=>false,
            'label' => 'Priority Interviewee',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));

        $builder->add('accepted', CheckboxType::class, array(
            'required'=>false,
            'label' => 'Accepted',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));
        $builder->add('acceptedandnotified', CheckboxType::class, array(
            'required'=>false,
            'label' => 'Accepted and Notified',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));
        $builder->add('rejectedandnotified', CheckboxType::class, array(
            'required'=>false,
            'label' => 'Rejected and Notified',
            'attr' => array('class' => 'filter-status-checkbox'),
        ));
        
    }

    public function configureOptions(OptionsResolver $resolver) : void
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

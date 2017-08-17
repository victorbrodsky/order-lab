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

namespace Oleg\FellAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
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

        $builder->add('startDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            //'placeholder' => 'Start Date',
            'required' => false,
            //'format' => 'MM/dd/yyyy',
            'format' => 'yyyy',
            //'attr' => array('class' => 'datepicker form-control'),
            //'attr' => array('class' => 'datepicker-only-year form-control'),
            'attr' => array('class'=>'datepicker-only-year form-control', 'title'=>'Start Year', 'data-toggle'=>'tooltip'),
        ));

//        $builder->add('filter', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:FellowshipSubspecialty',
//            //'placeholder' => 'Fellowship Type',
//            'property' => 'name',
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

        $builder->add('filter', 'choice', array(
            'label' => false,
            'required'=> false,
            //'multiple' => false,
            'choices' => $this->params['fellTypes'],
            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty-filter'),
        ));
        
        $builder->add('search', 'text', array(
            //'placeholder' => 'Search',
            'max_length'=>200,
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));


        $builder->add('hidden', 'checkbox', array(
            'required'=>false,
            'label' => 'Hidden',
        ));

        $builder->add('archived', 'checkbox', array(
            'required'=>false,
            'label' => 'Archived',
        ));

        $builder->add('complete', 'checkbox', array(
            'required'=>false,
            'label' => 'Complete',
        ));

        $builder->add('interviewee', 'checkbox', array(
            'required'=>false,
            'label' => 'Interviewee',
        ));

        $builder->add('active', 'checkbox', array(
            'required'=>false,
            'label' => 'Active',
        ));

        $builder->add('reject', 'checkbox', array(
            'required'=>false,
            'label' => 'Rejected',
        ));

//        $builder->add('onhold', 'checkbox', array(
//            'required'=>false,
//            'label' => 'On Hold',
//        ));

        $builder->add('priority', 'checkbox', array(
            'required'=>false,
            'label' => 'Priority',
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

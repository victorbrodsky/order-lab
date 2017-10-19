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

        $builder->add( 'projectSpecialty', EntityType::class, array(
            'class' => 'OlegTranslationalResearchBundle:SpecialtyList',
            //'choice_label' => 'name',
            'label' => false,   //'Project Specialty',
            //'disabled' => ($this->params['admin'] ? false : true),
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
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
        
        $builder->add('search', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

        $builder->add('state',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['stateChoiceArr'],
            'attr' => array('class' => 'combobox'),
        ));

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

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

namespace App\UserdirectoryBundle\Form;


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class LectureType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('id',HiddenType::class,array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));


        $builder->add('lectureDate', DateType::class, array(
            'label' => 'Lecture Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));


        $builder->add( 'importance', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:ImportanceList',
            'label'=> "Importance:",
            'required'=> false,
            'multiple' => false,
            'choice_label' => 'name',
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

        $builder->add('title',null,array(
            'label'=>'Lecture Title:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('organization', CustomSelectorType::class, array(
            'label' => 'Institution:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-organization', 'type' => 'hidden'),
            'classtype' => 'organization'
        ));

        $builder->add('city', CustomSelectorType::class, array(
            'label' => 'Lecture City:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-city', 'type' => 'hidden'),
            'classtype' => 'city'
        ));

        //state
        //$defaultState = null;
        //$defaultState = $this->params['em']->getRepository('AppUserdirectoryBundle:States')->findOneByName('New York');
        $builder->add( 'state', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:States',
            //'choice_label' => 'name',
            'label'=>'Lecture State:',
            'required'=> false,
            'multiple' => false,
            //'data' => $defaultState,
            'attr' => array('class'=>'combobox combobox-width geo-field-state'),
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

        //country
        //$defaultCountry = null;
        //$defaultCountry = $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findOneByName('United States');
        $preferredCountries = $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findByName(array('United States'));
        $builder->add( 'country', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Countries',
            'choice_label' => 'name',
            'label'=>'Lecture Country:',
            'required'=> false,
            'multiple' => false,
            //'data' => $defaultCountry,
            'preferred_choices' => $preferredCountries,
            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
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




    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Lecture',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_lecture';
    }
}

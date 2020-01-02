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

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class PublicationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
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


        $builder->add('publicationDate',CustomSelectorType::class, array(
            'label' => 'Publication Month and Year:',
            'required' => false,
            'attr' => array('class' => 'datepicker-exception form-control'),
            'classtype' => 'month_year_date_only'
        ));

        if( $this->params['cycle'] == "show" ) {
            $builder->add('updatedate', DateType::class, array(
                //'disabled' => true,
                'label' => 'Update Date:',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control', 'readonly' => true),
            ));
        }

        $builder->add('citation', TextareaType::class, array(
            'required' => false,
            'label'=>'Citation / Reference:',
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('pubmedid', null, array(
            'label' => 'PubMed ID:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('link', null, array(
            'label' => 'PubMed or Relevant Link:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add( 'importance', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:ImportanceList',
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






    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Publication',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_publication';
    }
}

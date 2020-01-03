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

namespace App\OrderformBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EncounterPatsexType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        $builder->add( 'field', 'choice', array(
//            'label'=>"Patient's Sex (at the time of encounter)",
//            'choices' => array("Female"=>"Female", "Male"=>"Male", "Unspecified"=>"Unspecified"),
//            'multiple' => false,
//            'expanded' => true,
//            'attr' => array('class' => 'horizontal_type encountersex-field')
//        ));

//        $builder->add( 'field', null, array(
//            'label' => "Patient's Sex (at the time of encounter)",
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width encountersex-field')
//        ));

        $builder->add( 'field', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:SexList',
            'choice_label' => 'name',
            'label' => "Patient's Gender (at the time of encounter):",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width encountersex-field'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added'
                        ));
                },
        ));

        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\EncounterPatsex',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\EncounterPatsex',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_encounterpatsex';
    }
}

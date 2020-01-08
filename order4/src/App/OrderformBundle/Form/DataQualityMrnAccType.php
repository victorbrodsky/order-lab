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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataQualityMrnAccType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $type = TextType::class; //'hidden';

        $builder->add( 'btnoption', ChoiceType::class, array( //flipped
            'label'=>'MRN-ACCESSION CONFLICT',
            //'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
            'choices' => array("TEXT1"=>"OPTION1", "TEXT2"=>"OPTION2", "TEXT3"=>"OPTION3"),
            //'choices_as_values' => true,
            'multiple' => false,
            'expanded' => true,
            'mapped' => false,
            'required' => true,
            'attr' => array('required'=>'required')
        ));

        //description
        $builder->add( 'description', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //accession
        $builder->add( 'accession', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //accession type
        $builder->add( 'accessiontype', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //mrn
        $builder->add( 'mrn', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));


        //mrn types
        $builder->add( 'mrntype', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
//        $resolver->setDefaults(array(
//            'data_class' => 'App\OrderformBundle\Entity\DataQualityMrnAcc',
//        ));
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'dataqualitymrnacc';
    }
}

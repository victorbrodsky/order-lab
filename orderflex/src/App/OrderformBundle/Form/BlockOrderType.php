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

use App\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentContainerType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class BlockOrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('processedDate', DateType::class, array(
            'label' => "Processed Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedByUser', null, array(
            'label' => 'Block Processed By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));


        //Block Image container
//        $params = array('labelPrefix'=>'Block Image');
//        $builder->add('documentContainer', new DocumentContainerType($params), array(
//            'data_class' => 'App\UserdirectoryBundle\Entity\DocumentContainer',
//            'label' => false
//        ));

//        $params = array('labelPrefix'=>' for Embedder');
//        $builder->add('instruction', new InstructionType($params), array(
//            'data_class' => 'App\OrderformBundle\Entity\InstructionList',
//            'label' => false
//        ));

        //EmbedderInstructionList
        $builder->add('embedderInstruction', ScanCustomSelectorType::class, array(
            'label' => 'Instructions for Embedder:',
            'attr' => array('class' => 'ajax-combobox-embedderinstruction', 'type' => 'hidden'),
            'required'=>true,
            'classtype' => 'embedderinstruction'
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\BlockOrder',
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_blockordertype';
    }
}

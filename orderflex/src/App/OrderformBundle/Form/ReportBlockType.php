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

use App\UserdirectoryBundle\Form\DocumentContainerType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ReportBlockType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('issuedDate', DateType::class, array(
            'label' => "Issued Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('receivedDate', DateType::class, array(
            'label' => "Received Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('signatureDate', DateType::class, array(
            'label' => "Signature Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedDate', DateType::class, array(
            'label' => "Processed Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedByUser', null, array(
            'label' => 'Block Processed By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        $builder->add('embeddedDate', DateType::class, array(
            'label' => "Embedded Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('embeddedByUser', null, array(
            'label' => 'Block Embedded By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\ReportBlock',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_orderformbundle_reportblocktype';
    }
}

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

use App\UserdirectoryBundle\Entity\Identifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ExaminationType extends AbstractType
{

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('scores', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        $builder->add('USMLEStep1DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep1Score', null, array(
            'label' => 'Score:',
            //'attr' => array('class'=>'form-control digit-mask')
            //USMLE Step 1 results are now reported a pass/fail only, as the exam transitioned to this format in January 2022.
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('USMLEStep1Percentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('USMLEStep2CKDatePassed', null, array(
            'label' => 'CK - Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep2CKScore', null, array(
            'label' => 'CK - Score (optional):',
            'attr' => array('class'=>'form-control digit-mask')
        ));
        $builder->add('USMLEStep2CKPercentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('USMLEStep2CSDatePassed', null, array(
            'label' => 'CS - Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep2CSScore', null, array(
            'label' => 'CS - Score (optional):',
            'attr' => array('class'=>'form-control digit-mask')
        ));
        $builder->add('USMLEStep2CSPercentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('USMLEStep3DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep3Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control digit-mask')
        ));
        $builder->add('USMLEStep3Percentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('ECFMGCertificateNumber', null, array(
            'label' => 'ECFMG Certificate Number:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('ECFMGCertificateDate', null, array(
            'label' => 'Date ECFMG Certificate Granted:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('ECFMGCertificate', CheckboxType::class, array(
            'label' => false,
            'attr' => array('class'=>'form-control fellapp-ecfmgcertificate-field', 'onclick'=>'showHideWell(this)')
        ));


        $builder->add('COMLEXLevel1DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        //COMLEX Level 1 result is pass/fail since May 10, 2022.
        $builder->add('COMLEXLevel1Score', null, array(
            'label' => 'Score:',
            //'attr' => array('class'=>'form-control digit-mask')
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('COMLEXLevel1Percentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('COMLEXLevel2DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('COMLEXLevel2Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control digit-mask')
        ));
        $builder->add('COMLEXLevel2Percentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('COMLEXLevel3DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('COMLEXLevel3Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control digit-mask')
        ));
        $builder->add('COMLEXLevel3Percentile', null, array(
            'label' => 'Percentile:',
            'attr' => array('class'=>'form-control')
        ));


    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Examination',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_examination';
    }
}

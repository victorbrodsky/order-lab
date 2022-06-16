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

namespace App\ResAppBundle\Form;

use App\UserdirectoryBundle\Entity\Identifier;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ResAppExaminationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //USMLE
        $builder->add('USMLEStep1Score', null, array(
            'label' => 'USMLE Scores Step 1:',
            'attr' => array('class'=>'form-control digit-mask')
        ));

        $builder->add('USMLEStep2CKScore', null, array(
            'label' => 'USMLE Scores Step 2 CK:',
            'attr' => array('class'=>'form-control digit-mask')
        ));

        $builder->add('USMLEStep2CSScore', null, array(
            'label' => 'USMLE Scores Step 2 CS (Pass/Fail):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('USMLEStep3Score', null, array(
            'label' => 'USMLE Scores Step 3:',
            'attr' => array('class'=>'form-control digit-mask')
        ));

        //COMLEX
        $builder->add('COMLEXLevel1Score', null, array(
            'label' => 'COMLEX Level 1 Score:',
            'attr' => array('class'=>'form-control digit-mask')
        ));

        $builder->add('COMLEXLevel2Score', null, array(
            'label' => 'COMLEX Level 2 Score:',
            'attr' => array('class'=>'form-control digit-mask')
        ));

        $builder->add('COMLEXLevel2PEScore', null, array(
            'label' => 'COMLEX Level 2 PE Score (Pass/Fail):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('COMLEXLevel3Score', null, array(
            'label' => 'COMLEX Level 3 Score:',
            'attr' => array('class'=>'form-control digit-mask')
        ));
    }

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

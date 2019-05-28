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

namespace Oleg\OrderformBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EncounterInpatientinfoType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('source', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => 'Inpatient Info Source System:',
            'required' => false,
            'data'  => null,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        //->where("list.name = 'WCM Epic Ambulatory EMR' OR list.name = 'Written or oral referral'")
                        ->orderBy("list.orderinlist","ASC");

                },
        ));

        $builder->add('admissiondate',DateTimeType::class,array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Admission Date:',
        ));

        $builder->add('admissiontime', TimeType::class, array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Admission Time:'
        ));

        $builder->add('admissiondiagnosis',null,array(
            'required' => false,
            'label'=>'Diagnosis on Admission:',
            //'attr' => array('class' => 'form-control'),
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('dischargedate',DateTimeType::class,array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Discharge Date:',
        ));

        $builder->add('dischargetime', TimeType::class, array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Discharge Time:'
        ));

        $builder->add('dischargediagnosis',null,array(
            'required' => false,
            'label'=>'Diagnosis on Discharge:',
            'attr' => array('class'=>'textarea form-control')
        ));


        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterInpatientinfo',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterInpatientinfo',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_encounterinpatientinfotype';
    }
}

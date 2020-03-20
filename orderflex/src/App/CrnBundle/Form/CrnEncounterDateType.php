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

namespace App\CrnBundle\Form;

use App\OrderformBundle\Form\ArrayFieldType;
use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrnEncounterDateType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('field', DateType::class, array(
            'label' => "Encounter Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control encounter-date', 'style'=>'margin-top: 0;'),
        ));

        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\EncounterDate',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

        //extra data-structure fields
        //echo "encounter time <br>";
        $builder->add('time', TimeType::class, array(
            'input' => 'datetime',
            'widget' => 'choice',
            'label' => 'Encounter Time:',
            //'disabled' => $this->params['readonlyEncounter']
            'attr' => array('readonly'=>$this->params['readonlyEncounter'])
        ));

        //timezone 'choice'
        $tzUtil = new TimeZoneUtil();
        $builder->add('timezone', ChoiceType::class, array(
            //'label' => false,
            'label' => "Time zone:",
            'choices' => $tzUtil->tz_list(),
            //'choices_as_values' => true,
            'required' => true,
            'data' => $this->params['timezoneDefault'],
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\EncounterDate',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_encounterdatetype';
    }
}

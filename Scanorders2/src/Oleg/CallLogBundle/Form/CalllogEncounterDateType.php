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

namespace Oleg\CallLogBundle\Form;

use Oleg\OrderformBundle\Form\ArrayFieldType;
use Oleg\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogEncounterDateType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('field', 'date', array(
            'label' => "Encounter Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control encounter-date', 'style'=>'margin-top: 0;'),
        ));

        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterDate',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

        //extra data-structure fields
        //echo "encounter time <br>";
        $builder->add('time', 'time', array(
            'input' => 'datetime',
            'widget' => 'choice',
            'label' => 'Encounter Time:',
            'disabled' => $this->params['readonlyEncounter']
        ));

        //timezone 'choice'
        $tzUtil = new TimeZoneUtil();
        $builder->add('timezone', 'choice', array(
            'label' => false,
            'choices' => $tzUtil->tz_list(),
            'required' => true,
            'data' => $this->params['timezoneDefault'],
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterDate',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterdatetype';
    }
}

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

namespace App\VacReqBundle\Form;


use App\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestBaseType extends AbstractType
{

    protected $params;

    protected $requestTypeName;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //Symfony<2.8 read_only' => true, Symfony>2.8 'attr' => ['readonly' => true], or use disabled
        $builder->add('startDate', DateType::class, array(
            'label' => $this->requestTypeName.' - First Day Away:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control vacreq-startDate'), //datepicker-nocleardate datepicker-onclear-cleartooltip
            'disabled' => ($this->params['review'] ? true : false)
        ));

        $builder->add('endDate', DateType::class, array(
            'label' => $this->requestTypeName.' - Last Day Away:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control vacreq-endDate'),
            'disabled' => ($this->params['review'] ? true : false)
        ));


        $builder->add('numberOfDays', null, array(
            'label' => $this->numberOfDaysLabelPrefix . ' (Please do not include '.$this->params['holidaysUrl'].'):',
            'attr' => array('class'=>'form-control vacreq-numberOfDays'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

//        $builder->add('firstDayBackInOffice', 'date', array(
//            'label' => 'First Day Back in Office:',
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control vacreq-firstDayBackInOffice'),
//            'disabled' => ($this->params['review'] ? true : false)
//        ));

//        if( $this->params['cycle'] == 'edit' || $this->params['cycle'] == 'show' ) {
//            $builder->add('approverComment', TextareaType::class, array(
//                'label' => 'Approver Comment:',
//                'required' => false,
//                'attr' => array('class' => 'textarea form-control'),
//            ));
//        }

        if( $this->params['cycle'] == 'review' || $this->params['cycle'] == 'show' ) {

            //enable status radio only for admin or for reviewer
            $readOnly = true;
            if( $this->params['review'] === true || $this->params['roleAdmin'] || $this->params['roleApprover'] ) {
                $readOnly = false;
            }

            $builder->add('status', ChoiceType::class, array( //flipped
                'disabled' => $readOnly,    //($this->params['roleAdmin'] ? false : true),
//                'choices' => array(
//                    'pending' => 'Pending',
//                    'approved' => 'Approved',
//                    'rejected' => 'Rejected'
//                ),
                'choices' => array(
                    'Pending' => 'pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected'
                ),
                //'choices_as_values' => true,
                'label' => false,   //"Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                'attr' => array('class' => 'horizontal_type_wide'), //horizontal_type
            ));

            $builder->add('approverComment', TextareaType::class, array(
                'label' => 'Approver Comment:',
                'disabled' => $readOnly,
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));
        }

    }

}

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

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class VacReqSiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);


        $builder->add('academicYearStart',null,array(
            'label'=>'Academic Year Start (July 1st):',
            //'attr' => array('class'=>'datepicker form-control datepicker-day-month')
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('academicYearEnd',null,array(
            'label'=>'Academic Year End (June 30th):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('holidaysUrl',null,array(
            'label'=>'Link to list of holidays (http://intranet.med.cornell.edu/hr/):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('vacationAccruedDaysPerMonth',null,array(
            'label'=>'Vacation days accrued per month by faculty (2):',
            'attr' => array('class'=>'form-control')
        ));

//        $builder->add('maxVacationDays', null, array(
//            'label' => 'Maximum number vacation days per year (usually 12*2=24 days):',
//            'attr' => array('class' => 'form-control')
//        ));

        $builder->add('maxCarryOverVacationDays', null, array(
            'label' => 'Maximum number of carry over vacation days per year (usually 15):',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('noteForVacationDays', null, array(
            'label' => 'Note for vacation days:',
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('noteForCarryOverDays', null, array(
            'label' => 'Note for carry over vacation days:',
            'attr' => array('class' => 'textarea form-control')
        ));

        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Submit',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\VacReqBundle\Entity\VacReqSiteParameter',
            'form_custom_value' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_vacreqbundle_vacreqsiteparameter';
    }
}

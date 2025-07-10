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



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Form\DocumentType;
use Doctrine\ORM\EntityRepository;
//use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
//use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;

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
            'label'=>'Vacation Academic Year Start (July 1st):',
            //'attr' => array('class'=>'datepicker form-control datepicker-day-month')
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('academicYearEnd',null,array(
            'label'=>'Vacation Academic Year End (June 30th):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('holidaysUrl',null,array(
            'label'=>'Link to list of holidays (http://intranet.med.cornell.edu/hr/):',
            'attr' => array('class'=>'form-control')
        ));

        ////////// TODO: Moved to the VacReqApprovalTypeList (can be deleted) //////////////
//        $builder->add('vacationAccruedDaysPerMonth',null,array(
//            'label'=>'Vacation days accrued per month by faculty (2):',
//            'attr' => array('class'=>'form-control')
//        ));
//        $builder->add('maxVacationDays', null, array(
//            'label' => 'Maximum number vacation days per year (usually 12*2=24 days):',
//            'attr' => array('class' => 'form-control')
//        ));
//        $builder->add('maxCarryOverVacationDays', null, array(
//            'label' => 'Maximum number of carry over vacation days per year (for example 15):',
//            'attr' => array('class' => 'form-control')
//        ));
//        $builder->add('noteForVacationDays', null, array(
//            'label' => 'Note for vacation days:',
//            'attr' => array('class' => 'textarea form-control')
//        ));
//        $builder->add('noteForCarryOverDays', null, array(
//            'label' => 'Note for carry over vacation days:',
//            'attr' => array('class' => 'textarea form-control')
//        ));
        ////////// EOF Moved to the VacReqApprovalTypeList //////////////

        $builder->add('floatingDayName', null, array(
            'label' => 'Floating Day Link Name (i.e. Floating Day):',
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('floatingDayNote', null, array(
            'label' => 'Floating Day Note:',
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('floatingRestrictDateRange', CheckboxType::class, array(
            'label' => 'Restrict Floating Date Range:',
            'required' => false,
            //'attr' => array('class' => 'form-control')
        ));
        $builder->add('enableFloatingDay', CheckboxType::class, array(
            'label' => 'Enable Floating Day Requests (show/hide new floating day page link):',
            'required' => false,
            //'attr' => array('class' => 'form-control')
        ));

//        $builder->add('holidayDatesUrl', null, array(
//            'label' => 'URL for US Holiday dates in iCal format:',
//            'required' => false,
//            'attr' => array('class' => 'textarea form-control holidayDatesUrl')
//        ));

        $builder->add('institutions', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            'class' => Institution::class,
            'choice_label' => 'getNameAndId', //name
            'label'=>'Instance maintained for the following institutions (Holiday\'s default institutions):',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        $builder->add('intTravelNote', null, array(
            'label' => 'International Travel Registry Note:',
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('enableTravelIntakeForm', CheckboxType::class, array(
            'label' => 'Enable Travel intake form:',
            'required' => false,
            //'attr' => array('class' => 'form-control')
        ));
        
        //titleTravelIntakeForm
        //Title of travel intake form accordion: [Travel intake form for Spend Control Committee approval]
        $builder->add('titleTravelIntakeForm', null, array(
            'label' => 'Title of travel intake form accordion (i.e. Travel intake form for Spend Control Committee approval): ',
            'attr' => array('class' => 'textarea form-control')
        ));

        //travelIntakePdfs
        $builder->add('travelIntakePdfs', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Travel intake form PDF:',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
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

    public function getBlockPrefix(): string
    {
        return 'oleg_vacreqbundle_vacreqsiteparameter';
    }
}

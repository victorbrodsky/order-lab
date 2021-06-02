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

namespace App\FellAppBundle\Form;

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

class FellappSiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);


        $builder->add('acceptedEmailSubject', null, array(
            'label' => 'Subject of e-mail to the accepted applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('acceptedEmailBody', null, array(
            'label' => 'Body of e-mail to the accepted applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));

        $builder->add('rejectedEmailSubject', null, array(
            'label' => 'Subject of e-mail to the rejected applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('rejectedEmailBody', null, array(
            'label' => 'Body of e-mail to the rejected applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));

        $builder->add('fellappAcademicYearStart',null,array(
            'label'=>'Application season start date (MM/DD) when the default fellowship application year changes to the following year (i.e. April 1st):',
            'attr' => array('class'=>'datepicker form-control datepicker-day-month')
//            'attr' => array('class'=>'form-control')
        ));

        $builder->add('fellappAcademicYearEnd',null,array(
            'label'=>'Application season end date (MM/DD) when the default fellowship application year changes to the following year, if empty set to start date -1 day (i.e. March 31):',
            'attr' => array('class'=>'form-control')
        ));



        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }



    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\FellAppBundle\Entity\FellappSiteParameter',
            'form_custom_value' => null,
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_fellappbundle_fellappsiteparameter';
    }
}

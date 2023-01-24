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
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VacReqHolidayFilterType extends AbstractType
{

    private $params;


    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('years', TextType::class, array(
            'label' => false, //'Start Date',
            'required' => false,
            //'data' => $startDates, //$this->params['defaultStartDates'],
            'attr' => array('class'=>'datepicker-only-year datepicker-multidate form-control', 'title'=>'Start Year', 'data-toggle'=>'tooltip'),
        ));

//        $builder->add('years', DateType::class, array(
//            'label' => false, //'Start Date/Time:',
//            'required' => false,
//            //'widget' => 'single_text',
//            //'format' => 'MM/dd/yyyy',
//            //'html5' => false,
//            'attr' => array('class' => 'datepicker-only-year datepicker-multidate form-control', 'placeholder' => 'Years', 'title'=>'Years', 'data-toggle'=>'tooltip')
//        ));

//        $builder->add('endYear', DateType::class, array(
//            'label' => false, //'Start Date/Time:',
//            'required' => false,
//            'widget' => 'single_text',
//            'format' => 'MM/dd/yyyy',
//            'html5' => false,
//            'attr' => array('class' => 'form-control datetimepicker', 'placeholder' => 'End Year', 'title'=>'End Year', 'data-toggle'=>'tooltip')
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}

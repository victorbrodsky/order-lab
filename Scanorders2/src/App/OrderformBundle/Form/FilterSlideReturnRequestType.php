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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSlideReturnRequestType extends AbstractType
{

    protected $status;

    public function formConstructor( $status = null )
    {
        $this->status = $status;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        $choices = array(   'all' => 'All',
//                            'active' => 'Active',
//                            'All Scanned & All Returned' => 'All Scanned & All Returned',
//                            'Some Scanned & All Returned' => 'Some Scanned & All Returned',
//                            'Not Scanned & All Returned' => 'Not Scanned & All Returned',
//                            'Checked: Not Received' => 'Checked: Not Received',
//                            'Checked: Previously Returned' => 'Checked: Previously Returned',
//                            'Checked: Some Returned' => 'Checked: Some Returned',
//                            'cancel' => 'Canceled'
//                        );
        $choices = array(
            'All' => 'all',
            'Active' => 'active',
            'All Scanned & All Returned' => 'All Scanned & All Returned',
            'Some Scanned & All Returned' => 'Some Scanned & All Returned',
            'Not Scanned & All Returned' => 'Not Scanned & All Returned',
            'Checked: Not Received' => 'Checked: Not Received',
            'Checked: Previously Returned' => 'Checked: Previously Returned',
            'Checked: Some Returned' => 'Checked: Some Returned',
            'Canceled' => 'cancel'
        );

        $builder->add('filter', ChoiceType::class, //flipped
            array(
                //'mapped' => false,
                'label' => false,
                //'preferred_choices' => array($this->status),
                'attr' => array('class' => 'combobox combobox-width'),
                'choices' => $choices,
                //'choices_as_values' => true,
            )
        );
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        //$resolver->setDefaults(array(
            //'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        //));
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter_search_box';
    }
}

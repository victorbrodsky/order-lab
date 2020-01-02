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

use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class SlideReturnRequestType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        if( $params ) $this->params = $params;

        $labels = array(
            'institution' => 'Institution:',
            'destinations.location' => 'Return Slides to:',
        );

        $this->params['labels'] = $labels;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $this->params['slide'] = true;
        $builder->add('message', MessageObjectType::class, array(
            'form_custom_value' => $this->params,
            'form_custom_value_entity' => null,
            'data_class' => 'Oleg\OrderformBundle\Entity\Message',
            'label' => false
        ));



        $builder->add('urgency', ScanCustomSelectorType::class, array(
            'label' => 'Urgency:',
            'attr' => array('class' => 'ajax-combobox-urgency', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'urgency'
        ));


        if( array_key_exists('type', $this->params) &&  $this->params['type'] == 'table' ) {

            //echo "type=table <br>";

            $builder->add('returnoption', CheckboxType::class, array(
                'label'     => 'Return all slides that belong to listed accession numbers:',
                'required'  => false,
            ));

            $builder->add('datalocker',HiddenType::class, array(
                'mapped' => false,
                'label' => false,
                'attr' => array('class' => 'slidereturnrequest-datalocker-field')
                //'required'  => false,
            ));

        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SlideReturnRequest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_slidereturnrequesttype';
    }
}

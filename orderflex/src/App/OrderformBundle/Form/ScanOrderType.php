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

namespace App\OrderformBundle\Form;

use App\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ScanOrderType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        if( $params ) $this->params = $params;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        if( $this->params['cycle'] == 'show' ) {
//            //echo "entity service";
//            $builder->add('service', 'entity', array(
//                'label' => 'Service:',
//                'required'=> false,
//                'multiple' => false,
//                'class' => 'AppUserdirectoryBundle:Service',
//                //'choices' => $this->params['services'],
//                'attr' => array('class' => 'combobox combobox-width')
//            ));
//        } else {
//            //service. User should be able to add institution to administrative or appointment titles
//            $builder->add('service', 'employees_custom_selector', array(
//                'label' => "Service:",
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
//                'classtype' => 'service'
//            ));
//        }

        //Default Originating Institution
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $title ) {
                $institution = $title->getScanOrderInstitutionScope();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
			if( !$label ) {
                $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
            }

            $form->add('scanOrderInstitutionScope', CustomSelectorType::class, array(
                //'label' => 'ScanOrder' . ' ' . $label . ' Scope' . ':',
                'label' => $label,  //"Originating Organizational Group ".$label,
                'required' => false,

                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    //'data-label-prefix' => 'ScanOrder',
                    //'data-label-postfix' => 'Scope'
                    'data-label-prefix' => '',  //'Originating Organizational Group',
                    'data-label-postfix' => ''
                ),
                'classtype' => 'institution'
            ));
        });

        //delivery
        $attr = array('class' => 'ajax-combobox-delivery', 'type' => 'hidden');
        $builder->add('delivery', ScanCustomSelectorType::class, array(
            'label' => 'Slide Delivery:',
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'delivery'
        ));

        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\ScanOrder',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_scanordertype';
    }
}

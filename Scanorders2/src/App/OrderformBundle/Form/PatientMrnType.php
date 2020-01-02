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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PatientMrnType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['type'] == 'One-Slide Scan Order') {
            $attr['style'] = 'width:100%';
            $mrnTypeLabel = "MRN Type:";
            //$gen_attr = array('label'=>false,'class'=>'App\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $mrnTypeLabel = false;
            //$gen_attr = array('label'=>'Accession Number [or Label]','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        }

        $builder->add( 'field', TextType::class, array(
            'label'=>'MRN:',
            'required'=>false,
            'attr' => array('class' => 'form-control keyfield patientmrn-mask')
        ));

//        $attr = array('class' => 'combobox combobox-width mrntype-combobox');
//        $builder->add('keytype', 'entity', array(
//            'class' => 'OlegOrderformBundle:MrnType',
//            'label'=>false, //'MRN Type',
//            'required' => true,
//            'attr' => $attr,
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('s')
//                    ->orderBy('s.id', 'ASC');
//            },
//        ));

        //mrn types
        $attr = array('class' => 'ajax-combobox combobox combobox-width mrntype-combobox', 'type' => 'hidden'); //combobox
        $options = array(
            'label' => $mrnTypeLabel,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'mrntype',
        );

        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
            $userSecUtil = $this->params['serviceContainer']->get('user_security_utility');
            $defaultScanMrnType = $userSecUtil->getSiteSettingParameter('defaultScanMrnType');
            if( $defaultScanMrnType ) {
                $options['data'] = $defaultScanMrnType->getId();
            } else {
                $options['data'] = 1; //new
            }
        }

        $builder->add('keytype', ScanCustomSelectorType::class, $options);

        //other fields from abstract
        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_mrntype';
    }
}

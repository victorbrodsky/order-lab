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

class AccessionAccessionType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //accession number
        $attr = array(
            'class'=>'form-control form-control-modif keyfield accession-mask',
            //'title' => 'Example: S12-123456 or SS12-123456. Valid Accession#: A00-1 through ZZ99-999999',
        );

        if( $this->params['type'] == 'One-Slide Scan Order') {
            $attr['style'] = 'width:100%; height:27px';
            $accTypeLabel = "Accession Type:";
            //$gen_attr = array('label'=>false,'class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $accTypeLabel = false;
            //$gen_attr = array('label'=>'Accession Number [or Label]','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        }

        $builder->add( 'field', TextType::class, array(
            'label'=>'Accession Number [or Label]:',
            'required'=>false,
            'attr' => $attr
        ));

        //accession type
        $attr = array('class' => 'ajax-combobox combobox combobox-width accessiontype-combobox', 'type' => 'hidden'); //combobox
        $options = array(
            'label' => $accTypeLabel,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'accessiontype',
        );

        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
            $options['data'] = 1; //new
            $userSecUtil = $this->params['serviceContainer']->get('user_security_utility');
            $defaultScanAccessionType = $userSecUtil->getSiteSettingParameter('defaultScanAccessionType');
            if( $defaultScanAccessionType ) {
                $options['data'] = $defaultScanAccessionType->getId();
            } else {
                $options['data'] = 1; //new
            }
        }

        $builder->add('keytype', ScanCustomSelectorType::class, $options);


        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionAccession',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionAccession',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_accessionaccessiontype';
    }
}

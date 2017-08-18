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
use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\ListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class LabTestType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( strpos($this->params['cycle'],'_standalone') === false ) {
            $readonly = true;
            $standalone = false;
        } else {
            $readonly = false;
            $standalone = true;
        }

        $builder->add('labTestType', ScanCustomSelectorType::class, array(
            'label' => "Laboratory Test ID Type:",
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-labtesttype', 'type' => 'hidden'),
            'classtype' => 'labtesttype'
        ));

        $builder->add('labTestId', null, array(
            'required'=>false,
            'label'=>'Laboratory Test ID:',
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('name', null, array(
            'required'=>false,
            'label'=>"Laboratory Test Title:",
            'attr' => array('class'=>'form-control'),
        ));


        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( $standalone ) {
            //echo "list attributes for LabTest <br>";
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "LabTest";
            $mapper['bundleName'] = "OlegOrderformBundle";

            //ListType($params, $mapper)
            $builder->add('list', ListType::class, array(
                'form_custom_value' => $params,
                'form_custom_value_mapper' => $mapper,
                'data_class' => 'Oleg\OrderformBundle\Entity\LabTest',
                'label' => false
            ));
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\LabTest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_labtesttype';
    }
}

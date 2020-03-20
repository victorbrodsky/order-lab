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

namespace App\CrnBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;




class CrnMessageCacheType extends AbstractType
{

    protected $entity;
    protected $params;

    public function formConstructor( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);


        $builder->add('formnodesCache', null, array(
            'label' => 'Cache in XML:',
            'required' => true,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('patientMrnCache', null, array(
            'label' => 'Patient MRN Cache:',
            'required' => true,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('patientNameCache', null, array(
            'label' => 'Patient Name Cache:',
            'required' => true,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('update', SubmitType::class, array(
            'label' => 'Update Cache',
            'attr' => array('class' => 'btn')
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Message',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_crnformbundle_messagecachetype';
    }

}

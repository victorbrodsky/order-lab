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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProcedureNumberType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //$builder->add('field', 'hidden', array('label'=>false));
        $builder->add('field', null, array(
            'label'=>'Procedure Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('keytype', EntityType::class, array(
            'class' => 'AppOrderformBundle:ProcedureType',
            'label'=>false,
            'required' => true,
            //'data' => 1,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));



        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\ProcedureNumber',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\ProcedureNumber',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_procedurenumbertype';
    }
}

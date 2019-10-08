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

namespace Oleg\CallLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalllogTaskType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('id', HiddenType::class);

//        echo "User=".$this->params['user']."<br>";
//        $builder->add('createdBy', HiddenType::class, array(
//            'label' => false,
//            'required' => false,
//            'data' => $this->params['user'],
//            'attr' => array('class' => 'form-control'),
//        ));

        $builder->add('description', TextareaType::class, array(
            'label' => "Description:",
            'required' => false,
            'attr' => array('class' => 'form-control textarea'),
        ));

//        $builder->add('systemStatus', TextType::class, array(
//            'label' => "System Status:",
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));

        if( $this->params['cycle'] != 'new' ) {
            $builder->add('status', CheckboxType::class, array(
                'label' => 'Status:',
                'required' => false,
                'attr' => array('class' => 'form-control')
            ));
        }

        $builder->add('calllogTaskType', null, array(
            'label' => "Task Type:",
            'required' => false,
            'attr' => array('class' => 'combobox'),
        ));



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CalllogTask',
            'form_custom_value' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_calllogformbundle_calllogtasktype';
    }
}

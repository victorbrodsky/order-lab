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

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class InstructionType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add('instruction', 'text', array(
            'label' => 'Instruction'.$this->params['labelPrefix'].':',
            'attr' => array('class' => 'textarea form-control'),
        ));

        $builder->add('createdate', 'date', array(
            'label' => 'Instruction'.$this->params['labelPrefix'].' On:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('creator',null,array(
            'label'=>'Instruction'.$this->params['labelPrefix'].' Author:',
            'required' => false,
            'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
        ));


//        $builder->add('others', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\Instruction',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Instruction',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_instructiontype';
    }
}

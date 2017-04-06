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

namespace Oleg\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LabelType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('userlabel', 'textarea', array(
            //'placeholder' => 'Enter Label Text',
            //'max_length' => 200,
            'required' => false,
            'label' => "Label Text (use <br> tag for a new line):",
            'data' => $this->params['label'],
            'attr' => array('class' => 'textarea form-control'),
        ));

        $builder->add('startrow', 'number', array(
            'required' => true,
            'label' => "Start Row Index:",
            'data' => 1,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('endrow', 'number', array(
            'required' => true,
            'label' => "End Row Index:",
            'data' => 10,
            'attr' => array('class' => 'form-control'),
        ));


        $builder->add('print', SubmitType::class, array(
            'label' => 'Print Internal Mailing Label',
            'attr' => array('class' => 'btn btn-success'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'label';
    }

}

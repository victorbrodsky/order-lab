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

namespace App\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LabelType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['singleUser'] ) {
            $builder->add('userlabel', TextareaType::class, array(
                //'placeholder' => 'Enter Label Text',
                //'max_length' => 200,
                'required' => false,
                'label' => "Label text (use '<br>' tag for a new line):",
                'data' => $this->params['label'],
                'attr' => array('class' => 'textarea form-control'),
            ));

            $builder->add('labelcount', IntegerType::class, array(
                'required' => true,
                'label' => "Number of labels to print (0 - for a whole page):",
                'data' => 1,
                'attr' => array('class' => 'form-control'),
            ));
        } else {
//            $builder->add('users', 'choice', array(
//                //'required' => true,
//                'label' => "Users:",
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Users'),
//                'choices' => $this->params['users']
//            ));

            //echo "user count=".count($this->params['users'])."<br>";

            $builder->add('users', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Users:",
                'choice_label' => "getUsernameOptimal",
                'multiple' => true,
                'required' => true,
                'attr' => array('class' => 'combobox combobox-width users', 'placeholder' => 'Users'),
                'choices' => $this->params['allusers'],
                'data' => $this->params['users']
            ));

        }

        $builder->add('startrow', IntegerType::class, array(
            'required' => true,
            'label' => "Start row index (from 1 to 10):",
            'data' => 1,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('startcolumn', IntegerType::class, array(
            'required' => true,
            'label' => "Start column index (from 1 to 3):",
            'data' => 1,
            'attr' => array('class' => 'form-control'),
        ));

//        $builder->add('endrow', 'number', array(
//            'required' => true,
//            'label' => "End row index:",
//            'data' => 10,
//            'attr' => array('class' => 'form-control'),
//        ));

        $builder->add('print', SubmitType::class, array(
            'label' => 'Print Internal Mailing Label',
            'attr' => array('class' => 'btn btn-success'),
        ));

        $builder->add('dotborders', CheckboxType::class, array(
            'required' => false,
            'label' => "Include dot borders (use it for a full page preview):",
            'data' => false,
            'attr' => array('class' => 'form-control'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'label';
    }

}

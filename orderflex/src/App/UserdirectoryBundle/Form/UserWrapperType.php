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


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;



class UserWrapperType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('labelPrefix', $this->params) ) {
            $this->params['labelPrefix'] = '';
        }

        if( !array_key_exists('name.label', $this->params) ) {
            $this->params['name.label'] = 'Original as entered '.$this->params['labelPrefix'].':';
        }

        if( !array_key_exists('user.label', $this->params) ) {
            $this->params['user.label'] = 'Mapped in DB '.$this->params['labelPrefix'].':';
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('name', null, array(
            'label' => $this->params['name.label'],
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add( 'user', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label' => $this->params['user.label'],
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {

                    if( array_key_exists('user.criterion', $this->params) ) {
                        $criterion = $this->params['user.criterion'];
                    } else {
                        $criterion = '';
                    }

                    return $er->createQueryBuilder('user')
                        ->where($criterion)
                        ->leftJoin("user.infos","infos")
                        ->orderBy("infos.displayName","ASC");
                },
        ));
//        $builder->add('user', null, array(
//            'label' => $this->params['user.label'],
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\UserWrapper',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_userwrappertype';
    }
}

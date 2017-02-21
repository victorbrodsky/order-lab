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


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;



class UserWrapperType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

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

        $builder->add('name', null, array(
            'label' => $this->params['name.label'],
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add( 'user', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserWrapper',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userwrappertype';
    }
}

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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/4/16
 * Time: 1:06 PM
 */

namespace App\UserdirectoryBundle\Form;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SimpleUserType extends UserType {


    public function formConstructor( $params )
    {
        $this->params = $params;

        $this->cycle = $params['cycle'];
        $this->readonly = $params['readonly'];
        //$this->sc = $params['sc'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $defaultPrimaryPublicUserIdType = null;
        if( isset($this->params['defaultPrimaryPublicUserIdType']) ) {
            $defaultPrimaryPublicUserIdType = $this->params['defaultPrimaryPublicUserIdType'];
        }
        //echo "defaultPrimaryPublicUserIdType=$defaultPrimaryPublicUserIdType<br>";

        //keytype
        //$this->addKeytype($builder,'Primary Public User ID Type:','combobox combobox-width',$defaultPrimaryPublicUserIdType);

//        $builder->add('keytype', EntityType::class, array(
//            'class' => 'AppUserdirectoryBundle:UsernameType',
//            //'disabled' => ($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
//            'choice_label' => 'name',
//            'label' => "Primary Public User ID Type:",
//            'required' => true,
//            'multiple' => false,
//            'data' => $defaultPrimaryPublicUserIdType,
//            'attr' => array('class'=>'combobox combobox-width user-keytype-field'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));

        $builder->add('keytype', ChoiceType::class, array(
            'label' => "Primary Public User ID Type:",
            //'required' => true,
            'multiple' => false,
            'choices' => $this->params['keytypeChoices'],
            //'choices_as_values' => true,
            'data' => $defaultPrimaryPublicUserIdType,
            'attr' => array('class' => 'combobox combobox-width'),
        ));


//        $readOnly = true;
//        if( $this->cycle == 'create' || $this->sc->isGranted('ROLE_PLATFORM_ADMIN') ) {
//            $readOnly = false;
//        }

        $builder->add('primaryPublicUserId', null, array(
            'label' => 'Primary Public User ID:',
            //'disabled' => $this->readonly,
            'attr' => array('class'=>'form-control submit-on-enter-field')
        ));

    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\User',
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return null;
        //return 'oleg_userdirectorybundle_user';
    }

} 
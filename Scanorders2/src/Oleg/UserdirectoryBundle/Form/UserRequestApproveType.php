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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserRequestApproveType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'id', 'hidden' );

        $builder->add( 'username', 'text', array(
            'label'=>false,
            'required'=> true,
            'attr' => array('class'=>'username'),
        ));


        if( array_key_exists('requestedScanOrderInstitutionScope', $this->params) ) {
            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
        } else {
            $requestedScanOrderInstitutionScope = null;
        }
        //echo "choices=".count($requestedScanOrderInstitutionScope)."<br>";
        $builder->add('requestedScanOrderInstitutionScope', 'entity', array(
            'label' => 'Organizational Group:',
            'required'=> false,
            'multiple' => false,
            //'empty_value' => false,
            'property' => 'getNodeNameWithRoot',
            'class' => 'OlegUserdirectoryBundle:Institution',
            //'choices' => $requestedScanOrderInstitutionScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));

        $builder->add('roles', 'choice', array(
            'choices' => $this->params['roles'],
            'label' => false,   //ucfirst($this->params['sitename']) . ' Role(s):',
            'attr' => array('class' => 'combobox combobox-width'),
            'multiple' => true,
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserRequest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_userrequesttype';
    }
}

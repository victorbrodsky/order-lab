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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserRequestApproveType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

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
            'choices' => $requestedScanOrderInstitutionScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\UserRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_userrequesttype';
    }
}

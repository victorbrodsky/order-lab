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



use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class InitialConfigurationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('environment','choice',array(
            'label'=>'Environment:',
            'choices' => array("live"=>"live", "test"=>"test", "dev"=>"dev"),
            'attr' => array('class'=>'form-control')
        ));

        //Name of Parent Organization (if applicable): [ ]
        $builder->add('institutionurl',null,array(
            'label'=>'Institution URL:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('institutionname',null,array(
            'label'=>'Institution Name:',
            'attr' => array('class'=>'form-control')
        ));

        //Name of Institution: [ ]
        $builder->add('subinstitutionurl',null,array(
            'label'=>'Sub Institution URL:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('subinstitutionname',null,array(
            'label'=>'Sub Institution Name:',
            'attr' => array('class'=>'form-control')
        ));

        //Name of Department or Group: [ ]
        $builder->add('departmenturl',null,array(
            'label'=>'Department URL:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('departmentname',null,array(
            'label'=>'Department or Group Name:',
            'attr' => array('class'=>'form-control')
        ));

        //New password for the Administrator account: [ ]
        $builder->add('password', 'repeated', array(
            'invalid_message' => 'Please make sure the passwords match',
            'options' => array('attr' => array('class' => 'password-field form-control')),
            'required' => true,
            'mapped' => false,
            'type' => 'password',
            'first_options'  => array('label' => 'New password for the Administrator account:'),
            'second_options' => array('label' => 'Repeat password:'),
        ));

        //E-Mail address for the Administrator account: [ ]
        $builder->add('siteEmail','email',array(
            'label'=>'E-Mail address for the Administrator account:',
            'attr' => array('class'=>'form-control user-email')
        ));

        //Live Site Root URL (such as "http://my.server.com/order"): [ ]
        $builder->add('liveSiteRootUrl',null,array(
            'label'=>'Live Site Root URL (such as "http://my.server.com/order"):',
            'attr' => array('class'=>'form-control')
        ));





        $builder->add('save', 'submit', array(
            'label' => 'Save',
            'attr' => array('class'=>'btn btn-primary')
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\SiteParameters',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_initialconfigurationtype';
    }
}

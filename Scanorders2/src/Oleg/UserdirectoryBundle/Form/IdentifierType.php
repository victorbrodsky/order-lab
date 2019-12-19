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

use Oleg\UserdirectoryBundle\Entity\Identifier;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class IdentifierType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        //only the "Platform Administrator" and "Deputy Platform Administrator" should be able to confirm the MRN by setting the Status of the MRN identifier as "Reviewed by Administration"
        if( $this->params['container']->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            $this->rolePlatformAdmin = true;
        } else {
            $this->rolePlatformAdmin = false;

        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //service. User should be able to add institution to administrative or appointment titles
        $builder->add('keytype', CustomSelectorType::class, array(
            'label' => "Identifier Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-identifierkeytype', 'type' => 'hidden'),
            'classtype' => 'identifierkeytype'
        ));

        $builder->add('field', null, array(
            'label' => 'Identifier:',
            'attr' => array('class'=>'form-control identifier-field-field')
        ));

        $builder->add('link', null, array(
            'label' => 'Link:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('publiclyVisible', CheckboxType::class, array(
            'required' => false,
            'label' => 'Publicly visible:',
        ));

        $builder->add('enableAccess', CheckboxType::class, array(
            'required' => false,
            'label' => 'Identifier enables system/service access:',
        ));

        //status
        $baseUserAttr = new Identifier();
        $statusAttr = array('class' => 'combobox combobox-width');
        if( !$this->rolePlatformAdmin ) {
            $statusAttr['readonly'] = true;
        }
        $builder->add('status', ChoiceType::class, array(   //flipped
            //'disabled' => ($this->rolePlatformAdmin ? false : true),
//            'choices'   => array(
//                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
//                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
//            ),
            'choices'   => array(
                $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED) => $baseUserAttr::STATUS_UNVERIFIED,
                $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED) => $baseUserAttr::STATUS_VERIFIED
            ),
            //'choices_as_values' => true,
            'invalid_message' => 'invalid value: identifier status',
            'label' => "Status:",
            'required' => true,
            'attr' => $statusAttr,  //array('class' => 'combobox combobox-width'),
        ));

        //keytypemrn
        $builder->add('keytypemrn', EntityType::class, array(
            'class' => 'OlegOrderformBundle:MrnType',
            'choice_label' => 'name',
            'label'=>'MRN Type:',
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width identifier-keytypemrn-field'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Identifier',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_identifier';
    }
}

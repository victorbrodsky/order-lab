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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CredentialsType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }

        $builder->add('dob', DateType::class, array(
            'label' => 'Date of Birth:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('sex', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:SexList',
            'choice_label' => 'name',
            'label' => "Gender:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added'
                        ));
                },
        ));

        $builder->add('numberCLIA', null, array(
            'label' => 'Clinical Laboratory Improvement Amendments (CLIA) Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('cliaExpirationDate', DateType::class, array(
            'label' => 'CLIA Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        //Clinical Laboratory Improvement Amendments (CLIA) section Relevant Documents
        $params = array('labelPrefix'=>'Relevant Document');
        $params['document.showall'] = false;
        $params['document.imageId'] = false;
        $params['document.source'] = false;
        //$params['disabled'] = $readonly;
        $builder->add('cliaAttachmentContainer', AttachmentContainerType::class, array(
            'form_custom_value' => $params,
            'required' => false,
            'label' => false
        ));

        $builder->add('numberPFI', null, array(
            'label' => 'State Permanent Facility Identifier (PFI) Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('numberCOQ', null, array(
            'label' => 'COQ Serial Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('coqCode', null, array(
            'label' => 'Certificate of Qualification (COQ) Code:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('coqExpirationDate', DateType::class, array(
            'label' => 'COQ Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        //Certificate of Qualification section Relevant Documents
        $params = array('labelPrefix'=>'Relevant Document');
        $params['document.showall'] = false;
        $params['document.imageId'] = false;
        $params['document.source'] = false;
        //$params['disabled'] = $readonly;
        $builder->add('coqAttachmentContainer', AttachmentContainerType::class, array(
            'form_custom_value' => $params,
            'required' => false,
            'label' => false
        ));

        $builder->add('emergencyContactInfo', null, array(
            'label' => 'Emergency Contact Information:',
            'attr' => array('class'=>'textarea form-control')
        ));

        if( !$hasRoleSimpleView ) {
            $builder->add('ssn', null, array(
                'label' => 'Social Security Number:',
                'attr' => array('class'=>'form-control form-control-modif')
            ));

            $builder->add('hobby', null, array(
                'label' => 'Hobbies:',
                'attr' => array('class' => 'textarea form-control')
            ));
        }


        $builder->add('codeNYPH', CollectionType::class, array(
            'entry_type' => CodeNYPHType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__codenyph__',
        ));

        $builder->add('stateLicense', CollectionType::class, array(
            'entry_type' => StateLicenseType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__statelicense__',
        ));

        $builder->add('boardCertification', CollectionType::class, array(
            'entry_type' => BoardCertificationType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__boardcertification__',
        ));

        $builder->add('identifiers', CollectionType::class, array(
            'entry_type' => IdentifierType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__identifiers__',
        ));


        $builder->add('citizenships', CollectionType::class, array(
            'entry_type' => CitizenshipType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__citizenships__',
        ));

        $builder->add('examinations', CollectionType::class, array(
            'entry_type' => ExaminationType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__examinations__',
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Credentials',
            'form_custom_value' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_credentials';
    }

}

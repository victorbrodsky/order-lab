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

namespace App\VacReqBundle\Form;


use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestBusinessType extends VacReqRequestBaseType {

    public function formConstructor( $params=null )
    {
        parent::formConstructor($params);

        $this->requestTypeName = "Business Travel";
        $this->numberOfDaysLabelPrefix = "Number of Work Days Off-site";
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder,$options);


        $builder->add('expenses', TextType::class, array(
            'label' => 'Estimated Expenses:',
            'attr' => array('class'=>'form-control vacreq-expenses'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        $builder->add('description', TextareaType::class, array(
            'label' => 'Description:',
            'attr' => array('class'=>'textarea form-control vacreq-description'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        $builder->add('paidByOutsideOrganization', CheckboxType::class, array(
            'label' => 'Paid by Outside Organization:',
            'required' => false,
            //'attr' => array('class' => 'form-control'),
            'disabled' => ($this->params['review'] ? true : false)
        ));

        //Complete the Travel Intake form
        //Show if enableTravelIntakeForm is not False
        //echo "enableTravelIntakeForm=".$this->params['enableTravelIntakeForm']."<br>";
        if( $this->params['enableTravelIntakeForm'] === true ) {
            $builder->add('travelIntakeForms', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'Complete the Travel Intake form for Spend Control Committee Approval:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));
        }

    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\VacReqBundle\Entity\VacReqRequestBusiness',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_vacreqbundle_request_business';
    }
}

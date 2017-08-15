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

namespace Oleg\VacReqBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestBusinessType extends VacReqRequestBaseType {

    public function __construct( $params=null, $entity = null )
    {
        parent::__construct($params,$entity);

        $this->requestTypeName = "Business Travel";
        $this->numberOfDaysLabelPrefix = "Number of Work Days Off-site";
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder,$options);


        $builder->add('expenses', 'text', array(
            'label' => 'Estimated Expenses:',
            'attr' => array('class'=>'form-control vacreq-expenses'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $builder->add('description', 'textarea', array(
            'label' => 'Description:',
            'attr' => array('class'=>'textarea form-control vacreq-description'),
            'read_only' => ($this->params['review'] ? true : false)
        ));

        $builder->add('paidByOutsideOrganization', 'checkbox', array(
            'label' => 'Paid by Outside Organization:',
            'required' => false,
            //'attr' => array('class' => 'form-control'),
            'read_only' => ($this->params['review'] ? true : false)
        ));


    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestBusiness',
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request_business';
    }
}

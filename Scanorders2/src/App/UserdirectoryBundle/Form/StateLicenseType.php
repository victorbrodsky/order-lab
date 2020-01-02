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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class StateLicenseType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $this->processStateCountry($builder);

        $builder->add('licenseNumber', null, array(
            'label' => 'License Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('licenseIssuedDate', null, array(
            'label' => 'License Issued Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('licenseExpirationDate', null, array(
            'label' => 'License Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));


//        $builder->add('active', 'checkbox', array(
//            'label' => 'Active:',
//            'attr' => array('class'=>'form-control')
//        ));
        $builder->add('active', null, array(
            'label' => 'Active:',
            'attr' => array('class'=>'combobox')
        ));

        //Medical License(s) section Relevant Documents
        $params = array('labelPrefix'=>'Relevant Document');
        $params['document.showall'] = false;
        $params['document.imageId'] = false;
        $params['document.source'] = false;
        //$params['disabled'] = $readonly;
        $builder->add('attachmentContainer', AttachmentContainerType::class, array(
            'form_custom_value' => $params,
            'required' => false,
            'label' => false
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\StateLicense',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_statelicense';
    }


    public function processStateCountry( $builder ) {

        //In your defaulting mechanism for "Add New Employee" page, in the Medical License section, set the defaults
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $stateLicense = $event->getData();
            $form = $event->getForm();

            $createCycle = false;
            if (strpos($this->params['cycle'], 'create') !== false) {
                $createCycle = true;
            }

            //state
            $stateParams = array(
                'class' => 'OlegUserdirectoryBundle:States',
                //'choice_label' => 'name',
                'label'=>'State:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            );

            if( $stateLicense && !$stateLicense->getState() ) {
                if( $createCycle ) {
                    $stateParams['data'] = $this->params['em']->getRepository('OlegUserdirectoryBundle:States')->findOneByName("New York");
                }
            }

            $form->add('state', EntityType::class, $stateParams);

            //country
            //$preferredCountries = $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findByName(array('United States'));
            $defaultCountry = $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName("United States");
            $preferredCountries = array($defaultCountry);

            $countryParams = array(
                'class' => 'OlegUserdirectoryBundle:Countries',
                'choice_label' => 'name',
                'label'=>'Country:',
                'required'=> false,
                'multiple' => false,
                'preferred_choices' => $preferredCountries,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            );

            if( $stateLicense && !$stateLicense->getCountry() ) {
                if( $createCycle ) {
                    $countryParams['data'] = $defaultCountry;
                }
            }

            $form->add('country', EntityType::class, $countryParams);

        });

    }

}

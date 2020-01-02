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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class BoardCertificationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'certifyingBoardOrganization', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:CertifyingBoardOrganization',
            'choice_label' => 'name',
            'label'=>'Certifying Board Organization:',
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
        ));


        $builder->add( 'specialty', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:BoardCertifiedSpecialties',
            'choice_label' => 'name',
            'label'=>'Specialty:',
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
        ));

        $builder->add('issueDate', null, array(
            'label' => 'Date Issued:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('expirationDate', null, array(
            'label' => 'Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('recertificationDate', null, array(
            'label' => 'Recertification Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        //Board Certification(s) section Relevant Documents
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
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\BoardCertification',
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_boardcertification';
    }
}

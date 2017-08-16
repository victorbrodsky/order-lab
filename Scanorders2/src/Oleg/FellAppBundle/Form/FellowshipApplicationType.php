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

namespace Oleg\FellAppBundle\Form;


use Oleg\UserdirectoryBundle\Form\BoardCertificationType;
use Oleg\UserdirectoryBundle\Form\CitizenshipType;
use Oleg\UserdirectoryBundle\Form\DataTransformer\StringToBooleanTransformer;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\ExaminationType;
use Oleg\UserdirectoryBundle\Form\LocationType;
use Oleg\UserdirectoryBundle\Form\StateLicenseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class FellowshipApplicationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

//        $builder->add('fellowshipSubspecialty',null, array(
//            'label' => '* Fellowship Type:',
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty'),
//        ));
//        $builder->add('fellowshipSubspecialty', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:FellowshipSubspecialty',
//            'label'=> "* Fellowship Application Type:",
//            'required'=> false,
//            //'multiple' => true,
//            'attr' => array('class'=>'combobox combobox-width fellapp-fellowshipSubspecialty'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.institution","institution")
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->andWhere("institution.id IS NOT NULL")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));
        //get subfellowship types as for ROLE_FELLAPP_ADMIN
        $fellappUtil = $this->params['container']->get('fellapp_util');
        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);
        $builder->add('fellowshipSubspecialty', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:FellowshipSubspecialty',
            'label' => "* Fellowship Application Type:",
            'required'=> false,
            'choices' => $fellTypes,
            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty'),
        ));

        if( $this->params['cycle'] == "new" ) {
            $builder->add('timestamp','date',array(
                'widget' => 'single_text',
                'label' => "Application Receipt Date:",
                //'format' => 'MM/dd/yyyy, H:mm:ss',
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }

        $builder->add('startDate','date',array(
            'widget' => 'single_text',
            'label' => "Start Date:",
            'format' => 'MM/dd/yyyy',  //'MM/dd/yyyy, H:mm:ss',
            'attr' => array('class' => 'datepicker form-control fellapp-startDate'),
            'required' => false,
        ));

        $builder->add('endDate','date',array(
            'widget' => 'single_text',
            'label' => "End Date:",
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control fellapp-endDate'),
            'required' => false,
        ));

        //FellAppUserType($this->params)
        $builder->add('user', FellAppUserType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'label' => false,
            'required' => false,
        ));


        $builder->add('coverLetters', 'collection', array(
            //'type' => new DocumentType($this->params),
            'entry_type' => DocumentType::class,
            'label' => 'Cover Letter(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('cvs', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Curriculum Vitae (CV):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


    //        $builder->add('reprimand','choice', array(
    //            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
    //            'required' => false,
    //            'choices' => array('Yes'=>'Yes','No'=>'No'),
    //            'attr' => array('class' => 'combobox'),
    //        ));
        $builder->add('reprimand', 'checkbox', array(
            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-reprimand-field', 'onclick' => 'showHideWell(this)'),
        ));
        $builder->get('reprimand')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('reprimandDocuments', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Upload Reprimand Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

    //        $builder->add('lawsuit','choice', array(
    //            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
    //            'required' => false,
    //            'choices' => array('Yes'=>'Yes','No'=>'No'),
    //            'attr' => array('class' => 'combobox'),
    //        ));
        $builder->add('lawsuit', 'checkbox', array(
            'label' => 'Have you ever been named in (and/or had a judgment against you) in a medical malpractice legal suit?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-lawsuit-field', 'onclick' => 'showHideWell(this)'),
        ));
        $builder->get('lawsuit')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('lawsuitDocuments', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Upload Legal Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        $builder->add('references', 'collection', array(
            'entry_type' => ReferenceType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => 'Reference(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__references__',
        ));


        $builder->add('honors',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('publications',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('memberships',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));



        $builder->add('signatureName',null, array(
            'label' => 'Signature:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('signatureDate', null, array(
            'label' => 'Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));


        $builder->add('reports', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('formReports', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Form Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        $builder->add('oldReports', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Old Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        //other documents
        $builder->add('documents', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Other Document(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('itinerarys', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Itinerary / Interview Schedule(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        $builder->add('interviewDate', null, array(
            'label' => 'Interview Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('interviews', 'collection', array(
            'entry_type' => InterviewType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => 'Interview(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__interviews__',
        ));

        $builder->add( 'observers', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Observer(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));


        /////////////////// user objects ////////////////////////////

        $builder->add('avatars', 'collection', array(
            'entry_type' => DocumentType::class,
            'label' => 'Applicant Photo(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('trainings', 'collection', array(
            'entry_type' => FellAppTrainingType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__trainings__',
        ));

        $this->userLocations($builder);

        $builder->add('citizenships', 'collection', array(
            'entry_type' => CitizenshipType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__citizenships__',
        ));

        $builder->add('examinations', 'collection', array(
            'entry_type' => ExaminationType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__examinations__',
        ));

        $builder->add('stateLicenses', 'collection', array(
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
            'prototype_name' => '__statelicenses__',
        ));

        $builder->add('boardCertifications', 'collection', array(
            'entry_type' => BoardCertificationType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__boardcertifications__',
        ));

        //////////////////////////////////////////////////////////////


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\FellAppBundle\Entity\FellowshipApplication',
            'form_custom_value' => null,
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_fellowshipapplication';
    }



    public function userLocations($builder) {


        if( $this->params['container']->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') ) {
            $roleAdmin = true;
            $readonly = true;
        } else {
            $roleAdmin = false;
            $readonly = false;
        }
        //echo "readonly=".$readonly."<br>";
        $readonly = false;

        $currentUser = false;
        $user = $this->params['container']->get('security.token_storage')->getToken()->getUser();
        if( $user->getId() === $this->params['user']->getId() ) {
            $currentUser = true;
        }
        //echo "currentUser=".$currentUser."<br>";


        $params = array('read_only'=>$readonly,'admin'=>$roleAdmin,'currentUser'=>$currentUser,'cycle'=>$this->params['cycle'],'em'=>$this->params['em'],'subjectUser'=>$this->params['user']);

        $builder->add('locations', 'collection', array(
            'entry_type' => FellAppLocationType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__locations__',
        ));

        return $builder;
    }

}

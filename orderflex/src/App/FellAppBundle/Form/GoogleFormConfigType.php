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

namespace App\FellAppBundle\Form;



use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty


use App\FellAppBundle\Entity\VisaStatus; //process.py script: replaced namespace by ::class: added use line for classname=VisaStatus

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class GoogleFormConfigType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

//        $builder->add('updateDate', DateTimeType::class, array(
//            'label'=>'Updated On:',
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM/dd/yyyy',
//            'disabled' => true,
//            'attr' => array('class' => 'datepicker form-control'),
//        ));
//
//        $builder->add('updatedBy',null,array(
//            'label'=>"Updated By:",
//            'required' => false,
//            'disabled' => true,
//            'attr' => array('class'=>'form-control')
//        ));

        $builder->add('acceptingSubmission', CheckboxType::class, array(
            'label' => "Accepting Submission",
            'required' => false,
            //'attr' => array('class' => 'form-control checkbox')
        ));

        $builder->add('fellowshipSubspecialties', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            'class' => FellowshipSubspecialty::class,
            'label' => "Fellowship specialties to display on the public application page for which applications should be accepted".$this->params['fellappTypesListLink'].":",
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['fellTypes'],
            'invalid_message' => 'fellowshipSubspecialty invalid value',
            //'choices_as_values' => true,
            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty'),
        ));

        $builder->add('applicationFormNote', null, array(
            'label' => "Application Notes:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('adminEmail', null, array(
            'label' => "Admin Email:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('fellappAdminEmail', null, array(
            'label' => "Fellowship Admin Email:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('exceptionAccount', null, array(
            'label' => "Exception Account for the Fellowship Application (the application is still shown to this google user for testing purposes):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('submissionConfirmation', null, array(
            'label' => "Fellowship Application Submission Confirmation Message:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));



        $builder->add('letterAcceptingSubmission', CheckboxType::class, array(
            'label' => "Accepting Submission of the Recommendation Letters:",
            'required' => false,
            //'attr' => array('class' => 'form-control checkbox')
        ));

        $builder->add('letterError', null, array(
            'label' => "Error note on the recommendation letter form:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('letterExceptionAccount', null, array(
            'label' => "Exception Account for the recommendation letter form (the form is still shown to this google user for testing purposes):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('fellowshipVisaStatuses', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:VisaStatus'] by [VisaStatus::class]
            'class' => VisaStatus::class,
            'label' => "Fellowship Visa Status".$this->params['fellappVisaStatusesListLink'].":",
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['fellVisaStatus'],
            'invalid_message' => 'fellowshipVisaStatuses invalid value',
            //'choices_as_values' => true,
            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipVisaStatuses'),
        ));

        $builder->add('visaNote', null, array(
            'label' => "Citizenship/Visa Note (i.e. 'NYPH-CORNELL ONLY ACCEPTS/SPONSORS J-1 VISAS'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('otherExperienceNote', null, array(
            'label' => "Other ExperienceNote Note (i.e. 'In chronological order, list other educational experiences, jobs, military service or training that is not accounted for above.'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('nationalBoardNote', null, array(
            'label' => "National Board Note (i.e. 'Please indicate national board examination dates and results received.'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('medicalLicenseNote', null, array(
            'label' => "Medical Licensure Note (i.e. 'Please list any states in which you hold a license to practice medicine. Please provide a license number. If an application is pending in a state, please write “pending.”'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('boardCertificationNote', null, array(
            'label' => "Board Certification Note (i.e. 'Please indicate any areas of board certification.'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('referenceLetterNote', null, array(
            'label' => "Reference Letter Note (i.e. 'Please list the individuals who will write your letters of recommendation. At least three are required.'):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('signatureStatement', null, array(
            'label' => "Signature Statement (i.e. 'I hereby certify that all of the information on this application is accurate...'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //RecLetter
        $builder->add('recSpreadsheetFolderId', null, array(
            'label' => "Google folder ID for recommendation letters Spreadsheet:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('recUploadsFolderId', null, array(
            'label' => "Google folder ID for recommendation letters upload:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('recTemplateFileId', null, array(
            'label' => "Google file ID for the Spreadsheet Template for the recommendation letter:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('recBackupTemplateFileId', null, array(
            'label' => "Google file ID for the Backup Spreadsheet Template for the recommendation letter:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //Fellowship Applications files and folders
        $builder->add('felSpreadsheetFolderId', null, array(
            'label' => "Google folder ID for fellowship application Spreadsheet:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('felUploadsFolderId', null, array(
            'label' => "Google folder ID for fellowship application upload:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('felTemplateFileId', null, array(
            'label' => "Google file ID for the Spreadsheet Template for the fellowship application:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('felBackupTemplateFileId', null, array(
            'label' => "Google file ID for the Backup Spreadsheet Template for the fellowship application:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\FellAppBundle\Entity\GoogleFormConfig',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_fellappbundle_googleformconfig';
    }
}

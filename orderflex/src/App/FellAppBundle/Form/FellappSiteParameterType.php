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



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Form\DataTransformer\DayMonthDateTransformer;
use Doctrine\ORM\EntityRepository;
//use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
//use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;

class FellappSiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);


        $builder->add('acceptedEmailSubject', null, array(
            'label' => 'Subject of e-mail to the accepted applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('acceptedEmailBody', null, array(
            'label' => 'Body of e-mail to the accepted applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));

        $builder->add('rejectedEmailSubject', null, array(
            'label' => 'Subject of e-mail to the rejected applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('rejectedEmailBody', null, array(
            'label' => 'Body of e-mail to the rejected applicant:',
            'attr' => array('class' => 'form-control textarea')
        ));

        $builder->add('fellappRecLetterUrl', null, array(
            'label' => 'Web app url from deployment GAS, send by email in inviteSingleReferenceToSubmitLetter'.
                ' (i.e. https://script.google.com/macros/s/fellapp_recletters_script_deployment_id/exec):',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('confirmationEmailFellApp', null, array(
            'label' => 'Email address for confirmation of application submission:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('applicationPageLinkFellApp', null, array(
            'label' => 'Link to the Fellowship Application Page:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('authPathFellApp', null, array(
            'label' => 'Full path to the credential authentication JSON file for Google:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('googleDriveApiUrlFellApp', null, array(
            'label' => 'Google Drive API URL (https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds):',
            'attr' => array('class' => 'form-control')
        ));

//        $builder->add('localInstitutionFellApp', null, array(
//            'label' => 'Local Organizational Group for imported fellowship applications:',
//            'attr' => array('class' => 'combobox'),
//            'required' => false
//        ));
        $builder->add( 'localInstitutionFellApp', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            'class' => Institution::class,
            'choice_label' => 'getTreeName',
            'label' => 'Local organizational group for imported fellowship applications (WCM => Pathology Fellowship Programs):',
            'required'=> false,
            'multiple' => false,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.parent","department")
                    //->where("(list.type = :typedef OR list.type = :typeadd) AND department.name = :pname")
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        //'pname' => 'Pathology and Laboratory Medicine'
                        //'medicalInstitution' => 'Medical'
                    ));
            },
        ));

        $builder->add('identificationUploadLetterFellApp', null, array(
            'label' => 'Fellowship identification string to download recommendation letters (wcmpath):',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('recLetterSaltFellApp', null, array(
            'label' => 'Recommendation Letter Salt (pepper):',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('allowPopulateFellApp',null,array(
            'label' => 'Periodically import fellowship applications and reference letters submitted via the Google form:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('sendEmailUploadLetterFellApp', null, array(
            'label'=>'Automatically send invitation emails to upload recommendation letters:',
            'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
        ));

        $builder->add('confirmationSubjectFellApp',null,array(
            'label'=>'Email subject for confirmation of application submission:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('confirmationBodyFellApp',null,array(
            'label'=>'Email body for confirmation of application submission:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('deleteImportedAplicationsFellApp',null,array(
            'label'=>"Delete successfully imported applications from Google Drive:",
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('deleteOldAplicationsFellApp',null,array(
            'label'=>'Delete downloaded spreadsheets with fellowship applications after successful import into the database:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('yearsOldAplicationsFellApp',null,array(
            'label'=>'Number of years to keep downloaded spreadsheets with fellowship applications as backup:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('spreadsheetsPathFellApp',null,array(
            'label'=>'Path to the downloaded spreadsheets with fellowship applications (fellapp/Spreadsheets):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

        $builder->add('applicantsUploadPathFellApp',null,array(
            'label'=>'Path to the downloaded attached documents (fellapp/FellowshipApplicantUploads):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

        $builder->add('reportsUploadPathFellApp',null,array(
            'label'=>'Path to the generated fellowship applications in PDF format (fellapp/Reports):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

        $builder->add('enablePublicFellApp',null,array(
            'label'=>'Enable access to the fellowship application form page on this site via /fellowship-applications/apply and enable recommendation letter upload via /fellowship-applications/submit-recommendation:',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));


        //TODO: implement date transformer when year is not set
//        $builder->add('fellappAcademicYearStart',null,array(
//            'label'=>'Application season start date (MM/DD) when the default fellowship application year changes to the following year (i.e. April 1st):',
//            //'attr' => array('class'=>'datepicker form-control datepicker-day-month')
//            'attr' => array('class'=>'form-control')
//        ));
//        $builder->add('fellappAcademicYearEnd',null,array(
//            'label'=>'Application season end date (MM/DD) when the default fellowship application year changes to the following year, if empty set to start date -1 day (i.e. March 31):',
//            'attr' => array('class'=>'form-control')
//        ));

        $builder->add(
                $builder->create('fellappAcademicYearStart', null, [
                    'label'=>'Application season start date (MM/DD) when the default fellowship application'.
                        ' year changes to the following year (i.e. April 1st)'.
                        ' (the global start date is used if not set):',
                    'required' => false,
                ])
                    ->addViewTransformer(new DayMonthDateTransformer())
            );
        $builder->add(
            $builder->create('fellappAcademicYearEnd', null, [
                'label'=>'Application season end date (MM/DD) when the default fellowship application'.
                    ' year changes to the following year, if empty set to start date -1 day (i.e. March 31)'.
                    ' (the global end date is used if not set):',
                'required' => false,
            ])
                ->addViewTransformer(new DayMonthDateTransformer())
        );

        //5 fields:
        $builder->add('localInstitution', null, array(
            'label' => 'Local Institution:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('fromInvitedInterview', null, array(
            'label' => 'From (e-mail to the applicant invited for an interview):',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('replyToInvitedInterview', null, array(
            'label' => 'Reply To (e-mail to the applicant invited for an interview):',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('subjectInvitedInterview', null, array(
            'label' => 'Subject of e-mail to the applicant invited for an interview:',
            'attr' => array('class' => 'form-control textarea')
        ));
        $builder->add('bodyInvitedInterview', null, array(
            'label' => 'Body of e-mail to the applicant invited for an interview:',
            'attr' => array('class' => 'form-control textarea')
        ));



        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }



    }


    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\FellAppBundle\Entity\FellappSiteParameter',
            'form_custom_value' => null,
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_fellappbundle_fellappsiteparameter';
    }
}

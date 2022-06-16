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

namespace App\ResAppBundle\Form;


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
            'attr' => array('class' => 'form-control checkbox')
        ));

        $builder->add('residencySubspecialties', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:ResidencySpecialty',
            'label' => "Residency Track".$this->params['resappTypesListLink'].":",
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['resTypes'],
            'invalid_message' => 'residencyTrack invalid value',
            //'choices_as_values' => true,
            'attr' => array('class' => 'combobox combobox-width resapp-residencyTrack'),
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

        $builder->add('resappAdminEmail', null, array(
            'label' => "Residency Admin Email:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('exceptionAccount', null, array(
            'label' => "Exception Account for the Residency Application (the application is still shown to this google user for testing purposes):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('submissionConfirmation', null, array(
            'label' => "Residency Application Submission Confirmation Message:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));



        $builder->add('letterAcceptingSubmission', CheckboxType::class, array(
            'label' => "Accepting Submission of the Recommendation Letters:",
            'required' => false,
            'attr' => array('class' => 'form-control checkbox')
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

        $builder->add('residencyVisaStatuses', EntityType::class, array(
            'class' => 'AppResAppBundle:VisaStatus',
            'label' => "Residency Visa Status".$this->params['resappVisaStatusesListLink'].":",
            'required' => false,
            'multiple' => true,
            'choices' => $this->params['resVisaStatus'],
            'invalid_message' => 'residencyVisaStatuses invalid value',
            //'choices_as_values' => true,
            'attr' => array('class' => 'combobox combobox-width resapp-residencyVisaStatuses'),
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

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\ResAppBundle\Entity\GoogleFormConfig',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_resappbundle_googleformconfig';
    }
}

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

namespace App\TranslationalResearchBundle\Form;

use App\TranslationalResearchBundle\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Minimalistic form for "Investigator's Initial Project Inquiry" (specialty 'init')
 * with only the fields shown on the CTP new-project-inquiry page:
 * 'WCM Investigator Project Inquiry' (inquiryType='wcm', requester-group=Internal)
 * 'External Collaboration Project Inquiry' (inquiryType='external', requester-group=External)
 */
class InitProjectType extends AbstractType
{
    private $params;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->params = $options['form_custom_value'];
        $disabled = $this->params['disabled'];
        $inquiryType = isset($this->params['inquiryType']) ? $this->params['inquiryType'] : 'wcm';

//        $builder->add('createDate', DateType::class, array(
//            'label' => "Date:",
//            'widget' => 'single_text',
//            'required' => true,
//            'disabled' => $disabled,
//            'attr' => array('class' => 'datepicker form-control')
//        ));

        if( $inquiryType === 'external' ) {
            //External Collaboration Project Inquiry fields

            $builder->add('inquiryContactName', TextType::class, array(
                'label' => "External Collaborator Contact Name:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryContactEmail', EmailType::class, array(
                'label' => "External Contact Email:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryOrgName', TextType::class, array(
                'label' => "Institution:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryPhone', TextType::class, array(
                'label' => "Contact Phone Number (optional):",
                'required' => false,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));
        } else {
            //WCM Investigator Project Inquiry fields

            $builder->add('title', TextType::class, array(
                'label' => "Project Title:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryPiName', TextType::class, array(
                'label' => "Principal Investigator:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryOrgName', TextType::class, array(
                'label' => "Department:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryPhone', TextType::class, array(
                'label' => "Phone:",
                'required' => false,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryContactName', TextType::class, array(
                'label' => "Contact Name:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('inquiryContactEmail', EmailType::class, array(
                'label' => "Contact Email Address:",
                'required' => true,
                'disabled' => $disabled,
                'attr' => array('class' => 'form-control')
            ));
        }

        //Common fields for both inquiry types
        $builder->add('description', TextareaType::class, array(
            'label' => "Background:",
            'required' => true,
            'disabled' => $disabled,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('inquiryPlanSummary', TextareaType::class, array(
            'label' => "Experimental Plan Summary:",
            'required' => true,
            'disabled' => $disabled,
            'attr' => array('class' => 'textarea form-control')
        ));

        if( $inquiryType !== 'external' ) {
            $builder->add('inquiryFundingSource', TextareaType::class, array(
                'label' => "Funding Source:",
                'required' => false,
                'disabled' => $disabled,
                'attr' => array('class' => 'textarea form-control')
            ));
        }

        if( $this->params['saveAsDraft'] === true ) {
            $builder->add('saveAsDraft', SubmitType::class, array(
                'label' => 'Save Draft Project Request',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
        if( $this->params['submitIrbReview'] === true ) {
            $builder->add('submitIrbReview', SubmitType::class, array(
                'label' => 'Submit for Review',
                'attr' => array('class' => 'btn btn-success')
            ));
        }
        if( $this->params['updateProject'] === true ) {
            $builder->add('updateProject', SubmitType::class, array(
                'label' => 'Save Changes',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
            'form_custom_value' => null,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'project';
    }
}

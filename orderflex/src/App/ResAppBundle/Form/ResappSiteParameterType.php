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

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ResappSiteParameterType extends AbstractType
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



        $builder->add('confirmationSubjectResApp', null, array(
            'label' => 'Email subject for confirmation of application submission:',
            'attr' => array('class'=>'form-control textarea form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('confirmationBodyResApp', null, array(
            'label'=>'Email body for confirmation of application submission:',
            'attr' => array('class'=>'form-control textarea form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('confirmationEmailResApp',null,array(
            'label'=>'Email address for confirmation of application submission:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('localInstitutionResApp',null,array(
            'label'=>'Local Organizational Group for imported residency applications (Pathology Residency Programs (WCM)):',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('spreadsheetsPathResApp',null,array(
            'label'=>'Path to the downloaded spreadsheets with residency applications (resapp/Spreadsheets):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

        $builder->add('applicantsUploadPathResApp',null,array(
            'label'=>'Path to the downloaded attached documents (resapp/ResidencyApplicantUploads):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

        $builder->add('reportsUploadPathResApp',null,array(
            'label'=>'Path to the generated residency applications in PDF format (resapp/Reports):',
            'attr' => array(
                'class'=>'form-control form-control-modif',
                'style'=>'margin:0',
            )
        ));

//        $builder->add('defaultResidencyTrack',null,array(
//            'label'=>'Default Residency Track for Bulk Import:',
//            'attr' => array(
//                'class'=>'form-control form-control-modif',
//                'style'=>'margin:0',
//            )
//        ));
        $builder->add('defaultResidencyTrack', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:ResidencyTrackList',
            'choice_label' => 'name',
            'label' => 'Default Residency Track for Bulk Import:',
            'required'=> false,
            'multiple' => false,
            'attr' => array('class' => 'combobox'),
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

        $builder->add('dataExtractionAnchor',null,array(
            'label'=>'Data Extraction Anchors in json format ([{"field":"Applicant ID:","startAnchor":"Applicant ID:","endAnchor":["AAMC ID:","Email:"],"minLength":10,"length":11,"maxLength":11},...]):',
            'attr' => array(
                'class'=>'form-control textarea',
            )
        ));


        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\ResAppBundle\Entity\ResappSiteParameter',
            'form_custom_value' => null,
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_resappbundle_resappsiteparameter';
    }
}

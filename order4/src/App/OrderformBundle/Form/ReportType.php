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

namespace App\OrderformBundle\Form;

use App\UserdirectoryBundle\Form\DocumentContainerType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ReportType extends AbstractType
{

    protected $params;
    protected $label;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        //////////// create labels ////////////
        $label = array();
        $label['processedDate'] = "Processed Date:";
        $label['processedByUser'] = "Processed By:";

        $messageCategory = $this->params['dataEntity.messageCategory'];

        //slide report
        if( $messageCategory == "Slide Report" ) {
            $label['processedDate'] = "Slide Cut or Prepared On:";
            $label['processedByUser'] = "Slide Cut or Prepared By:";
        }

        //stain report
        if( $messageCategory == "Stain Report" ) {
            $label['processedDate'] = "Slide Stained On:";
            $label['processedByUser'] = "Slide Stained By:";
        }

        //Outside Report
        if(
            $messageCategory == "Outside Report" ||
            $messageCategory == "Lab Report" ||
            $messageCategory == "Image Analysis Report" ||
            $messageCategory == "Scan Report"
        ) {
            $label['processedDate'] = null;
            $label['processedByUser'] = null;
        }

        $this->label = $label;
        //////////// EOF create labels ////////////
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('issuedDate', DateType::class, array(
            'label' => "Issued Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('receivedDate', DateType::class, array(
            'label' => "Received Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('signatureDate', DateType::class, array(
            'label' => "Signature Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        if( $this->label['processedDate'] ) {
            $builder->add('processedDate', DateType::class, array(
                'label' => $this->label['processedDate'], //"Processed Date:",
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
            ));
        }

        if( $this->label['processedByUser'] ) {
            $builder->add('processedByUser', null, array(
                'label' => $this->label['processedByUser'], //'Processed By:',
                'attr' => array('class' => 'combobox combobox-width'),
            ));
        }

//        $builder->add('reportType', null, array(
//            'label' => "Report Type:",
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Report',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_reporttype';
    }
}

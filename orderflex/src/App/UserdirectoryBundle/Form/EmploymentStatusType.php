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

namespace App\UserdirectoryBundle\Form;



use App\UserdirectoryBundle\Entity\EmploymentTerminationType; //process.py script: replaced namespace by ::class: added use line for classname=EmploymentTerminationType


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\VacReqBundle\Entity\VacReqApprovalTypeList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
//use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class EmploymentStatusType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['currentUser'] == true ) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        $builder->add('employmentType',null,array(
            'disabled' => $readonly,
            'label'=>"Employee Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

        //hireDate datetime
        //Error on windows only: unable to fetch the response from the backend: read tcp 127.0.0.1:64308->127.0.0.1:55029: wsarecv: An existing connection was forcibly closed by the remote host.
        //Or error: "apache AH00428: Parent: child process 5192 exited with status 255 Restarting"
        //caused by 'format' => 'MM/dd/yyyy', fix: replace by 'input_format' => 'MM/dd/yyyy'
        //Check if the dates are shown not correctly: 2025-08-23 shown as 02/02/2050 (example in project show)
        //Do not use input_format because it does not work correctly with datepicker.
        //Fixed: this caused by php.ini: extension=intl. Disabling it fixed the problem
        $builder->add('hireDate',DateTimeType::class,array(
            //'disabled' => $readonly,
            'label'=>"Date of Hire:",
            'widget' => 'single_text',
            'required' => false,
            //'format' => 'MM/dd/yyyy hh:mm',
            'format' => 'MM/dd/yyyy', //'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control') //'readonly'=>$readonly
        ));

        $builder->add('terminationDate',DateTimeType::class,array(
            'disabled' => $readonly,
            'label'=>"End of Employment Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy', //'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control user-expired-end-date')
        ));

        if( $readonly ) {
            $attr = array('class'=>'combobox combobox-width', 'readonly'=>'readonly');
        } else {
            $attr = array('class'=>'combobox combobox-width');
        }
        $builder->add( 'terminationType', EntityType::class, array(
            'disabled' => ($this->params['disabled'] ? true : false),
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentTerminationType'] by [EmploymentTerminationType::class]
            'class' => EmploymentTerminationType::class,
            'choice_label' => 'name',
            'label'=>'Type of End of Employment:',
            'required'=> false,
            'multiple' => false,
            'attr' => $attr,
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

        //do not show reason for user himself
        if( $this->params['currentUser'] == false ) {
            $builder->add('terminationReason', null, array(
                'label' => 'Reason for End of Employment:',
                'attr' => array('class'=>'textarea form-control')
            ));
        }

        $builder->add( 'jobDescriptionSummary', TextareaType::class, array(
            'label'=>'Job Description Summary:',
            'disabled' => $readonly,
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add( 'jobDescription', TextareaType::class, array(
            'label'=>'Job Description (official, as posted):',
            'disabled' => $readonly,
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //Associated Documents
        $params = array('labelPrefix'=>'Associated Document');
        $params['document.showall'] = false;
        $params['document.imageId'] = false;
        $params['document.source'] = false;
        //$params['disabled'] = $readonly;
        $builder->add('attachmentContainer', AttachmentContainerType::class, array(
            'form_custom_value' => $params,
            'required' => false,
            'label' => false
        ));

        /////// Fields for vacreq calculation ///////
        //PercentType::class
        //"data-inputmask" => "'mask': '[o]', 'repeat': 10, 'greedy' : false"
        $builder->add( 'effort', TextType::class, array(
            'label'=>'Effort in %:',
            'disabled' => $readonly,
            'required'=>false,
            'attr' => array('class' => 'form-control digit-mask')
        ));

        $builder->add( 'ignore', null, array(
            'label'=>'Ignore this employment period in vacation days calculation:',
            'disabled' => $readonly,
            'required'=>false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('approvalGroupType', EntityType::class, array(
            'class' => VacReqApprovalTypeList::class,
            'label' => "Time Away Approval Group Type:",
            'choice_label' => 'name',
            'required' => false,
            'multiple' => false,
            //'mapped' => false,
            //'data' => $this->params['approvalGroupType'],
            'attr' => array('class' => 'combobox', 'placeholder' => 'Time Away Approval Group Type'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));
        /////// EOF Fields for vacreq calculation ///////

        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $emplStatus = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $emplStatus ) {
                $institution = $emplStatus->getInstitution();
                //echo "emplStatus Inst=".$institution."<br>";
                if( $institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels(null) . ":";
            }
            //echo "label=".$label."<br>";

            $form->add('institution', CustomSelectorType::class, array(
                'label' => $label,
                'required' => false,
                //'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution'
                ),
                'classtype' => 'institution'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////



    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\EmploymentStatus',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_employmentstatus';
    }
}

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



use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['currentUser'] == true ) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        $builder->add('hireDate',null,array(
            'disabled' => $readonly,
            'label'=>"Date of Hire:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control') //'readonly'=>$readonly
        ));

        $builder->add('employmentType',null,array(
            'disabled' => $readonly,
            'label'=>"Employee Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('terminationDate',null,array(
            'disabled' => $readonly,
            'label'=>"End of Employment Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control user-expired-end-date')
        ));

        if( $readonly ) {
            $attr = array('class'=>'combobox combobox-width', 'readonly'=>'readonly');
        } else {
            $attr = array('class'=>'combobox combobox-width');
        }
        $builder->add( 'terminationType', EntityType::class, array(
            'disabled' => ($this->params['disabled'] ? true : false),
            'class' => 'OlegUserdirectoryBundle:EmploymentTerminationType',
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


        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $emplStatus = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $emplStatus ) {
                $institution = $emplStatus->getInstitution();
                //echo "emplStatus Inst=".$institution."<br>";
                if( $institution ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\EmploymentStatus',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_employmentstatus';
    }
}

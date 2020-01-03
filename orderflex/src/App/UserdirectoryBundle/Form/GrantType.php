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


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class GrantType extends AbstractType
{

    protected $params;

    //private $commentData = null;
    //private $effortData = null;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //echo "cycle=".$this->params['cycle']."<br>";

        if( strpos($this->params['cycle'],'_standalone') === false ) {
            $readonly = true;
            $standalone = false;
        } else {
            $readonly = false;
            $standalone = true;
        }

        $builder->add('id',HiddenType::class,array(
            'label'=>false,
            'attr' => array('class'=>'grant-id-field')
        ));

        $builder->add('grantid',null,array(
            //'disabled' => $readonly,
            'label'=>'Grant ID Number:',
            'attr' => array('class'=>'form-control grant-grantid-field', 'readonly'=>$readonly)
        ));

        $builder->add('amount',null,array(
            //'disabled' => $readonly,
            'label'=>'Total Amount:',
            'attr' => array('class'=>'form-control grant-amount-field', 'readonly'=>$readonly)
        ));

        $builder->add('currentYearDirectCost',null,array(
            //'disabled' => $readonly,
            'label'=>'Current Year Direct Cost:',
            'attr' => array('class'=>'form-control grant-currentYearDirectCost-field', 'readonly'=>$readonly)
        ));

        $builder->add('currentYearIndirectCost',null,array(
            //'disabled' => $readonly,
            'label'=>'Current Year Indirect Cost:',
            'attr' => array('class'=>'form-control grant-currentYearIndirectCost-field', 'readonly'=>$readonly)
        ));

        $builder->add('totalCurrentYearCost',null,array(
            //'disabled' => $readonly,
            'label'=>'Total Current Year Cost:',
            'attr' => array('class'=>'form-control grant-totalCurrentYearCost-field', 'readonly'=>$readonly)
        ));

        $builder->add('amountLabSpace',null,array(
            //'disabled' => $readonly,
            'label'=>'Amount of Lab Space:',
            'attr' => array('class'=>'form-control grant-amountLabSpace-field', 'readonly'=>$readonly)
        ));

        $builder->add('startDate', DateType::class, array(
            //'disabled' => $readonly,
            'label' => "Grant Support Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',    //'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control grant-startDate-field', 'readonly'=>$readonly),
        ));

        $builder->add('endDate', DateType::class, array(
            //'disabled' => $readonly,
            'label' => "Grant Support End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control grant-endDate-field', 'readonly'=>$readonly),
        ));

        $sourceOrganizationAttr = array('class' => 'combobox combobox-width ajax-combobox-sourceorganization', 'type' => 'hidden');
        if( $readonly ) {
            $sourceOrganizationAttr['readonly'] = true;
        }
        $builder->add('sourceOrganization', CustomSelectorType::class, array(
            //'disabled' => $readonly,
            'label' => "Grant Source Organization (Sponsor):",
            'required' => false,
            'attr' => $sourceOrganizationAttr,  //array('class' => 'combobox combobox-width ajax-combobox-sourceorganization', 'type' => 'hidden'),
            'classtype' => 'sourceorganization'
        ));

        $builder->add('grantLink', null, array(
            //'disabled' => $readonly,
            'label' => 'Link to a page with more information:',
            'attr' => array('class'=>'form-control grant-grantLink-field', 'readonly'=>$readonly)
        ));


        //Relevant Documents
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


        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        //if( strpos($this->params['cycle'],'_standalone') !== false && strpos($this->params['cycle'],'new') === false ) {
        if( $standalone ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "Grant";
            $mapper['bundleName'] = "AppUserdirectoryBundle";

            //ListType($params, $mapper)
            $builder->add('list', ListType::class, array(
                'form_custom_value' => $params,
                'form_custom_value_mapper' => $mapper,
                'data_class' => 'App\UserdirectoryBundle\Entity\Grant',
                'label' => false
            ));
        }



        if( !$standalone ) {

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $grant = $event->getData();
                $form = $event->getForm();

                $nameAttr = array('class' => 'combobox combobox-width ajax-combobox-grant', 'type' => 'hidden');
                if( $grant && $grant->getId() ) {
                    $nameAttr['readonly'] = true;
                }
                $form->add('name', CustomSelectorType::class, array(
                    //'disabled' => ($grant && $grant->getId() ? true : false),
                    'label' => "Grant Title:",
                    'required' => false,
                    'attr' => $nameAttr,    //array('class' => 'combobox combobox-width ajax-combobox-grant', 'type' => 'hidden'),
                    //'attr' => array('class' => 'combobox combobox-width ajax-combobox-grant', 'type' => 'hidden'),
                    'classtype' => 'grant'
                ));

                if( $grant && $grant->getId() && $this->params['subjectUser'] ) {

                    //comment
                    $comment = $this->params['em']->getRepository('AppUserdirectoryBundle:GrantComment')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    //exit("grant=".$grant->getId().", user=".$this->params['subjectUser']->getId()." => comment=".$comment);
                    if( $comment ) {
                        $grant->setCommentDummy($comment->getComment());
                    }

                    //effort
                    $effort = $this->params['em']->getRepository('AppUserdirectoryBundle:GrantEffort')->findOneBy(
                        array(
                            'grant' => $grant,
                            'author' => $this->params['subjectUser']
                        )
                    );

                    if( $effort ) {
                        $grant->setEffortDummy($effort->getEffort());
                    }

                }


            });


            //exit('this->commentData='.$this->commentData);

            $builder->add('commentDummy', TextareaType::class, array(
                //'mapped' => false,
                'required' => false,
                'label'=>'Comment:',
                'attr' => array('class'=>'textarea form-control grant-commentDummy-field', 'readonly'=>$readonly)
            ));

            $builder->add('effortDummy', CustomSelectorType::class, array(
                //'mapped' => false,
                'required' => false,
                'label' => 'Percent Effort:',
                'attr' => array('class'=>'ajax-combobox-effort grant-effortDummy-field', 'readonly'=>$readonly),
                //'attr' => array('class' => 'ajax-combobox-effort grant-effortDummy-field', 'type' => 'hidden', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
                'classtype' => 'effort'
            ));

        } else {

            $builder->add('name',null,array(
                'label'=>"Grant Title:",
                'required' => true,
                'attr' => array('class' => 'form-control')
            ));

        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Grant',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_grant';
    }
}

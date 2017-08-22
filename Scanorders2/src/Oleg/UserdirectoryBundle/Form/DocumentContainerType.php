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



use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class DocumentContainerType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {

        if( !$params || !array_key_exists('labelPrefix',$params) || !$params['labelPrefix'] ) {
            $params['labelPrefix'] = 'Image';
        }

        //set default as true
        if( $params && !array_key_exists('document.showall',$params) ) {
            $params['document.showall'] = true;
        } else {
            $params['document.showall'] = false;
        }

        ///////////////// labels /////////////////
        if( $params && !array_key_exists('document.device.label',$params) ) {
            $params['document.device.label'] = $params['labelPrefix'] . ' Device:';
        }

        if( $params && !array_key_exists('document.datetime.label',$params) ) {
            $params['document.datetime.label'] = $params['labelPrefix'] . ' Date:';
        }

        if( $params && !array_key_exists('document.time.label',$params) ) {
            $params['document.time.label'] = $params['labelPrefix'] . ' Time:';
        }

        if( $params && !array_key_exists('document.provider.label',$params) ) {
            $params['document.provider.label'] = $params['labelPrefix'] . ' Acquired By:';
        }

        if( $params && !array_key_exists('document.link.label',$params) ) {
            $params['document.link.label'] = $params['labelPrefix'] . ' Link:';
        }
        ///////////////////////////////////////////////////////////

        ///////////////// set default as true /////////////////
        if( $params && !array_key_exists('document.imageId',$params) ) {
            $params['document.imageId'] = true;
        } else {
            $params['document.imageId'] = false;
        }

        if( $params && !array_key_exists('document.source',$params) ) {
            $params['document.source'] = true;
        } else {
            $params['document.source'] = false;
        }

        if( $params && !array_key_exists('document.datetime',$params) ) {
            $params['document.datetime'] = true;
        } else {
            $params['document.datetime'] = false;
        }

        if( $params && !array_key_exists('document.provider',$params) ) {
            $params['document.provider'] = true;
        } else {
            $params['document.provider'] = false;
        }

        if( $params && !array_key_exists('document.device',$params) ) {
            $params['document.device'] = true;
        } else {
            $params['document.device'] = false;
        }

        if( $params && !array_key_exists('document.link',$params) ) {
            $params['document.link'] = true;
        } else {
            $params['document.link'] = false;
        }
        ///////////////////////////////////////////////////////////


        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('id', HiddenType::class, array(
            'attr' => array('class' => 'documentcontainer-field-id'),
        ));

        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => $this->params['labelPrefix'] . '(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        if( $this->params['document.showall'] == true ) {

            $builder->add('title', null, array(
                'label' => $this->params['labelPrefix'] . ' Title:',
                'attr' => array('class' => 'form-control'),
            ));

            //comments
            $docParams = array('documentContainer.comments.comment.label' => $this->params['labelPrefix'] );
            $builder->add('comments', CollectionType::class, array(
                //'type' => new DocumentCommentType($docParams),
                'entry_type' => DocumentCommentType::class,
                'entry_options' => array(
                    'form_custom_value' => $docParams
                ),
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__comments__',
            ));

            if( $this->params['document.device'] ) {
                if( array_key_exists('device.types', $this->params) && is_array($this->params['device.types']) && $this->params['device.types'] != false ) {
                    $builder->add( 'device', EntityType::class, array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'choice_label' => 'name',
                        'label' => $this->params['document.device.label'],
                        'required'=> true,
                        'multiple' => false,
                        'attr' => array('class'=>'combobox combobox-width'),
                        'query_builder' => function(EntityRepository $er) {

                                if( is_array($this->params['device.types']) ) {
                                    $equipmentTypes = $this->params['device.types'];
                                    $whereArr = array();
                                    foreach($equipmentTypes as $equipmentType) {
                                        $whereArr[] = "keytype.name = '" . $equipmentType . "'";
                                    }
                                    $whereStr = implode(' OR ', $whereArr);
                                    $where = $whereStr . " AND i.type != :typedef OR i.type = :typeadd";
                                } else {
                                    $where = "i.type != :typedef OR i.type = :typeadd";
                                }

                                return $er->createQueryBuilder('i')
                                    ->leftJoin('i.keytype','keytype')
                                    ->where($where)
                                    ->setParameters( array(
                                        'typedef' => 'default',
                                        'typeadd' => 'user-added',
                                    ));
                            },
                    ));
                }
            }

            if( $this->params['document.imageId'] ) {
                $builder->add('imageId', TextType::class, array(
                    'required'=>false,
                    'label'=>'Image ID:',
                    'attr' => array('class'=>'form-control'),
                ));
            }

            if( $this->params['document.source'] ) {
                $builder->add('source', null, array(
                    'required'=>false,
                    'label'=>'Image ID Source System:',
                    'attr' => array('class' => 'combobox combobox-width'),
                ));
            }

            if( $this->params['document.datetime'] ) {
                $builder->add('datetime', DateType::class, array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
                    'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
                    'required' => false,
                    'label' => $this->params['document.datetime.label'],
                ));

                $builder->add('time', TimeType::class, array(
                    'input'  => 'datetime',
                    'widget' => 'choice',
                    'label' => $this->params['document.time.label']
                ));
            }

            if( $this->params['document.provider'] ) {
                $builder->add('provider', null, array(
                    'label' => $this->params['document.provider.label'],
                    'attr' => array('class' => 'combobox combobox-width'),
                ));
            }

            if( $this->params['document.link'] ) {
                //echo "show link<br>";
//                $builder->add('link', null, array(
//                    'label' => $this->params['document.link.label'],
//                    'attr' => array('class' => 'form-control'),
//                ));
                $builder->add('links', CollectionType::class, array(
                    'entry_type' => LinkType::class,
                    //'entry_options' => array(
                    //    'form_custom_value' => $this->params
                    //),
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'by_reference' => false,
                    'prototype' => true,
                    'prototype_name' => '__links__',
                ));
            }

        } //showall

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_documentcontainertype';
    }
}

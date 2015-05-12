<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class DocumentContainerType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
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
            $params['document.datetime.label'] = $params['labelPrefix'] . ' Date & Time:';
        }

        if( $params && !array_key_exists('document.provider.label',$params) ) {
            $params['document.provider.label'] = $params['labelPrefix'] . ' Scanned By:';
        }

        if( $params && !array_key_exists('document.link.label',$params) ) {
            $params['document.link.label'] = $params['labelPrefix'] . ' Link:';
        }
        ///////////////////////////////////////////////////////////

        ///////////////// set default as true /////////////////
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

        $builder->add('id', 'hidden', array(
            'attr' => array('class' => 'documentcontainer-field-id'),
        ));

        $builder->add('documents', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => $this->params['labelPrefix'] . '(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        if( $this->params['document.showall'] == true ) {

            $builder->add('title', null, array(
                'label' => $this->params['labelPrefix'] . ' Title:',
                'attr' => array('class' => 'form-control'),
            ));

            //comments
            $docParams = array('documentContainer.comments.comment.label' => $this->params['labelPrefix'] );
            $builder->add('comments', 'collection', array(
                'type' => new DocumentCommentType($docParams),
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
                    $builder->add( 'device', 'entity', array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'property' => 'name',
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

            if( $this->params['document.datetime'] ) {
                $builder->add('datetime','date',array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
                    'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
                    'required' => false,
                    'label' => $this->params['document.datetime.label'],
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
                $builder->add('links', 'collection', array(
                    'type' => new LinkType($this->params),
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_documentcontainertype';
    }
}

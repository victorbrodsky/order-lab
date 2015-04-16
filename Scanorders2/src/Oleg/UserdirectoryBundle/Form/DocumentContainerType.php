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

        if( $params && !array_key_exists('document.showall',$params) ) {
            $params['document.showall'] = true;
        }

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

            if( array_key_exists('device.types', $this->params) &&  $this->params['device.types'] == true ) {
                $builder->add( 'device', 'entity', array(
                    'class' => 'OlegUserdirectoryBundle:Equipment',
                    'property' => 'name',
                    'label' => $this->params['labelPrefix'] . ' Device:',
                    'required'=> true,
                    'multiple' => false,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'query_builder' => function(EntityRepository $er) {

                            $equipmentTypes = $this->params['device.types'];
                            $whereArr = array();
                            foreach($equipmentTypes as $equipmentType) {
                                $whereArr[] = "keytype.name = '" . $equipmentType . "'";
                            }
                            $where = implode(' OR ', $whereArr);

                            return $er->createQueryBuilder('i')
                                ->leftJoin('i.keytype','keytype')
                                ->where($where . " AND i.type != :type")
                                ->setParameters( array('type' => 'disabled') );
                        },
                ));
            } else {
                $builder->add('device', null, array(
                    'label' => $this->params['labelPrefix'] . ' Device:',
                    'attr' => array('class' => 'combobox combobox-width'),
                ));
            }

            $builder->add('datetime','date',array(
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
                'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
                'required' => false,
                'label'=>$this->params['labelPrefix'] . ' Date & Time:',
            ));

            $builder->add('provider', null, array(
                'label' => $this->params['labelPrefix'] . ' Scanned By:',
                'attr' => array('class' => 'combobox combobox-width'),
            ));

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

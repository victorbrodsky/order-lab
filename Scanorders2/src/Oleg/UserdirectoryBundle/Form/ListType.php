<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ListType extends AbstractType
{

    protected $params;

    protected $mapper;

    protected $addwhere = "";

    protected $types = array(
                            "default"=>"default",
                            "user-added"=>"user-added",
                            "disabled"=>"disabled",
                            "draft"=>"draft"
                        );

    public function __construct( $params=null, $mapper=null )
    {
        $this->params = $params;
        $this->mapper = $mapper;

        if( array_key_exists('id', $this->params) ) {
            $this->addwhere = " AND list.id != ".$this->params['id'];
        }
        //echo "addwhere=".$this->addwhere."<br>";

        if( $this->mapper['className'] == 'AccessionType' || $this->mapper['className'] == 'accessiontype' ) {
            $this->types['TMA'] = 'TMA';
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('orderinlist',null,array(
            'label'=>'Display Order:',
            'required' => true,
            'attr' => array('class'=>'form-control')
        ));

        if( !array_key_exists('standalone', $this->params) || $this->params['standalone'] == false ) {
            $builder->add('name',null,array(
                'label'=>'Name:',
                'attr' => array('class'=>'form-control')
            ));
        }

        $builder->add('abbreviation',null,array(
            'label' => 'Abbreviation:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('shortname',null,array(
            'label' => 'Short Name:',
            'attr' => array('class' => 'form-control')
        ));

        //description
        $descriptionLabel = 'Description:';
        if( array_key_exists('labels', $this->mapper) && $this->mapper['labels'] ) {
            if( array_key_exists('description', $this->mapper['labels']) && $this->mapper['labels']['description'] ) {
                $descriptionLabel = $this->mapper['labels']['description'];
            }
        }
        $builder->add('description','textarea',array(
            'label' => $descriptionLabel,
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('type','choice',array(
            'label'=>'Type:',
            'choices' => $this->types,
            'required' => true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width select2-list-type')
        ));

        $builder->add('creator',null,array(
            'label'=>'Creator:',
            'required'=>true,
            'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
        ));

        $builder->add( 'createdate', 'date', array(
            'label'=>'Creation Date:',
            'widget' => 'single_text',
            'required'=>true,
            'read_only'=>true,
            'format' => 'MM/dd/yyyy, H:mm:ss',
            'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
            'attr' => array('class' => 'form-control'),
        ));


//        if( array_key_exists('cycle', $this->params) && $this->params['cycle'] != 'new' ) {
//
//                //echo "cycle=".$this->params['cycle']."<br>";
//
////                $builder->add('updatedby',null,array(
////                    'label'=>'Updated by:',
////                    'required'=>false,
////                    'read_only'=>true,
////                    'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
////                ));
//
////                $builder->add( 'updatedon', 'date', array(
////                    'label'=>'Updated on:',
////                    'widget' => 'single_text',
////                    'required'=>false,
////                    'read_only'=>true,
////                    'format' => 'MM/dd/yyyy, H:mm:ss',
////                    'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
////                    'attr' => array('class' => 'form-control'),
////                ));
//
//        }


        $builder->add('synonyms', 'entity', array(
            'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
            'label' => 'Synonyms:',
            //'read_only' => true,
            'required' => false,
            'multiple' => true,
            //'by_reference' => false,
            'attr' => array('class' => 'combobox combobox-width select2-list-synonyms'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where( "list.type != :disabletype AND list.type != :drafttype" . $this->addwhere )
                    ->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft') );
            },
        ));

        $builder->add('original', 'entity', array(
            'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
            'label'=>'Original (Canonical) Synonymous Term:',
            'required' => false,
            'multiple' => false,
            //'by_reference' => false,
            'attr' => array('class' => 'combobox combobox-width select2-list-original'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list');
                    //->where( "list.type = :type" . $this->addwhere )
                    //->setParameter( 'type','default' );
            },
        ));

        $builder->add('linkToListId',null,array(
            'label' => 'Link to List ID:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('objectType',null,array(
            'label' => 'Object Type:',
            'attr' => array('class' => 'combobox')
        ));

        $builder->add('entityNamespace',null,array(
            'label' => 'Object Namespace:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('entityName',null,array(
            'label' => 'Object Name:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('linkToObjectId',null,array(
            'label' => 'Linked Object ID:',
            'attr' => array('class' => 'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_listtype';
    }
}

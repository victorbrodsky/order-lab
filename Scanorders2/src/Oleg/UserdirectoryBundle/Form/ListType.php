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

    public function __construct( $params=null, $mapper )
    {
        $this->params = $params;
        $this->mapper = $mapper;

        if( array_key_exists('id', $this->params) ) {
            $this->addwhere = " AND list.id != ".$this->params['id'];
        }

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
            'attr' => array('class'=>'combobox combobox-width select2-list-creator')
        ));

        $builder->add( 'createdate', 'date', array(
            'label'=>'Creation Date:',
            'widget' => 'single_text',
            'required'=>true,
            'read_only'=>true,
            'format' => 'MM-dd-yyyy, H:mm:ss',
            'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
            'attr' => array('class' => 'form-control'),
        ));

        if( array_key_exists('cicle', $this->params) && $this->params['cicle'] != 'new' && $this->params['cicle'] != "create_location" ) {

            //echo "cicle=".$this->params['cicle']."<br>";

            $builder->add('updatedby',null,array(
                'label'=>'Updated by:',
                'required'=>false,
                'attr' => array('class'=>'combobox combobox-width select2-list-creator')
            ));

            $builder->add( 'updatedon', 'date', array(
                'label'=>'Updated on:',
                'widget' => 'single_text',
                'required'=>true,
                'read_only'=>true,
                'format' => 'MM-dd-yyyy, H:mm:ss',
                'view_timezone' => $this->params['user']->getPreferences()->getTimezone(),
                'attr' => array('class' => 'form-control'),
            ));
        } else {
            //echo "no update <br>";
        }

        if( array_key_exists('synonyms', $this->params) ) {

            $builder->add('synonyms', 'entity', array(
                'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
                'label'=>'Synonyms:',
                'required' => false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width select2-list-synonyms'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where( "list.type != :type" . $this->addwhere )
                        ->setParameters( array( 'type'=>'default' ) );
                },
            ));
        }


        if( array_key_exists('original', $this->params) ) {

            $builder->add('original', 'entity', array(
                'class' => $this->mapper['bundleName'].':'.$this->mapper['className'],
                'label'=>'Original Synonymous Term:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width select2-list-original'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where( "list.type = :type" . $this->addwhere )
                        ->setParameter( 'type','default' );
                },
            ));
        }



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

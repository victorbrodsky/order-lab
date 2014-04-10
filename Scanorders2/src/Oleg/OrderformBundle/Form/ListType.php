<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ListType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('orderinlist',null,array(
            'label'=>'Order:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('name',null,array(
            'label'=>'Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('type','choice',array(
            'label'=>'Type:',
            'choices' => array(
                "default"=>"default",
                "user-added"=>"user-added",
                "disabled"=>"disabled",
                "draft"=>"draft"
            ),
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
            'format' => 'MM-dd-yyyy, H:m:s',
            'view_timezone' => $this->params['user']->getTimezone(),
            'attr' => array('class' => 'form-control'),
        ));

        if( array_key_exists('cicle', $this->params) && $this->params['cicle'] != 'new' ) {

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
                'format' => 'MM-dd-yyyy, H:m:s',
                'view_timezone' => $this->params['user']->getTimezone(),
                'attr' => array('class' => 'form-control'),
            ));
        } else {
            //echo "no update <br>";
        }

        if( array_key_exists('synonyms', $this->params)) {
//            $builder->add('synonyms',null,array(
//                'label'=>'Synonym:',
//                //'multiple' => false,
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width select2-list-synonyms')
//            ));
            $builder->add('synonyms', 'entity', array(
                'class' => 'OlegOrderformBundle:'.$this->params['className'],
                'label'=>'Synonym:',
                'required' => false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width select2-list-synonyms'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where('list.type != :type')
                        ->setParameter('type', 'default');
                },
            ));
        }


        if( array_key_exists('original', $this->params)) {
//            $builder->add('original',null,array(
//                'label'=>'Original:',
//                //'multiple' => false,
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width select2-list-original')
//            ));
            $builder->add('original', 'entity', array(
                'class' => 'OlegOrderformBundle:'.$this->params['className'],
                'label'=>'Original:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width select2-list-original'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where('list.type = :type')
                        ->setParameter('type', 'default');
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
        return 'oleg_orderformbundle_listtype';
    }
}

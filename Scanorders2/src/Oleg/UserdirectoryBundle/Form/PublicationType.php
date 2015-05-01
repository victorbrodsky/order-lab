<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class PublicationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id','hidden',array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));


        $builder->add('publicationDate', 'date', array(
            'label' => 'Publication Month and Year:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker-exception form-control'),
        ));

        if( $this->params['cycle'] == "show" ) {
            $builder->add('updatedate', 'date', array(
                'read_only' => true,
                'label' => 'Update Date:',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
            ));
        }

        $builder->add('citation','textarea',array(
            'required' => false,
            'label'=>'Citation / Reference:',
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('pubmedid', null, array(
            'label' => 'PubMed ID:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('link', null, array(
            'label' => 'PubMed or Relevant Link:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add( 'importance', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:ImportanceList',
            'label'=> "Importance:",
            'required'=> false,
            'multiple' => false,
            'property' => 'name',
            'attr' => array('class'=>'combobox combobox-width'),
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






    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Publication',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_publication';
    }
}

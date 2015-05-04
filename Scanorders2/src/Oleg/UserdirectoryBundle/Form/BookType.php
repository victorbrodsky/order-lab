<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class BookType extends AbstractType
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

        $builder->add('publicationDate','employees_custom_selector', array(
            'label' => 'Publication Month and Year:',
            'required' => false,
            'attr' => array('class' => 'datepicker-exception form-control'),
            'classtype' => 'month_year_date_only'
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

        $builder->add('comment','textarea',array(
            'required' => false,
            'label'=>'Comment:',
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('isbn', null, array(
            'label' => 'ISBN:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('link', null, array(
            'label' => 'Relevant Link:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add( 'authorshipRole', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:AuthorshipRoles',
            'label'=> "Authorship Role:",
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
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Book',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_book';
    }
}

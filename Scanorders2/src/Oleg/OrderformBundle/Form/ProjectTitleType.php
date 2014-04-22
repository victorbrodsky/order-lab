<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ProjectTitleType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $builder->add('projectTitle', new ProjectTitleListType($this->params, $this->entity), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleList',
//            'label' => false,
//            'required' => false,
//        ));

        $builder->add( 'name', 'custom_selector', array(
            'label' => 'Research Project Title:',
            'required' => false,
            //'read_only' => $readonly,
            'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
            'classtype' => 'projectTitle'
        ));

        $builder->add( 'setTitles', 'custom_selector', array(
            'label' => 'Research Set Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
            //'read_only' => $readonly,
            'classtype' => 'setTitles'
        ));

        if( $this->params['type'] == 'SingleObject' ) {

            $builder->add('primaryPrincipal', 'entity', array(
                'class' => 'OlegOrderformBundle:PIList',
                'label'=>'Primary Principal Investigator:',
                'required' => true,
                //'read_only' => true,    //not working => disable by twig
                //'multiple' => true,
                //'attr' => array('class'=>'form-control form-control-modif'),
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.researches","researches")
                        ->where("researches.id = :id")
                        ->setParameter('id', $this->entity->getId());
                },
            ));

            $builder->add('principals', 'collection', array(
                'type' => new PrincipalType($this->params,$this->entity),
                'required' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'label' => " ",
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__patient__',
            ));

        } else {

            //$addlabel = " (as entered by user)";
            $builder->add('principals', 'custom_selector', array(
                'label' => 'Principal Investigator:',
                'attr' => array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden'),
                'required'=>false,
                'classtype' => 'optionalUserResearch'
            ));

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Research'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_researchtype';
    }
}

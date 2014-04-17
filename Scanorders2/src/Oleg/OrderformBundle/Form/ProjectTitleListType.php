<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ProjectTitleListType extends AbstractType
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


        ///////////////////// PI //////////////////////////////////

        $addlabel = "";
        $readonly = false;

        //echo "type=".$this->params['type']."<br>";

//        if( $this->params['type'] == 'SingleObject' ) {
//            //this is used by data review, when a single onject is shown
//            $attr = array('class'=>'form-control form-control-modif');
//            $addlabel = " (as entered by user)";
//            $readonly = true;
//        } else {
//            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
//            $attr = array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden');
//        }

        //show a user object linked to the research. Show it only for data review.
        if( $this->params['type'] == 'SingleObject' ) {

//            $attr = array('class' => 'combobox combobox-width');
//            $builder->add('pis', 'entity', array(
//                'class' => 'OlegOrderformBundle:PIList',
//                'label'=>'Principal Investigator:',
//                'required' => false,
//                //'read_only' => true,    //not working => disable by twig
//                'multiple' => true,
//                'attr' => array('class'=>'form-control form-control-modif'),
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->leftJoin("list.projects","parents")
//                        ->where("parents.id = :id")
//                        ->setParameter('id', $this->entity->getId());
//                },
//            ));

//            $attr = array('class' => 'combobox combobox-width');
//            $builder->add('pis', 'entity', array(
//                'class' => 'OlegOrderformBundle:User',
//                'label'=>'Principal Investigator:',
//                'required' => false,
//                //'read_only' => true,    //not working => disable by twig
//                //'multiple' => true,
//                'attr' => $attr,
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('u')
//                        ->where('u.locked=:locked')
//                        ->setParameter('locked', '0');
//                },
//            ));

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
                        ->leftJoin("list.projects","parents")
                        ->where("parents.id = :id")
                        ->setParameter('id', $this->entity->getId());
                },
            ));

//            $builder->add('pis', new PrincipalType($this->params, $this->entity), array(
//                'data_class' => 'Oleg\OrderformBundle\Entity\PIList',
//                'label' => false
//            ));
            $builder->add('pis', 'collection', array(
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

            $addlabel = " (as entered by user)";
            $builder->add('pis', 'custom_selector', array(
                'label' => 'Principal Investigator'.$addlabel.':',
                'attr' => array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden'),
                'required'=>false,
                'classtype' => 'optionalUserResearch'
            ));

        }

        ///////////////////////////// EOF PI ///////////////////////////////



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleList'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_projecttitlelisttype';
    }
}

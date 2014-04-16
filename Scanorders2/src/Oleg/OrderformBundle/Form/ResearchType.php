<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ResearchType extends AbstractType
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

//        $addlabel = "";
//        $readonly = false;
//
//        if( $this->params['type'] == 'SingleObject' ) {
//            //this is used by data review, when a single onject is shown
//            $attr = array('class'=>'form-control form-control-modif');
//            $addlabel = " (as entered by user)";
//            $readonly = true;
//        } else {
//            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
//            $attr = array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden');
//        }

        //project title
//        $builder->add('projectTitle', null, array(
//            'type' => new AccessionAccessionType($this->params, null),
//            'required' => false,
//        ));
        $builder->add('projectTitle', new ProjectTitleListType($this->params, null), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleList',
            'label' => false,
            'required' => false,
        ));


//        //principal
//        $builder->add('principalstr', 'custom_selector', array(
//            'label' => 'Principal Investigator'.$addlabel.':',
//            'attr' => $attr,
//            'required'=>false,
//            'classtype' => 'optionalUserResearch',
//            'read_only' => $readonly
//        ));
//
//        //show a user object linked to the research. Show it only for data review.
//        if( $this->params['type'] == 'SingleObject' ) {
//
//            $attr = array('class' => 'combobox combobox-width');
//            $builder->add('principal', 'entity', array(
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
//        }

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

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
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $addlabel = "";
        $readonly = false;

        if( $this->params['type'] == 'SingleObject' ) {
            //this is used by data review, when a single onject is shown
            $attr = array('class'=>'form-control form-control-modif');
            $addlabel = " (as entered by user)";
            $readonly = true;
        } else {
            //this is used by orderinfo form, when the scan order form is shown ($this->params['type']="Multi-Slide Scan Order")
            $attr = array('class' => 'ajax-combobox-optionaluser-research', 'type' => 'hidden');
        }
        
//        $builder->add( 'projectTitle', 'text', array(
//            'label'=>'Research Project Title:',
//            'max_length'=>'500',
//            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif'),
//            'read_only' => $readonly
//        ));
//
//        $builder->add( 'setTitle', 'text', array(
//            'label'=>'Research Set Title:',
//            'max_length'=>'500',
//            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif'),
//            'read_only' => $readonly
//        ));

        //////////////////// project->title ////////////////////
//        $builder->add('projectTitle', null, array(
//            'type' => new AccessionAccessionType($this->params, null),
//            'required' => false,
//        ));
        $builder->add('projectTitle', new ProjectTitleListType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProjectTitleList',
            'label' => false,
            'required' => false,
        ));

        //http://symfony.com/doc/current/cookbook/form/dynamic_form_modification.html#cookbook-form-events-submitted-data

//        $builder->add('projectTitle', 'custom_selector', array(
//            'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
//            'label' => 'Research Project Title:',
//            'classtype' => 'projectTitle'
//        ));

//        $builder
//            ->add('projectTitle', 'entity', array(
//                'class'       => 'OlegOrderformBundle:Research',
//                'empty_value' => '',
//            ));
//        ;
//
//        $formModifier = function( FormInterface $form, ProjectTitleList $project = null ) { //FormInterface
//            //$projectTitles = array("1"=>"1","2"=>"2","3"=>"3");
//
//            //$projectTitles = $projectlist;
//            $settitles = null === $project ? array() : $project->getSetTitles();
//            echo "count=".count($settitles)."<br/>";
//
//            $form->add('setTitles', 'entity', array(
//                'class'       => 'OlegOrderformBundle:ProjectTitleList',
//                'empty_value' => '',
//                'choices'     => $settitles,
//            ));
//        };
//
//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($formModifier) {
//                // this would be your entity, i.e. SportMeetup
//                $data = $event->getData();
//
//                echo "data=".$data."<br \>";
//
//                $formModifier( $event->getForm(), $data->getProjectTitle() );
//            }
//        );
//
//        $builder->get('projectTitle')->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event) use ($formModifier) {
//                // It's important here to fetch $event->getForm()->getData(), as
//                // $event->getData() will get you the client data (that is, the ID)
//                $sport = $event->getForm()->getData();
//
//                // since we've added the listener to the child, we'll have to pass on
//                // the parent to the callback functions!
//                $formModifier($event->getForm()->getParent(), $sport);
//            }
//        );

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//
//                // this would be your entity, i.e. Project Title
//                $data = $event->getData();
//
//                $project = $data->getProjectTitle();
//
//                echo "project=".$project."<br/>";
//
//                $positions = null === $project ? array() : $project->getSetTitle();
//
//                $form->add('setTitle', 'custom_selector', array(
//                    'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
//                    'label' => 'Research Set Title:',
//                    //'choices' => $positions,
//                    'classtype' => 'setTitle',
//                ));
//            }
//        );

        //////////////////// EOF project->title ////////////////////


        //principal
        $builder->add('principalstr', 'custom_selector', array(
            'label' => 'Principal Investigator'.$addlabel.':',
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'optionalUserResearch',
            'read_only' => $readonly
        ));

        //show a user object linked to the research. Show it only for data review.
        if( $this->params['type'] == 'SingleObject' ) {

            $attr = array('class' => 'combobox combobox-width');
            $builder->add('principal', 'entity', array(
                'class' => 'OlegOrderformBundle:User',
                'label'=>'Principal Investigator:',
                'required' => false,
                //'read_only' => true,    //not working => disable by twig
                //'multiple' => true,
                'attr' => $attr,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.locked=:locked')
                        ->setParameter('locked', '0');
                },
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

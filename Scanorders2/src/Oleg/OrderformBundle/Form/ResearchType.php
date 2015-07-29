<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\UserWrapperType;
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

        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "ProjectTitleTree",
                'bundleName' => "OrderformBundle",
                'organizationalGroupType' => "ResearchGroupType"
            );
            if( $title ) {
                $projectTitle = $title->getProjectTitle();
                if( $projectTitle ) {
                    $label = $this->params['em']->getRepository('OlegOrderformBundle:ProjectTitleTree')->getLevelLabels($projectTitle,$mapper) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository('OlegOrderformBundle:ProjectTitleTree')->getLevelLabels(null,$mapper) . ":";
            }
            //echo "label=".$label."<br>";

            $form->add('projectTitle', 'custom_selector', array(
                'label' => $label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree combobox-research-projectTitle',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'OrderformBundle',
                    'data-compositetree-classname' => 'ProjectTitleTree'
                ),
                'classtype' => 'projectTitle'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////


//        $builder->add('principals', 'collection', array(
//            'type' => new PrincipalType($this->params,$this->entity),
//            'required' => false,
//        ));


        echo "this->params['type']=".$this->params['type']."<br>";
        if( $this->params['type'] == 'SingleObject' ) {

//            //data review: we need only edit primary pi and link principals to the existing User objects => all of this is inside of "ProjectTitleList" entity
//            $builder->add( 'projectTitle', new ProjectTitleListType($this->params,$this->entity), array(
//                'label'=>false
//            ));

            $criterion = "user.roles LIKE '%ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR%'";

            $this->params['user.criterion'] = $criterion;   //array('role'=>'ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR');

            $this->params['name.label'] = 'Principal Investigator (as entered by user for this order):';
            $this->params['user.label'] = 'Principal Investigator:';

            $builder->add('userWrappers', 'collection', array(
                'type' => new UserWrapperType($this->params),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__userwrapper__',
            ));


            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $title = $event->getData();
                $form = $event->getForm();

                if( !$title ) {
                    return;
                }

                //echo "id=".$this->entity->getId()."<br>";
                //echo $this->entity;
                //echo "projectTitle id=".$this->entity->getProjectTitle()->getId()."<br>";
                $principalArr = array();
                $userWrappers = array();
                $comment = '';
                if( $title ) {
                    $userWrappers = $title->getUserWrappers();

                    //create array of choices: 'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
                    foreach( $userWrappers as $userWrapper ) {
                        //echo $principal."<br>";
                        $principalArr[$userWrapper->getId()] = $userWrapper->getName();
                    }

                    if( $title->getPrimarySet() ) {
                        $comment = ' for this order';
                    }
                }

//                $form->add('primaryPrincipal', 'choice', array(
//                    'required' => true,
//                    'label'=>'Primary Principal Investigator (as entered by user'.$comment.'):',
//                    'attr' => array('class' => 'combobox combobox-width'),
//                    'choices' => $principalArr,
//                ));

                $form->add( 'primaryPrincipal', 'entity', array(
                    'class' => 'OlegUserdirectoryBundle:UserWrapper',
                    'label'=>'Primary Principal Investigator (as entered by user'.$comment.'):',
                    'required'=> false,
                    'multiple' => false,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'choices' => $userWrappers
//                    'query_builder' => function(EntityRepository $er) {
//
//                            if( array_key_exists('user.criterion', $this->params) ) {
//                                $criterion = $this->params['user.criterion'];
//                            } else {
//                                $criterion = '';
//                            }
//
//                            return $er->createQueryBuilder('user')
//                                ->where($criterion)
//                                ->leftJoin("user.infos","infos")
//                                ->orderBy("infos.displayName","ASC");
//                        },
                ));


                //get all users with Primary Investigator Role
                //$securityUtil = $this->params['container']->get('order_security_utility');
                //$primaryInvestigators = $securityUtil->findByRoles(array('ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR'));





            });


        } else {

//            $builder->add( 'projectTitleStr', 'custom_selector', array(
//                'label' => 'Research Project Title:',
//                'required' => false,
//                //'read_only' => $readonly,
//                'attr' => array('class' => 'combobox combobox-width combobox-research-projectTitle', 'type' => 'hidden'),
//                'classtype' => 'projectTitle'
//            ));
//
//            $builder->add( 'setTitleStr', 'custom_selector', array(
//                'label' => 'Research Set Title:',
//                'required' => false,
//                'attr' => array('class' => 'combobox combobox-width combobox-research-setTitle', 'type' => 'hidden'),
//                //'read_only' => $readonly,
//                'classtype' => 'setTitles'
//            ));

            //$addlabel = " (as entered by user)";
            //TODO: add mask: comma is not allowed
            $builder->add('userWrappers', 'custom_selector', array(
                'label' => 'Principal Investigator(s):',
                'attr' => array('class' => 'combobox combobox-width combobox-optionaluser-research', 'type' => 'hidden'),  //combobox-optionaluser-research
                //'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
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

<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UserPositionType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        echo 'cycle='.$this->params['cycle']."<br>";
//        $readonly = '';
//        if( strpos($this->params['cycle'],'show') !== false ) {
//            $readonly = 'readonly';
//        }
//        echo 'readonly='.$readonly."<br>";

        //hidden: set by js
        $builder->add( 'institution', null, array(
            'label' => false,
            'required'=> false,
            'data' => $this->params['treenode'],
            'attr' => array('class'=>'userposition-institution'),
        ));
//        $this->params['nodeid'] = '1';
//        if( $this->params['treenode'] ) {
//            $this->params['nodeid'] = $this->params['treenode']->getId();
//        }
//        $builder->add( 'institution', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'label' => 'Institution:',
//            'required'=> true,
//            'multiple' => false,
//            //'attr' => array('class'=>'combobox combobox-width userposition-institution'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.id = :nodeid")
//                        ->setParameters( array(
//                            'nodeid' => $this->params['nodeid']
//                        ));
//                },
//        ));

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//                $userPosition = $event->getData();
//                $institution = null;
//
//                if( $userPosition ) {
//                    $institution = $userPosition->getInstitution();
//                }
//
//                if( $institution ) {
//                    $data = $institution;
//                } else {
//                    $data = null;
//                }
//
//                $form->add( 'institution', null, array(
//                    'label' => false,
//                    'required' => false,
//                    'data' => $data
//                ));
//            }
//        );

        //hidden: set by js
//        $builder->add( 'user', null, array(
//            'label' => false,
//            'required' => false,
//            'data' => $this->params['user']
//        ));
        $builder->add( 'user', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => 'User:',
            'required'=> true,
            'multiple' => false,
            //'attr' => array('class'=>'combobox combobox-width userposition-user'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.id = :userid")
                        ->setParameters( array(
                            'userid' => $this->params['user']->getId()
                        ));
                },
        ));

        $attr = array('class'=>'combobox combobox-width userposition-positiontypes');
        //echo 'cycle='.$this->params['cycle']."<br>";
        if( strpos($this->params['cycle'],'show') !== false ) {
            $attr['readonly'] = 'readonly';
            //echo 'readonly!!! <br>';
        }

        //visible as positionType combobox attached to an institution node
        $builder->add( 'positionTypes', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:PositionTypeList',
            'property' => 'name',
            'label'=>'Position Type:',
            'required'=> false,
            'multiple' => true,
            'attr' => $attr,
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

//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $userpos = $event->getData();
//            $form = $event->getForm();
//            if( !$userpos ) {
//                return;
//            }
//            //echo 'userpos count='.coun($userpos)."<br>";
//            //echo 'postypes count='.coun($userpos->getPositionTypes())."<br>";
//            foreach( $userpos->getPositionTypes() as $type ) {
//                echo 'type='.$type."<br>";
//            }
//
//            $attr = array('class'=>'combobox combobox-width userposition-positiontypes');
//            //echo 'cycle='.$this->params['cycle']."<br>";
//            if( strpos($this->params['cycle'],'show') !== false ) {
//                $attr['readonly'] = 'readonly';
//                //echo 'readonly!!! <br>';
//            }
//
//            //visible as positionType combobox attached to an institution node
//            $form->add( 'positionTypes', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:PositionTypeList',
//                'property' => 'name',
//                'label'=>'Position Type:',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => $attr,
//                'data' => $userpos->getPositionTypes(),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            ->where("list.type = :typedef OR list.type = :typeadd")
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setParameters( array(
//                                'typedef' => 'default',
//                                'typeadd' => 'user-added',
//                            ));
//                    },
//            ));
//
//        });


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPosition',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userposition';
    }
}

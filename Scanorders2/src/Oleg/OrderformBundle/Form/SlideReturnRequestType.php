<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class SlideReturnRequestType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        $labels = array(
            'institution' => 'Institution:',
            'destinations' => 'Return Slides to:'
        );

        $this->params['labels'] = $labels;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('orderinfo', new MessageType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\OrderInfo',
            'label' => false
        ));



        $builder->add('urgency', 'custom_selector', array(
            'label' => 'Urgency:',
            'attr' => array('class' => 'ajax-combobox-urgency', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'urgency'
        ));

        $builder->add('slide', 'collection', array(
            'type' => new SlideSimpleType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slides__',
        ));

        //$builder->add( 'provider', new ProviderType(), array('label'=>'Submitter:') );

//        $builder->add('proxyuser', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:User',
//            'label'=>'Ordering Provider:',
//            'required' => false,
//            //'multiple' => true,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('u')
//                        ->where('u.roles LIKE :roles OR u=:user')
//                        ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
//                },
//        ));

//        $builder->add('institution', 'entity', array(
//            'label' => 'Institution:',
//            'required'=> true,
//            'multiple' => false,
//            'empty_value' => false,
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'choices' => $this->params['institutions'],
//            'attr' => array('class' => 'combobox combobox-width combobox-institution')
//        ));


        if( array_key_exists('type', $this->params) &&  $this->params['type'] == 'table' ) {

            //echo "type=table <br>";

            $builder->add('returnoption', 'checkbox', array(
                'label'     => 'Return all slides that belong to listed accession numbers:',
                'required'  => false,
            ));

            $builder->add('datalocker','hidden', array(
                'mapped' => false,
                'label' => false,
                'attr' => array('class' => 'slidereturnrequest-datalocker-field')
                //'required'  => false,
            ));

        }


        ////////////// returnLocation //////////////////////
//        $returnLocationsOptions = array(
//            'label' => "Return Slides to:",
//            'required' => true,
//            'attr' => array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
//            'classtype' => 'location',
//        );
//
//        //locations default and preferred choices
//        if( array_key_exists('returnLocation', $this->params) ) {
//            if( array_key_exists('cycle', $this->params) && $this->params['cycle'] == 'new' ) {
//                $returnLocation = $this->params['returnLocation'];
//                $returnLocationsOptions['data'] = $returnLocation['data']->getId();
//            }
//        }
//
//        if( array_key_exists('cycle', $this->params) === false || $this->params['cycle'] != 'new' ) {
//            $builder->add('returnLocation', 'entity', array(
//                'label' => 'Return Slides to:',
//                'required'=> false,
//                'multiple' => false,
//                'class' => 'OlegUserdirectoryBundle:Location',
//                'attr' => array('class' => 'combobox combobox-width')
//            ));
//        } else {
//            $builder->add('returnLocation', 'employees_custom_selector', $returnLocationsOptions);
//        }
        ////////////// EOF returnLocation //////////////////////



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SlideReturnRequest'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidereturnrequesttype';
    }
}

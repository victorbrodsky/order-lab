<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserRequestApproveType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'id', 'hidden' );

        $builder->add( 'username', 'text', array(
            'label'=>false,
            'required'=> true,
            'attr' => array('class'=>'username'),
        ));
        
//        $builder->add( 'institution', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'property' => 'name',
//            'label'=>false,
//            'required'=> true,
//            'multiple' => true,
//            'attr' => array('class'=>'combobox institution-combobox'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));

        if( array_key_exists('requestedInstitutionalPHIScope', $this->params) ) {
            $requestedInstitutionalPHIScope = $this->params['requestedInstitutionalPHIScope'];
        } else {
            $requestedInstitutionalPHIScope = null;
        }
        //echo "choices=".count($requestedInstitutionalPHIScope)."<br>";
        $builder->add('requestedInstitutionalPHIScope', 'entity', array(
            'label' => 'Institutional PHI Scope:',
            'required'=> true,
            'multiple' => true,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $requestedInstitutionalPHIScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


        //requestedScanOrderInstitutionScope
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $title = $event->getData();
//            $form = $event->getForm();
//
//            $label = 'Institution:';
//            if( $title ) {
//                $institution = $title->getRequestedScanOrderInstitutionScope();
//                if( $institution && $institution->getOrganizationalGroupType() ) {
//                    //echo "PRE_SET_DATA inst id:".$institution->getId().", name=".$institution->getName()."<br>";
//                    $label = $institution->getOrganizationalGroupType()->getName().":";
//                }
//            }
//
//            $form->add('requestedScanOrderInstitutionScope', 'employees_custom_selector', array(
//                'label' => $label,
//                'required' => false,
//                'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//                'classtype' => 'institution'
//            ));
//        });

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\UserRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_userrequesttype';
    }
}

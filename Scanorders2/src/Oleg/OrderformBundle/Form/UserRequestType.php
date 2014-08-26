<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Helper\FormHelper;

class UserRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'cwid', 'text', array(
                'label'=>'WCMC CWID:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif cwid'),
        ));

        //hascwid
        $builder->add( 'hascwid', 'choice', array(
            'label'=>'Do you (the requester) have a CWID username?',
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type hascwid')
        ));

        //password
        $builder->add( 'password', 'password', array(
            'mapped' => false,
            'label'=>'Password:',
            'attr' => array('class' => 'form-control form-control-modif cwid-password')
        ));
        
        $builder->add( 'name', 'text', array(
                'label'=>'Name:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'email', 'email', array(
                'label'=>'* Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control form-control-modif email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', 'text', array(
                'label'=>'Job title:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        //institutions
        $builder->add( 'institution', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                'property' => 'name',
                'label'=>'Institution:',
                'required'=> false,
                'multiple' => true,
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
        
//        $builder->add( 'department', 'text', array(
//                'label'=>'Department:',
//                'required'=> false,
//                'data'=>'Pathology and Laboratory Medicine',
//                'attr' => array('class'=>'form-control form-control-modif'),
//        ));

        //foreach( $this->params['departments'] as $dep ) {
        //    echo "dep=".$dep->getName()."<br>";
        //}


        $builder->add('department', 'entity', array(
            'label' => 'Department:',
            'required'=> true,
            'class' => 'OlegUserdirectoryBundle:Department',
            'choices' => $this->params['departments'],
            'attr' => array('class' => 'combobox combobox-width')
        ));

        //services
//        $attr = array('class' => 'ajax-combobox-service', 'type' => 'hidden');    //new
//        $builder->add('services', 'custom_selector', array(
//            'label' => 'Departmental Service(s):',
//            'attr' => $attr,
//            'required' => false,
//            'classtype' => 'userServices'
//        ));
        $builder->add('services', 'entity', array(
            'label' => 'Service(s):',
            'class' => 'OlegUserdirectoryBundle:Service',
            'choices' => $this->params['services'],
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width')
        ));
        
        $builder->add('request', 'textarea', array(
            'label'=>'Reason for account request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));

//        $attr = array('class' => 'combobox combobox-width ');
//        $builder->add('similaruser', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:User',
//            'label'=>'Access permissions similar to:',
//            'required' => false,
//            //'multiple' => true,
//            'attr' => $attr,
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('u')
//                    ->where('u.username <> :username')
//                    ->setParameter('username', 'system');
//            },
//        ));

        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', 'text', array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add('creationdate');

//        $builder->add("cwidyesno", "choice", array(
//            'mapped' => false,
//            'multiple' => false,
//            'expanded' => true,
//            'label' => false,
//            'choices' => array("Yes"=>"Yes", "No"=>"No"),
//            'attr' => array('class' => 'horizontal_type cwidyesno')
//        ));

        $builder->add( 'referencename', 'text', array(
            'label'=>'Reference Name:',
            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif element-with-tooltip', 'data-toggle'=>'tooltip', 'title' => 'name of your supervisor or of the person who can confirm the validity of your request'),
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referenceemail', 'text', array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencephone', 'text', array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

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

<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/4/16
 * Time: 1:06 PM
 */

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class SimpleUserType extends UserType {


    public function __construct( $params )
    {
        $this->params = $params;

        $this->cycle = $params['cycle'];
        $this->readonly = $params['readonly'];
        //$this->sc = $params['sc'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //keytype
        $this->addKeytype($builder,'Primary Public User ID Type:','combobox combobox-width');


//        $readOnly = true;
//        if( $this->cycle == 'create' || $this->sc->isGranted('ROLE_PLATFORM_ADMIN') ) {
//            $readOnly = false;
//        }

        $builder->add('primaryPublicUserId', null, array(
            'label' => 'Primary Public User ID:',
            //'read_only' => $this->readonly,
            'attr' => array('class'=>'form-control submit-on-enter-field')
        ));

    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return null;
        //return 'oleg_userdirectorybundle_user';
    }

} 
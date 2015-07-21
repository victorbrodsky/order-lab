<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientContactinfoType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $currentUser = true;
        $cycle = $this->params['cycle'];
        $em = $this->params['em'];
        $roleAdmin = true;
        $read_only = false;

        $params = array('read_only'=>$read_only,'admin'=>$roleAdmin,'currentUser'=>$currentUser,'cycle'=>$cycle,'em'=>$em,'institution'=>false);
        $builder->add('field', new LocationType($params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
            'label' => false,
        ));


        //other fields from abstract
        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientContactinfo',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientContactinfo',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patientcontactinfotype';
    }
}

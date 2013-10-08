<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\UserUtil;

class FilterType extends AbstractType
{

    protected $statuses;
    protected $user;

    public function __construct( $statuses = null, $user = null )
    {
        $this->statuses = $statuses;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              

        $builder->add( 'filter', 'choice', array(  
                'label' => 'Filter by Order Status:',
                'max_length'=>50,
                //'choices' =>$this->statuses,  // $this->statuses->name, //$search,
                'choices' => $this->statuses,
                'required' => false,
                //'multiple' => true,
                //'expanded' => true,
                'attr' => array('class' => 'combobox combobox-width')
        ));                       
        
        $builder->add('search', 'text', array(
                'max_length'=>200,
                'required'=>false,
                'label'=>'Search:',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));


//        $services = $this->user->getPathologyServices();
//        //echo "count services=".count($services);
//        //exit();
//
//        $choicesServ = array();
//        $choicesServ[] = 'My Orders';
//        foreach( $services as $service ) {
//            $choicesServ[] = "All ".$service->getName()." Orders";
//        }

        $userUtil = new UserUtil();

        $builder->add('service', 'choice', array(
            'label'     => 'Services',
            'required'  => true,
            'choices' => $userUtil->generateUserPathServices($this->user),
            'attr' => array('class' => 'combobox combobox-width')
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$resolver->setDefaults(array(
            //'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        //));
    }

    public function getName()
    {
        return 'filter_search_box';
    }
}

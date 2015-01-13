<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericListType extends AbstractType
{

    protected $params;
    protected $mapper;

    public function __construct( $params, $mapper )
    {
        $this->params = $params;
        $this->mapper = $mapper;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('list', new ListType($this->params, $this->mapper), array(
            'data_class' => $this->mapper['fullClassName'],
            'label' => false
        ));


        //tree classes
        if( method_exists($this->params['entity'],'getParent') ) {
            $builder->add('parent',null,array(
                'label' => $this->mapper['parentClassName'].':',
                'attr' => array('class' => 'combobox combobox-width', 'readonly'=>'readonly')
            ));
        }

        //Roles
        if( strtolower($this->mapper['className']) == strtolower("Roles") ) {
            $builder->add('alias',null,array(
                'label'=>'Alias:',
                'attr' => array('class' => 'form-control')
            ));
            $builder->add('description',null,array(
                'label'=>'Explanation of Capabilities:',
                'attr' => array('class' => 'textarea form-control')
            ));
            $builder->add('attributes','entity',array(
                'class' => 'OlegUserdirectoryBundle:RoleAttributeList',
                'label' => "Attribute(s):",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false,
            ));
        }

        //Role Attributes
        if( strtolower($this->mapper['className']) == strtolower("RoleAttributeList") ) {
            $builder->add('value',null,array(
                'label'=>'Value:',
                'attr' => array('class' => 'form-control')
            ));
        }



        //Floor: suites, rooms
        if( strtolower($this->mapper['className']) == strtolower("FloorList") ) {
            $builder->add('suites','entity',array(
                'class' => 'OlegUserdirectoryBundle:SuiteList',
                'property' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('rooms','entity',array(
                'class' => 'OlegUserdirectoryBundle:RoomList',
                'property' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }


        //Suite: rooms, departments, buildings
        if( strtolower($this->mapper['className']) == strtolower("SuiteList") ) {
            $builder->add('buildings','entity',array(
                'class' => 'OlegUserdirectoryBundle:BuildingList',
                'label'=>'Building(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('departments','entity',array(
                'class' => 'OlegUserdirectoryBundle:Department',
                'label'=>'Department(s):',
                'required'=> false,
                'multiple' => true,
                'by_reference' => false,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('rooms','entity',array(
                'class' => 'OlegUserdirectoryBundle:RoomList',
                'property' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        //Room: departments, buildings
        if( strtolower($this->mapper['className']) == strtolower("RoomList") ) {
            $builder->add('buildings','entity',array(
                'class' => 'OlegUserdirectoryBundle:BuildingList',
                'label'=>'Building(s):',
                'required'=> false,
                'multiple' => true,
                //'by_reference' => false,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('departments','entity',array(
                'class' => 'OlegUserdirectoryBundle:Department',
                'label'=>'Department(s):',
                'required'=> false,
                'multiple' => true,
                //'by_reference' => false,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }








    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->mapper['fullClassName']
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_'.strtolower($this->mapper['className']);
    }
}

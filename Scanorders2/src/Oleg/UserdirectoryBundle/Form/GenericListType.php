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
            //echo "cycle=".$this->params['cycle']."<br>";
            if( $this->params['cycle'] == "show" ) {
                $attr = array('class' => 'combobox combobox-width', 'readonly'=>'readonly');
            } else {
                $attr = array('class' => 'combobox combobox-width');
            }
            $builder->add('parent',null,array(
                'label' => $this->mapper['parentClassName'].' (Parent):',
                //'attr' => array('class' => 'combobox combobox-width')
                'attr' => $attr
            ));
        }

        //types
        if( method_exists($this->params['entity'],'getTypes') ) {
            //echo "cycle=".$this->params['cycle']."<br>";
            if( $this->params['cycle'] == "show" ) {
                $attr = array('class' => 'combobox combobox-width', 'readonly'=>'readonly');
            } else {
                $attr = array('class' => 'combobox combobox-width');
            }
            $builder->add('types',null,array(
                'label' => $this->mapper['className'].' Type(s):',
                'attr' => $attr
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





        ///////////////// Many To Many relationship /////////////////

        //Department:
        //not editable: suites, rooms
        if( strtolower($this->mapper['className']) == strtolower("Department") ) {
            $builder->add('suites','entity',array(
                'class' => 'OlegUserdirectoryBundle:SuiteList',
                'property' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('rooms','entity',array(
                'class' => 'OlegUserdirectoryBundle:RoomList',
                'property' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        //Floor:
        //not editable: suites, rooms
        if( strtolower($this->mapper['className']) == strtolower("FloorList") ) {
            $builder->add('suites','entity',array(
                'class' => 'OlegUserdirectoryBundle:SuiteList',
                'property' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('rooms','entity',array(
                'class' => 'OlegUserdirectoryBundle:RoomList',
                'property' => 'FullName',
                'label'=>'Room(s):',
                'required'=> false,
                'multiple' => true,
                'disabled' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }


        //Suite: departments, buildings, floors
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
                //'by_reference' => false,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('floors','entity',array(
                'class' => 'OlegUserdirectoryBundle:FloorList',
                'label'=>'Floor(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        //Room: departments, buildings, suite
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
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('suites','entity',array(
                'class' => 'OlegUserdirectoryBundle:SuiteList',
                'property' => 'FullName',
                'label'=>'Suite(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('floors','entity',array(
                'class' => 'OlegUserdirectoryBundle:FloorList',
                'label'=>'Floor(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }

        ///////////////// EOF Many To Many relationship /////////////////






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

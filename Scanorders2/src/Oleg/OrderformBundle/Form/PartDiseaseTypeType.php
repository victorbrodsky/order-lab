<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class PartDiseaseTypeType extends AbstractType
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

        //New in Symfony 2.8: choices is array
        //get array of diseaseTypes
        $repository = $this->params['em']->getRepository('OlegOrderformBundle:DiseaseTypeList');
        $dql = $repository->createQueryBuilder("list")->orderBy("list.orderinlist","ASC");
        $query = $this->params['em']->createQuery($dql);
        $items = $query->getResult();
        $diseaseTypesArr = array();
        foreach( $items as $item ) {
            $diseaseTypesArr[] = $item;
        }
        //echo "count items=".count($diseaseTypesArr)."<br>";
        //exit();

        $builder->add( 'diseaseTypes', 'entity', array(
            'class' => 'OlegOrderformBundle:DiseaseTypeList',
            'label'=>'Type of Disease:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type diseaseType'), //'required' => '0', 'disabled'
            'choices' => $diseaseTypesArr
//            'choices' => function(EntityRepository $er) {
//                    //return $er->createQueryBuilder('list')
//                    //    ->orderBy("list.orderinlist","ASC");
//                    $query = $er->createQueryBuilder('list')
//                        ->orderBy("list.orderinlist","ASC");
//                    $items = $query->getResult();
//                    $itemsArr = array();
//                    foreach( $items as $item ) {
//                        $itemsArr[] = $item;
//                    }
//                    echo "count items=".count($itemsArr)."<br>";
//                    exit();
//                    return $itemsArr;
//                },
        ));

        //get array of diseaseTypes
        $repository = $this->params['em']->getRepository('OlegOrderformBundle:DiseaseOriginList');
        $dql = $repository->createQueryBuilder("list")->orderBy("list.orderinlist","ASC");
        $query = $this->params['em']->createQuery($dql);
        $items = $query->getResult();
        $DiseaseOriginListArr = array();
        foreach( $items as $item ) {
            $DiseaseOriginListArr[] = $item;
        }

        $builder->add( 'diseaseOrigins', 'entity', array(
            'class' => 'OlegOrderformBundle:DiseaseOriginList',
            'label'=>'Origin:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type origin-checkboxes'), //'required' => '0', 'disabled'
            'choices' => $DiseaseOriginListArr
//            'choices' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->orderBy("list.orderinlist","ASC");
//                },
        ));

        $builder->add('primaryOrgan', 'custom_selector', array(
            'label' => 'Primary Site of Origin:',
            'attr' => array('class' => 'ajax-combobox ajax-combobox-organ', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'sourceOrgan'
        ));

        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partdiseasetypetype';
    }
}

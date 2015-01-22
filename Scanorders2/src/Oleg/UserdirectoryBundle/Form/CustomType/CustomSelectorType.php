<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form\CustomType;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericManytomanyTransformer;
use Oleg\UserdirectoryBundle\Form\DataTransformer\ResearchLabTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Oleg\UserdirectoryBundle\Form\DataTransformer\StringTransformer;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class CustomSelectorType extends AbstractType {

    /**
     * @var ObjectManager
     * @var SecurityContext
     */
    private $om;
    private $sc;
    private $serviceContainer;

     /**
     * @param ObjectManager $om
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, SecurityContext $sc, $serviceContainer = null)
    {
        $this->om = $om;
        $this->sc = $sc;
        $this->serviceContainer = $serviceContainer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $username = $this->sc->getToken()->getUser();
        
        $classtype = $options['classtype'];
         
        switch( $classtype ) {
            case "institution":
                $transformer = new GenericTreeTransformer($this->om, $username, 'Institution');
                break;
            case "department":
                $transformer = new GenericTreeTransformer($this->om, $username, 'Department');
                break;
            case "division":
                $transformer = new GenericTreeTransformer($this->om, $username, 'Division');
                break;
            case "service":
                $transformer = new GenericTreeTransformer($this->om, $username, 'Service');
                break;
            case "identifierkeytype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'IdentifierTypeList');
                break;
            case "fellowshiptype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'FellowshipTypeList');
                break;
            case "commentType":
                $transformer = new GenericTreeTransformer($this->om, $username, 'CommentTypeList');
                break;
            case "commentSubType":
                $transformer = new GenericTreeTransformer($this->om, $username, 'CommentSubTypeList');
                break;
            case "researchlab":
                $transformer = new ResearchLabTransformer($this->om, $username, 'ResearchLab');
                break;
            case "location":
                $transformer = new GenericTreeTransformer($this->om, $username, 'Location');
                break;
            case "building":
                $transformer = new GenericTreeTransformer($this->om, $username, 'BuildingList');
                break;
            case "room":
                $transformer = new GenericTreeTransformer($this->om, $username, 'RoomList');
                break;
            case "suite":
                $transformer = new GenericTreeTransformer($this->om, $username, 'SuiteList');
                break;
            case "floor":
                $transformer = new GenericTreeTransformer($this->om, $username, 'FloorList');
                break;
            case "mailbox":
                $transformer = new GenericTreeTransformer($this->om, $username, 'MailboxList');
                break;
            case "effort":
                $transformer = new GenericTreeTransformer($this->om, $username, 'EffortList');
                break;
            case "administrativetitletype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'AdminTitleList');
                break;
            case "appointmenttitletype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'AppTitleList');
                break;

            //training (6 from 8)
            case "trainingmajors":
                $transformer = new GenericManytomanyTransformer($this->om, $username, 'MajorTrainingList');
                break;
            case "trainingminors":
                $transformer = new GenericManytomanyTransformer($this->om, $username, 'MinorTrainingList');
                break;
            case "traininghonors":
                $transformer = new GenericManytomanyTransformer($this->om, $username, 'HonorTrainingList');
                break;
            case "trainingfellowshiptitle":
                $transformer = new GenericTreeTransformer($this->om, $username, 'FellowshipTitleList');
                break;
            case "residencyspecialty":
                $transformer = new GenericTreeTransformer($this->om, $username, 'ResidencySpecialty');
                break;
            case "fellowshipsubspecialty":
                $transformer = new GenericTreeTransformer($this->om, $username, 'FellowshipSubspecialty');
                break;

            default:
                $transformer = new StringTransformer($this->om, $username);
        }
        
        
        $builder->addModelTransformer($transformer);        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selection does not exist',
        ));
        
        $resolver->setRequired(array(
            'classtype',
        ));

//        $resolver->setAllowedTypes(array(
//            'classtype' => 'Doctrine\Common\Persistence\ObjectManager',
//        ));
        
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'employees_custom_selector';
    }


}
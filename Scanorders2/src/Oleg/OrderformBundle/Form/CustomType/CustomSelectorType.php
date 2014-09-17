<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\CustomType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Oleg\OrderformBundle\Form\DataTransformer\StainTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\ProcedureTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\SourceOrganTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\ServiceTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\UserServicesTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\StringTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\ResearchTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\SetTitleTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\PrincipalTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\UserTransformer;
//use Oleg\OrderformBundle\Form\DataTransformer\DepartmentTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\GenericListTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\AccountTransformer;

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
        //echo "classtype=".$classtype."<br>";
         
        switch( $classtype ) {
            case "stain":
                $transformer = new StainTransformer($this->om, $username);
                break;
            case "staintype":
                $transformer = new StainTransformer($this->om, $username);
                break;
            case "procedureType":
                $transformer = new ProcedureTransformer($this->om, $username);
                break;         
            case "sourceOrgan":
                $transformer = new SourceOrganTransformer($this->om, $username);
                break;
            case "service":
                $transformer = new ServiceTransformer($this->om, $this->serviceContainer, $username);
                break;
            case "userServices":
                $transformer = new UserServicesTransformer($this->om, $this->serviceContainer, $username);
                break;
            case "accessiontype":
                $transformer = new AccessionTypeTransformer($this->om, $username);
                break;
            case "mrntype":
                $transformer = new MrnTypeTransformer($this->om, $username);
                break;
            case "scanRegion":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "slideDelivery":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "returnSlide":
                $transformer = new StringTransformer($this->om, $username);
                break; 
            case "partname":
                $transformer = new StringTransformer($this->om, $username);
                break; 
            case "blockname":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "projectTitle":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "setTitles":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "courseTitle":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "lessonTitles":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "urgency":
                $transformer = new StringTransformer($this->om, $username);
                break;
            case "optionalUserEducational":
                $transformer = new PrincipalTransformer($this->om, $username, 'DirectorList');
                break;
            case "optionalUserResearch":
                $transformer = new PrincipalTransformer($this->om, $username, 'PIList');
                break;
            case "institution":
                $transformer = new GenericListTransformer($this->om, $username, 'Institution');
                break;
            case "department":
                $transformer = new GenericListTransformer($this->om, $username, 'Department');
                break;
            case "division":
                $transformer = new GenericListTransformer($this->om, $username, 'Division');
                break;
            case "service":
                $transformer = new GenericListTransformer($this->om, $username, 'Service');
                break;
            case "account":
                $transformer = new AccountTransformer($this->om, $username);
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
        return 'custom_selector';
    }


}
<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Form\CustomType;


use App\UserdirectoryBundle\Form\DataTransformer\DayMonthDateTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\GenericUserTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\StringItemCodeTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\UserWrapperTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
//use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\UserdirectoryBundle\Form\DataTransformer\GenericSelectTransformer;
//use App\UserdirectoryBundle\Form\DataTransformer\IntegerCheckboxTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\MonthYearDateTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\ResearchLabTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\StringTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Form\DataTransformer\GenericManyToManyTransformer;

use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomSelectorType extends AbstractType {

    private $om;
    private $serviceContainer;

    public function __construct(EntityManagerInterface $om, ContainerInterface $serviceContainer)
    {
        $this->om = $om;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $username = $this->serviceContainer->get('user_utility')->getLoggedinUser();
        
        $classtype = $options['classtype'];

        switch( $classtype ) {
            case "institution":
                $params = array('type'=>'Medical');
                $transformer = new GenericTreeTransformer($this->om, $username, 'Institution', null, $params);
                break;
            case "institution-many":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'Institution');
                break;
            case "commenttype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'CommentTypeList');
                break;
            case "messageCategory":
                $transformer = new GenericTreeTransformer($this->om, $username, 'MessageCategory', 'OrderformBundle');
                break;
            case "patientList":
                $transformer = new GenericTreeTransformer($this->om, $username, 'PatientListHierarchy', 'OrderformBundle');
                break;
            case "patientLists":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'PatientListHierarchy', 'OrderformBundle');
                break;
            case "accessionLists":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'AccessionListHierarchy', 'OrderformBundle');
                break;
//            case "institution_id":
//                $params = array('field'=>'id');
//                $transformer = new GenericTreeTransformer($this->om, $username, 'Institution', null, $params);
//                break;
//            case "department":
//                $transformer = new GenericTreeTransformer($this->om, $username, 'Department');
//                break;
//            case "division":
//                $transformer = new GenericTreeTransformer($this->om, $username, 'Division');
//                break;
//            case "service":
//                $transformer = new GenericTreeTransformer($this->om, $username, 'Service');
//                break;
            case "identifierkeytype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'IdentifierTypeList');
                break;
            case "fellowshiptype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'FellowshipTypeList');
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
            case "city":
                $transformer = new GenericTreeTransformer($this->om, $username, 'CityList');
                break;
            case "organization":
                $transformer = new GenericTreeTransformer($this->om, $username, 'OrganizationList');
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
            case "medicaltitletype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'MedicalTitleList');
                break;

            //training (7 from 9)
            case "traininginstitution":
                $params = array('type'=>'Educational');
                $transformer = new GenericTreeTransformer($this->om, $username, 'Institution', null, $params);
                break;
            case "trainingmajors":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'MajorTrainingList');
                break;
            case "trainingminors":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'MinorTrainingList');
                break;
            case "traininghonors":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'HonorTrainingList');
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
            case "globalfellowshipspecialty":
                $transformer = new GenericTreeTransformer($this->om, $username, 'GlobalFellowshipSpecialty', 'FellAppBundle');
                break;
            case "locationusers":
                $transformer = new GenericTreeTransformer($this->om, $username, 'User');
                break;
            case "genericusers":
                $transformer = new GenericUserTransformer($this->om, $username, 'User', 'UserdirectoryBundle', array('multiple'=>true));
                break;
            case "genericuser":
                $transformer = new GenericUserTransformer($this->om, $username, 'User', 'UserdirectoryBundle', array('multiple'=>false));
                break;
            case "jobTitle":
                $transformer = new GenericTreeTransformer($this->om, $username, 'JobTitleList');
                break;
            case "referringProviderSpecialty":
                $transformer = new GenericTreeTransformer($this->om, $username, 'HealthcareProviderSpecialtiesList');
                break;
            case "locationName":
                $transformer = new GenericSelectTransformer($this->om, $username, 'Location');
                break;
            case "usernametype":
                $transformer = new GenericTreeTransformer($this->om, $username, 'UsernameType');
                break;

            case "transresprojecttypes":
                $transformer = new GenericTreeTransformer($this->om, $username, 'ProjectTypeList', 'TranslationalResearchBundle');
                break;

            case "transresitemcodes":
                //$transformer = new GenericTreeTransformer($this->om, $username, 'RequestCategoryTypeList', 'TranslationalResearchBundle');
                $transformer = new StringItemCodeTransformer($this->om, $username, 'RequestCategoryTypeList', 'TranslationalResearchBundle');
                //$transformer = new GenericSelectTransformer($this->om, $username, 'RequestCategoryTypeList', 'TranslationalResearchBundle');
                break;

            //grants
            case "sourceorganization":
                $transformer = new GenericTreeTransformer($this->om, $username, 'SourceOrganization');
                break;

//            //labtesttype
//            case "labtesttype":
//                $transformer = new GenericTreeTransformer($this->om, $username, 'LabTestType', 'OrderformBundle');
//                break;

            //month year date only
            case "month_year_date_only":
                $transformer = new MonthYearDateTransformer($this->om, $username);
                break;

            case "day_month_date_only":
                $transformer = new DayMonthDateTransformer($this->om, $username);
                break;

            case "grant":
                $transformer = new ResearchLabTransformer($this->om, $username, 'Grant');
                break;

            case "learnareas":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'LearnAreaList', 'ResAppBundle');
                break;

            case "specificindividuals":
                $transformer = new UserWrapperTransformer($this->om, $this->serviceContainer, $username, 'UserWrapper');
                break;

//            case "hostedusergroup":
//                $transformer = new GenericManyToManyTransformer($this->om, $username, 'HostedUserGroupList');
//                break;
            case "tenanturls":
                $transformer = new GenericManyToManyTransformer($this->om, $username, 'TenantUrlList');
                break;

            default:
                $transformer = new StringTransformer($this->om, $username);
        }
        
        
        $builder->addModelTransformer($transformer);        
        
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selection does not exist',
            //'widget' => 'choice',
        ));
        
        $resolver->setRequired(array(
            'classtype',
        ));

//        $resolver->setAllowedTypes(array(
//            'classtype' => 'Doctrine\Common\Persistence\ObjectManager',
//        ));
        
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'employees_custom_selector';
    }


    public function getName() : string
    {
        return $this->getBlockPrefix();
    }


}
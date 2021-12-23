<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 11/19/2021
 * Time: 2:07 PM
 */

namespace App\DashboardBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardInit
{
    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }

    public function getDashboardRolesArr() {
        //Dashboards-Site-Administrator-Department-Of-Pathology
        //Dashboards-Chairman-Department-Of-Pathology
        //Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology
        //Dashboards-Administrator-Department-Of-Pathology
        //Dashboards-Associate-Administrator-Department-Of-Pathology
        //Dashboards-Financial-Administrator-Department-Of-Pathology

        //Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology
        //Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology
        //Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology
        //Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology

        $roles = array(

            "ROLE_DASHBOARD_ADMIN" => array(    //key - name
                "Dashboards Administrator",     //0 alias
                "View all dashboards",          //1 description
                90,                             //2 level
                "dashboard",                    //3 sitename
                "Dashboards-Admin"              //4 abbreviation
            ),

            //Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_INFORMATICS_DIRECTOR_PATHOLOGY" => array(
                "Dashboards Medical Director Informatics Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology"
            ),

            //Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_INFORMATICS_PATHOLOGY" => array(
                "Dashboards Manager Informatics Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology"
            ),

            //Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_SYS_ADMIN_INFORMATICS_PATHOLOGY" => array(
                "Dashboards System Administrator Informatics Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology"
            ),

            //Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_SOFTWARE_DEVELOPER_INFORMATICS_PATHOLOGY" => array(
                "Dashboards Software Developer Informatics Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology"
            ),

            //Dashboards-Site-Administrator-Department-Of-Pathology
            "ROLE_DASHBOARD_SITE_ADMIN_PATHOLOGY" => array(
                "Dashboards Site Administrator Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-Site-Administrator-Department-Of-Pathology"
            ),

            //Dashboards-Chairman-Department-Of-Pathology
            "ROLE_DASHBOARD_CHAIRMAN_PATHOLOGY" => array(
                "Dashboards Chairman Pathology Department",
                "View all pathology dashboards",
                80,
                "dashboard",
                "Dashboards-Chairman-Department-Of-Pathology"
            ),

            //Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology
            "ROLE_DASHBOARD_CHAIRMAN_ASSISTANT_PATHOLOGY" => array(
                "Dashboards Chairman Assistant Pathology Department",
                "",
                80,
                "dashboard",
                "Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology"
            ),

            //Dashboards-Administrator-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_PATHOLOGY" => array(
                "Dashboards Administrator Pathology Department",
                "",
                80,
                "dashboard",
                "Dashboards-Administrator-Department-Of-Pathology"
            ),

            //Dashboards-Associate-Administrator-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_ASSOCIATE_PATHOLOGY" => array(
                "Dashboards Administrator Associate Pathology Department",
                "",
                80,
                "dashboard",
                "Dashboards-Associate-Administrator-Department-Of-Pathology"
            ),

            //Dashboards-Financial-Administrator-Department-Of-Pathology
            "ROLE_DASHBOARD_FINANCIAL_ADMIN_PATHOLOGY" => array(
                "Dashboards Financial Administrator Pathology",
                "",
                80,
                "dashboard",
                "Dashboards-Financial-Administrator-Department-Of-Pathology"
            ),

            //all other roles
//            Dashboards-Administrator-of-Grants-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_GRANT_PATHOLOGY" => array(
                "Dashboards Grant Administrator Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Administrator-of-Grants-Department-Of-Pathology"
            ),

//            Dashboards-Administrator-of-Research-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_RESEARCH_PATHOLOGY" => array(
                "Dashboards Research Administrator Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Administrator-of-Research-Department-Of-Pathology"
            ),
//            Dashboards-Administrator-of-Outreach-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_OUTREACH_PATHOLOGY" => array(
                "Dashboards Outreach Administrator Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Administrator-of-Outreach-Department-Of-Pathology"
            ),
//            Dashboards-Administrator-of-Billing-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMIN_BILLING_PATHOLOGY" => array(
                "Dashboards Billing Administrator Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Administrator-of-Billing-Department-Of-Pathology"
            ),
//            Dashboards-Vice-Chair-of-Clinical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_VICE_CHAIR_CLINICAL_PATHOLOGY" => array(
                "Dashboards Vice Chair Clinical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Vice-Chair-of-Clinical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Vice-Chair-of-Anatomic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_VICE_CHAIR_ANATOMIC_PATHOLOGY" => array(
                "Dashboards Vice Chair Anatomic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Vice-Chair-of-Anatomic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Vice-Chair-of-Research-Department-Of-Pathology
            "ROLE_DASHBOARD_VICE_CHAIR_RESEARCH_PATHOLOGY" => array(
                "Dashboards Vice Chair Research Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Vice-Chair-of-Research-Department-Of-Pathology"
            ),
//            Dashboards-Vice-Chair-of-Experimental-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_VICE_CHAIR_EXPERIMENTAL_PATHOLOGY" => array(
                "Dashboards Vice Chair Experimental Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Vice-Chair-of-Experimental-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Vice-Chair-of-Education-Department-Of-Pathology
            "ROLE_DASHBOARD_VICE_CHAIR_EDUCATIONAL_PATHOLOGY" => array(
                "Dashboards Vice Chair Education Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Vice-Chair-of-Education-Department-Of-Pathology"
            ),
//            Dashboards-Residency-Program-Director-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_RESIDENCY_PATHOLOGY" => array(
                "Dashboards Director Residency Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Residency-Program-Director-Department-Of-Pathology"
            ),
//            Dashboards-Residency-Program-Coordinator-Department-Of-Pathology
            "ROLE_DASHBOARD_COORDINATOR_RESIDENCY_PATHOLOGY" => array(
                "Dashboards Coordinator Residency Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Residency-Program-Coordinator-Department-Of-Pathology"
            ),
//            Dashboards-Fellowship-Program-Director-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_FELLOWSHIP_PATHOLOGY" => array(
                "Dashboards Director Fellowship Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Fellowship-Program-Director-Department-Of-Pathology"
            ),
//            Dashboards-Fellowship-Program-Coordinator-Department-Of-Pathology
            "ROLE_DASHBOARD_COORDINATOR_FELLOWSHIP_PATHOLOGY" => array(
                "Dashboards Coordinator Fellowship Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Fellowship-Program-Coordinator-Department-Of-Pathology"
            ),
//            Dashboards-Histology-Lab-Director-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_HISTOLOGY_LAB_PATHOLOGY" => array(
                "Dashboards Director Histology Lab Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Histology-Lab-Director-Department-Of-Pathology"
            ),
//            Dashboards-Chief-Physician-Assistant-Department-Of-Pathology
            "ROLE_DASHBOARD_CHIEF_PHYSICIAN_ASSISTANT_PATHOLOGY" => array(
                "Dashboards Chief Physician Assistant Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Chief-Physician-Assistant-Department-Of-Pathology"
            ),
//            Dashboards-Physician-Assistant-Department-Of-Pathology
            "ROLE_DASHBOARD_PHYSICIAN_ASSISTANT_PATHOLOGY" => array(
                "Dashboards Physician Assistant Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Physician-Assistant-Department-Of-Pathology"
            ),
//            Dashboards-Accessioning-Manager-Department-Of-Pathology
            "ROLE_DASHBOARD_ACCESSIONING_MANAGER_PATHOLOGY" => array(
                "Dashboards Accessioning Manager Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Accessioning-Manager-Department-Of-Pathology"
            ),
//            Dashboards-Chief-Resident-Department-Of-Pathology
            "ROLE_DASHBOARD_CHIEF_RESIDENT_PATHOLOGY" => array(
                "Dashboards Chief Resident Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Chief-Resident-Department-Of-Pathology"
            ),
//            Dashboards-Resident-Department-Of-Pathology
            "ROLE_DASHBOARD_RESIDENT_PATHOLOGY" => array(
                "Dashboards Resident Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Resident-Department-Of-Pathology"
            ),
//            Dashboards-Fellow-Department-Of-Pathology
            "ROLE_DASHBOARD_FELLOW_PATHOLOGY" => array(
                "Dashboards Fellow Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Fellow-Department-Of-Pathology"
            ),
//            Dashboards-Director-of-Surgical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_SURGICAL_PATHOLOGY" => array(
                "Dashboards Director Surgical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Director-of-Surgical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-of-Surgical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_SURGICAL_PATHOLOGY" => array(
                "Dashboards Manager Surgical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-of-Surgical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Assistant-to-Director-of-Surgical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_ASSISTANT_SURGICAL_PATHOLOGY" => array(
                "Dashboards Director Assistant Surgical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Assistant-to-Director-of-Surgical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Director-of-Cytopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_CYTOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Director Cytopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Director-of-Cytopathology-Department-Of-Pathology"
            ),
//            Dashboards-Assistant-to-Director-of-Cytopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_DIRECTOR_ASSISTANT_CYTOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Director Assistant Cytopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Assistant-to-Director-of-Cytopathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-of-Cytopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_CYTOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Cytopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-of-Cytopathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Hematopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_HEMATOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Hematopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Hematopathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Hematopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_HEMATOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Hematopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Hematopathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Molecular-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_MOLECULAR_PATHOLOGY" => array(
                "Dashboards Medical Director Molecular Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Molecular-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Molecular-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_MOLECULAR_PATHOLOGY" => array(
                "Dashboards Manager Molecular Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Molecular-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Genomic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_GENOMIC_PATHOLOGY" => array(
                "Dashboards Medical Director Genomic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Genomic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Genomic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_GENOMIC_PATHOLOGY" => array(
                "Dashboards Manager Genomic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Genomic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Breast-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_BREAST_PATHOLOGY" => array(
                "Dashboards Medical Director Breast Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Breast-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Breast-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_BREAST_PATHOLOGY" => array(
                "Dashboards Manager Breast Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Breast-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Dermatopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_DERMATOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Dermatopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Dermatopathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Dermatopathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_DERMATOPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Dermatopathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Dermatopathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Gastrointestinal-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_GASTROINTESTINAL_PATHOLOGY" => array(
                "Dashboards Medical Director Gastrointestinal Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Gastrointestinal-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Gastrointestinal-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_GASTROINTESTINAL_PATHOLOGY" => array(
                "Dashboards Manager Gastrointestinal Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Gastrointestinal-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Liver-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_LIVER_PATHOLOGY" => array(
                "Dashboards Medical Director Liver Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Liver-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Liver-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_LIVER_PATHOLOGY" => array(
                "Dashboards Manager Liver Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Liver-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Genitourinary-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_GENITOURINARY_PATHOLOGY" => array(
                "Dashboards Medical Director Genitourinary Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Genitourinary-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Genitourinary-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_GENITOURINARY_PATHOLOGY" => array(
                "Dashboards Manager Genitourinary Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Genitourinary-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Gynecologic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_GYNECOLOGIC_PATHOLOGY" => array(
                "Dashboards Medical Director Gynecologic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Gynecologic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Gynecologic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_GYNECOLOGIC_PATHOLOGY" => array(
                "Dashboards Manager Gynecologic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Gynecologic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Perinatal-and-Obstetrical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_PERINATAL_OBSTETRICAL_PATHOLOGY" => array(
                "Dashboards Medical Director Perinatal and Obstetrical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Perinatal-and-Obstetrical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Perinatal-and-Obstetrical-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_PERINATAL_OBSTETRICAL_PATHOLOGY" => array(
                "Dashboards Manager Perinatal and Obstetrical Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Perinatal-and-Obstetrical-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Pediatric-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_PEDIATRIC_PATHOLOGY" => array(
                "Dashboards Medical Director Pediatric Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Pediatric-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Pediatric-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_PEDIATRIC_PATHOLOGY" => array(
                "Dashboards Manager Pediatric Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Pediatric-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Pulmonary-and-Thoracic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_PULMONARY_THORACIC_PATHOLOGY" => array(
                "Dashboards Medical Director Pulmonary and Thoracic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Pulmonary-and-Thoracic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Pulmonary-and-Thoracic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_PULMONARY_THORACIC_PATHOLOGY" => array(
                "Dashboards Manager Pulmonary and Thoracic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Pulmonary-and-Thoracic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Cardicac-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_CARDIAC_PATHOLOGY" => array(
                "Dashboards Medical Director Cardiac Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Cardiac-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Cardiac-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_CARDIAC_PATHOLOGY" => array(
                "Dashboards Manager Cardiac Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Cardiac-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Renal-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_RENAL_PATHOLOGY" => array(
                "Dashboards Medical Director Renal Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Renal-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Renal-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_RENAL_PATHOLOGY" => array(
                "Dashboards Manager Renal Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Renal-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Ophthalmologic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_OPHTHALMOLOGIC_PATHOLOGY" => array(
                "Dashboards Medical Director Ophthalmologic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Ophthalmologic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Ophthalmologic-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_OPHTHALMOLOGIC_PATHOLOGY" => array(
                "Dashboards Manager Ophthalmologic Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Ophthalmologic-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Neuropathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_NEUROPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Neuropathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Neuropathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Neuropathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_NEUROPATHOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Neuropathology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Neuropathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Head-and-Neck-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_HEAD_NECK_PATHOLOGY" => array(
                "Dashboards Medical Director Head and Neck Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Head-and-Neck-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Head-and-Neck-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_HEAD_NECK_PATHOLOGY" => array(
                "Dashboards Manager Head and Neck Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Head-and-Neck-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Bone-and-Soft-Tissue-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_BONE_SOFT_TISSUE_PATHOLOGY" => array(
                "Dashboards Medical Director Bone and Soft Tissue Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Bone-and-Soft-Tissue-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Bone-and-Soft-Tissue-Pathology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_BONE_SOFT_TISSUE_PATHOLOGY" => array(
                "Dashboards Manager Bone and Soft Tissue Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Bone-and-Soft-Tissue-Pathology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Autopsy-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_AUTOPSY_PATHOLOGY" => array(
                "Dashboards Medical Director Autopsy Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Autopsy-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Autopsy-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_AUTOPSY_PATHOLOGY" => array(
                "Dashboards Manager Autopsy Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Autopsy-Department-Of-Pathology"
            ),
//            Dashboards-Diener-Autopsy-Department-Of-Pathology
            "ROLE_DASHBOARD_DIENER_AUTOPSY_PATHOLOGY" => array(
                "Dashboards Diener Autopsy Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Diener-Autopsy-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_INFORMATICS_PATHOLOGY" => array(
                "Dashboards Medical Director Informatics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_INFORMATICS_PATHOLOGY" => array(
                "Dashboards Manager Informatics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology"
            ),
//            Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_SYSADMIN_INFORMATICS_PATHOLOGY" => array(
                "Dashboards SysAdmin Informatics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology"
            ),
//            Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology
            "ROLE_DASHBOARD_SOFTWARE_DEVELOPER_INFORMATICS_PATHOLOGY" => array(
                "Dashboards Software Developer Informatics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Clinical-Chemistry-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_CLINICAL_CHEMISTRY_PATHOLOGY" => array(
                "Dashboards Medical Director Clinical Chemistry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Clinical-Chemistry-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Clinical-Chemistry-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_CLINICAL_CHEMISTRY_PATHOLOGY" => array(
                "Dashboards Manager Clinical Chemistry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Clinical-Chemistry-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Core-Laboratory-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_CORE_LABORATORY_PATHOLOGY" => array(
                "Dashboards Medical Director Core Laboratory Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Core-Laboratory-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Core-Laboratory-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_CORE_LABORATORY_PATHOLOGY" => array(
                "Dashboards Manager Core Laboratory Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Core-Laboratory-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Transfusion-Medicine-and-Cellular-Therapy-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_TRANSFUSION_MEDICINE_CELLULAR_THERAPY_PATHOLOGY" => array(
                "Dashboards Medical Director Transfusion Medicine and Cellular Therapy Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Transfusion-Medicine-and-Cellular-Therapy-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Transfusion-Medicine-and-Cellular-Therapy-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_TRANSFUSION_MEDICINE_CELLULAR_THERAPY_PATHOLOGY" => array(
                "Dashboards Manager Transfusion Medicine and Cellular Therapy Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Transfusion-Medicine-and-Cellular-Therapy-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Transfusion-Medicine-and-Blood-Bank-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_TRANSFUSION_MEDICINE_BLOOD_BANK_PATHOLOGY" => array(
                "Dashboards Medical Director Transfusion Medicine and Blood Bank Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Transfusion-Medicine-and-Blood-Bank-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Transfusion-Medicine-and-Blood-Bank-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_TRANSFUSION_MEDICINE_BLOOD_BANK_PATHOLOGY" => array(
                "Dashboards Manager Transfusion Medicine and Blood Bank Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Transfusion-Medicine-and-Blood-Bank-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Routine-and-Special-Coagulation-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_ROUTINE_SPECIAL_COAGULATION_PATHOLOGY" => array(
                "Dashboards Medical Director Routine and Special Coagulation Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Routine-and-Special-Coagulation-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Routine-and-Special-Coagulation-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_ROUTINE_SPECIAL_COAGULATION_PATHOLOGY" => array(
                "Dashboards Manager Routine and Special Coagulation Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Routine-and-Special-Coagulation-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Endocrinology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_ENDOCRINOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Endocrinology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Endocrinology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Endocrinology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_ENDOCRINOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Endocrinology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Endocrinology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Routine-and-Special-Hematology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_ROUTINE_SPECIAL_HEMATOLOGY" => array(
                "Dashboards Medical Director Routine and Special Hematology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Routine-and-Special-Hematology"
            ),
//            Dashboards-Manager-Routine-and-Special-Hematology
            "ROLE_DASHBOARD_MANAGER_ROUTINE_SPECIAL_HEMATOLOGY" => array(
                "Dashboards Manager Routine and Special Hematology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Routine-and-Special-Hematology"
            ),
//            Dashboards-Medical-Director-Immunochemistry-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_IMMUNOCHEMISTRY_PATHOLOGY" => array(
                "Dashboards Medical Director Immunochemistry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Immunochemistry-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Immunochemistry-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_IMMUNOCHEMISTRY_PATHOLOGY" => array(
                "Dashboards Manager Immunochemistry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Immunochemistry-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Immunology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_IMMUNOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Immunology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Immunology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Immunology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_IMMUNOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Immunology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Immunology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Immunobiology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_IMMUNOBIOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Immunobiology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Immunobiology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Immunobiology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_IMMUNOBIOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Immunobiology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Immunobiology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Serology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_SEROLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Serology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Serology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Serology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_SEROLOGY_PATHOLOGY" => array(
                "Dashboards Manager Serology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Serology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Flow-Cytometry-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_FLOW_CYTOMETRY_PATHOLOGY" => array(
                "Dashboards Medical Director Flow Cytometry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Flow-Cytometry-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Flow-Cytometry-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_FLOW_CYTOMETRY_PATHOLOGY" => array(
                "Dashboards Manager Flow Cytometry Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Flow-Cytometry-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Immunohematology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_IMMUNOHEMATOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Immunohematology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Immunohematology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Immunohematology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_IMMUNOHEMATOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Immunohematology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Immunohematology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Microbiology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_MICROBIOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Microbiology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Microbiology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Microbiology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_MICROBIOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Microbiology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Microbiology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Molecular-Diagnostics-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_MOLECULAR_DIAGNOSTICS_PATHOLOGY" => array(
                "Dashboards Medical Director Molecular Diagnostics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Molecular-Diagnostics-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Molecular-Diagnostics-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_MOLECULAR_DIAGNOSTICS_PATHOLOGY" => array(
                "Dashboards Manager Molecular Diagnostics Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Molecular-Diagnostics-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Toxicology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_TOXICOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Toxicology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Toxicology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Toxicology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_TOXICOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Toxicology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Toxicology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Mycology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_MYCOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Mycology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Mycology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Mycology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_MYCOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Mycology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards"
            ),
//            Dashboards-Medical-Director-Therapeutic-Drug-Monitoring-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_THERAPEUTIC_DRUG_MONITORING_PATHOLOGY" => array(
                "Dashboards Medical Director Therapeutic Drug Monitoring Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Therapeutic-Drug-Monitoring-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Therapeutic-Drug-Monitoring-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_THERAPEUTIC_DRUG_MONITORING_PATHOLOGY" => array(
                "Dashboards Manager Therapeutic Drug Monitoring Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Therapeutic-Drug-Monitoring-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Parasitology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_PARASITOLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Parasitology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Parasitology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Parasitology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_PARASITOLOGY_PATHOLOGY" => array(
                "Dashboards Manager Parasitology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Parasitology-Department-Of-Pathology"
            ),
//            Dashboards-Medical-Director-Virology-Department-Of-Pathology
            "ROLE_DASHBOARD_MEDICAL_DIRECTOR_VIROLOGY_PATHOLOGY" => array(
                "Dashboards Medical Director Virology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Medical-Director-Virology-Department-Of-Pathology"
            ),
//            Dashboards-Manager-Virology-Department-Of-Pathology
            "ROLE_DASHBOARD_MANAGER_VIROLOGY_PATHOLOGY" => array(
                "Dashboards Manager Virology Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Manager-Virology-Department-Of-Pathology"
            ),
//            Dashboards-Course-Director-for-Medical-Students-Department-Of-Pathology
            "ROLE_DASHBOARD_COURSE_DIRECTOR_MEDICAL_STUDENTS_PATHOLOGY" => array(
                "Dashboards Course Director for Medical Students Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Course-Director-for-Medical-Students-Department-Of-Pathology"
            ),
//            Dashboards-Course-Director-for-Postgraduate-Education-Department-Of-Pathology
            "ROLE_DASHBOARD_COURSE_DIRECTOR_POSTGRADUATE_EDUCATION_PATHOLOGY" => array(
                "Dashboards Course Director for Postgraduate Education Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Course-Director-for-Postgraduate-Education-Department-Of-Pathology"
            ),
//            Dashboards-Administrator-Quality-Assurance-Department-Of-Pathology
            "ROLE_DASHBOARD_ADMINISTRATOR_QUALITY_ASSURANCE_PATHOLOGY" => array(
                "Dashboards Administrator Quality Assurance Department Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-Administrator-Quality-Assurance-Department-Of-Pathology"
            ),
//            Dashboards-TRP-Administrator-Department-of-Pathology
            "ROLE_DASHBOARD_ADMINISTRATOR_TRP_PATHOLOGY" => array(
                "Dashboards TRP Administrator Pathology",
                "",
                50,
                "dashboard",
                "Dashboards-TRP-Administrator-Department-of-Pathology"
            ),

        );

        return $roles;
    }

    public function getCharts() {
        $repository = $this->em->getRepository('AppDashboardBundle:ChartList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.institutions", "institutions");
        $dql->where("list.type = :typedef OR list.type = :typeadd");
        //$dql->andWhere("institutions IS NOT NULL");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        return $charts;
    }

    //add institutions, topics, roles to the charts
    public function initCharts( $testing=false ) {

        //return NULL;

        $resInst = 0;
        $resInst = $this->assignInstitutionsToCharts($testing);

        $resTopic = 0;
        $resTopic = $this->assignTopicsToCharts($testing);

        $resRole = 0;
        $resRole = $this->assignRolesToCharts($testing);

        $resAdd = 0;
        $resAdd = $this->assignAdditionalTopicsAndRolesToCharts($testing);

        $resTypes = 0;
        $resTypes = $this->assignTypesToCharts($testing);

        return $resInst + $resTopic + $resRole + $resAdd + $resTypes;
    }

    public function assignInstitutionsToCharts( $testing=false ) {
        //4- Set all charts except 57, 58, 59, 62, 63
        //to the “Institution” of:
        //Weill Cornell Medical College > Pathology and Laboratory Medicine > Center for Translational Pathology
        $exceptionTrpStrArr = array("57. ", "58. ", "59. ", "62. ", "63. ");

//        5- Set charts 57, 58, 59
//        to the institution of:
//        Weill Cornell Medical College > Pathology and Laboratory Medicine > Pathology Informatics
        $pathInformaticsArr = array("57. ", "58. ", "59. ");

//        6- Set charts 62, 63
//        to the institution of:
//        Weill Cornell Medical College > Pathology and Laboratory Medicine
        $pathologyArr = array("62. ", "63. ");

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );
        $wcmc = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('No Institution: "WCM"');
        }
        if( $wcmc->getLevel() != 0 ) {
            exit('Institution "WCM" level is not 0');
        }
        $pathology = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        $trp = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Center for Translational Pathology",
            $pathology,
            $mapper
        );
        if( !$trp ) {
            exit("Institution not found by name 'Center for Translational Pathology'");
        }

        $informatics = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology Informatics",
            $pathology,
            $mapper
        );
        if( !$informatics ) {
            exit("Institution not found by name 'Pathology Informatics'");
        }

        $charts = $this->getCharts();

        $count = 0;

        foreach($charts as $chart) {

            $processFlag = true;
            $chartName = $chart->getName();
            $chartInstitutions = $chart->getInstitutions();

            echo "Process chart $chartName <br>";

            ///////////// 4 set all charts except 57, 58, 59, 62, 63 to 'Center for Translational Pathology' /////////////
            foreach( $exceptionTrpStrArr as $exceptionTrpStr ) {
                //if (strpos($chartName, $exceptionTrpStr) !== false) {
                if( $this->compareChartName($chartName,$exceptionTrpStr) ) {
                    //echo 'true';
                    $processFlag = false;
                    break;
                }

//                if ($trp && !$chartInstitutions->contains($trp)) {
//                    $chart->addInstitution($trp);
//                    echo "- Added TRP <br>";
//                    $count++;
//                }
            }

            if( $processFlag ) {
                //skip chart
                //continue;

                //4 add Center for Translational Pathology
                if ($trp && !$chartInstitutions->contains($trp)) {
                    $chart->addInstitution($trp);
                    echo "- 1Added TRP <br>";
                    $count++;
                }
            }
            ///////////// EOF 4 set all charts except 57, 58, 59, 62, 63 to 'Center for Translational Pathology' /////////////

            //////////// 5 - Set charts 57, 58, 59 to 'Pathology Informatics' ////////////
            foreach( $pathInformaticsArr as $pathInformaticsPartName ) {
                //if (strpos($chartName, $pathInformaticsPartName) !== false) {
                if( $this->compareChartName($chartName,$pathInformaticsPartName) ) {
                    //echo 'true';
                    if ($informatics && !$chartInstitutions->contains($informatics)) {
                        $chart->addInstitution($informatics);
                        echo "-- 2Added Institution $informatics<br>";
                        $count++;
                    } else {
                        echo "-- 2Already exists Institution $informatics<br>";
                    }
                    break;
                }
            }
            //////////// EOF 5 - Set charts 57, 58, 59 to 'Pathology Informatics' ////////////


            //////////// 6- Set charts 62, 63 to Pathology and Laboratory Medicine ////////////
            foreach( $pathologyArr as $pathologyPartName ) {
                //if (strpos($chartName, $pathologyPartName) !== false) {
                if( $this->compareChartName($chartName,$pathologyPartName) ) {
                    //echo 'true';
                    if ($pathology && !$chartInstitutions->contains($pathology)) {
                        $chart->addInstitution($pathology);
                        echo "--- 3Added Institution $pathology<br>";
                        $count++;
                    } else {
                        echo "--- 3Already exists Institution $pathology<br>";
                    }
                    break;
                }
            }
            //////////// EOF 6- Set charts 62, 63 to Pathology and Laboratory Medicine ////////////

        }//foreach

        if( $count > 0 ) {
            if( !$testing ) {
                $this->em->flush();
            }
        }

        //exit('Added Institutions count='.$count);
        return $count;
    }

    function assignTopicsToCharts( $testing=false ) {

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'DashboardBundle',
            'className' => 'TopicList'
        );
        $root = $this->em->getRepository('AppDashboardBundle:TopicList')->findOneByName("All Charts");
        if( !$root ) {
            exit('No Root: "All Charts"');
        }
        if( $root->getLevel() != 0 ) {
            exit('Root "All Charts" level is not 0');
        }

        $charts = $this->getCharts();

        //7- topics 
        //1) Financial > Translational Research
        $charts1Arr = array();
        //16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 47, 48, 49, 50, 51, 52, 55, 64, 65
        $addChart1Arr = array("16. ", "17. ","18. ","19. ","20. ","21. ","22. ","23. ","24. ",
            "25. ","26. ","27. ","28. ","29. ","47. ","48. ","49. ","50. ","51. ","52. ","55. ","64. ","65. ");
        $financial = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Financial",
            $root,
            $mapper
        );
        if( !$financial ) {
            exit("Error: not found: Financial");
        }
        $financialTrp = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Translational Research",
            $financial,
            $mapper
        );
        if( !$financialTrp ) {
            exit("Error: not found: Financial > Translational Research");
        } else {
            echo "Financial > Translational Research ID=".$financialTrp->getId()."<br>";
        }
        //$financialTrpArr = array();//testing

        //2) Productivity > Turnaround Times > Translational Research
        $charts2Arr = array();
        //32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 53, 54, 55
        $addChart2Arr = array("32. ","33. ","34. ","35. ","36. ","37. ","38. ","39. ","40. ","41. ","53. ","54. ","55. ");
        $productivity = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Productivity",
            $root,
            $mapper
        );
        if( !$productivity ) {
            exit("Error: not found: Productivity");
        }
        $productivityTurntime = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Turnaround Times",
            $productivity,
            $mapper
        );
        if( !$productivityTurntime ) {
            exit("Error: not found: Productivity>Turnaround Times");
        }
        $productivityTurntimeTrp = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Translational Research",
            $productivityTurntime,
            $mapper
        );
        if( !$productivityTurntimeTrp ) {
            exit("Error: not found: Productivity > Turnaround Times > Translational Research");
        } else {
            echo "Productivity > Turnaround Times > Translational Research ID=".$productivityTurntimeTrp->getId()."<br>";
        }
        //exit('111');

        //3) Productivity > Translational Research
        $charts3Arr = array();
        //8, 9, 10, 11, 12, 13, 14, 15, 30, 31, 42, 43, 44, 45, 46, 56
        $addChart3Arr = array("8. ","9. ","10. ","11. ","12. ","13. ","14. ","15. ","30. ","31. ","42. ","43. ","44. ","45. ","46. ","56. ");
        $productivityTrp = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Translational Research",
            $productivity,
            $mapper
        );
        if( !$productivityTrp ) {
            exit("Error: not found: Productivity > Translational Research");
        }

        //4) Productivity > Pathologist Involvement in Translational Research
        $charts4Arr = array();
        //5, 6, 7, 26, 27, 28, 29
        $addChart4Arr = array("5. ", "6. ", "7. ", "26. ", "27. ", "28. ", "29. ");
        $productivityPath = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Pathologist Involvement in Translational Research",
            $productivity,
            $mapper
        );
        if( !$productivityPath ) {
            exit("Error: not found: Productivity > Pathologist Involvement in Translational Research");
        }

        //5) Research > Translational Projects
        $charts5Arr = array();
        //1, 2, 3, 4, 5
        $addChart5Arr = array("1. ", "2. ", "3. ", "4. ", "5. ");
        $research = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Research",
            $root,
            $mapper
        );
        if( !$research ) {
            exit("Error: not found: Research");
        }
        $researchTrp = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Translational Projects",
            $research,
            $mapper
        );
        if( !$researchTrp ) {
            exit("Error: not found: Translational Projects");
        }

        //6) Site Utilization > Platform
        $charts6Arr = array();
        //57, 58, 59
        $addChart6Arr = array("57. ", "58. ", "59. ");
        $siteutil = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Site Utilization",
            $root,
            $mapper
        );
        if( !$siteutil ) {
            exit("Error: not found: Site Utilization");
        }
        $siteutilPlatform = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Platform",
            $siteutil,
            $mapper
        );
        if( !$siteutilPlatform ) {
            exit("Error: not found: Platform");
        }

        //7) Site Utilization > Call Log
        $charts7Arr = array();
        //62, 63
        $addChart7Arr = array("62. ", "63. ");
        $siteutilCalllog = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Call Log",
            $siteutil,
            $mapper
        );
        if( !$siteutilCalllog ) {
            exit("Error: not found: Call Log");
        }

        //8) 21- assign the appropriate Educational topic to charts 60 and 61
        $charts8Arr = array();
        $addChart8Arr = array("60. ", "61. ");
        $educational = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Educational",
            $root,
            $mapper
        );
        if( !$educational ) {
            exit("Error: not found: Educational");
        }
        $educationalFellapp = $this->em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
            "Fellowship Candidate Statistics",
            $educational,
            $mapper
        );
        if( !$educationalFellapp ) {
            exit("Error: not found: Educational->Fellowship Candidate Statistics");
        }

        $count = 0;

        foreach($charts as $chart) {

//            if( $chart->getName() == '55. Number of reminder emails sent per month (linked)' ) {
//                foreach($chart->getTopics() as $topic) {
//                    echo $chart->getName().": topic=".$topic->getTreeName()."<br>";
//                }
//            }

            //$chartId = $chart->getId();
            //$chartName = $chart->getName();

            //1) Financial > Translational Research
            //echo "Try to add topic financialTrp ID=".$financialTrp->getId()."<br>";
            $charts1Arr = $this->addSpecificTopic($chart,$financialTrp,$addChart1Arr,$charts1Arr);

            //2) Productivity > Turnaround Times > Translational Research
            //echo "Try to add topic productivityTurntimeTrp ID=".$productivityTurntimeTrp->getId()."<br>";
            $charts2Arr = $this->addSpecificTopic($chart,$productivityTurntimeTrp,$addChart2Arr,$charts2Arr);

            //3) Productivity > Translational Research
            $charts3Arr = $this->addSpecificTopic($chart,$productivityTrp,$addChart3Arr,$charts3Arr);

            //4) Productivity > Pathologist Involvement in Translational Research
            $charts4Arr = $this->addSpecificTopic($chart,$productivityPath,$addChart4Arr,$charts4Arr);

            //5) Research > Translational Projects
            $charts5Arr = $this->addSpecificTopic($chart,$researchTrp,$addChart5Arr,$charts5Arr);

            //6) Site Utilization > Platform
            $charts6Arr = $this->addSpecificTopic($chart,$siteutilPlatform,$addChart6Arr,$charts6Arr);

            //7) Site Utilization > Call Log
            $charts7Arr = $this->addSpecificTopic($chart,$siteutilCalllog,$addChart7Arr,$charts7Arr);

            //8) Educational->Fellowship Candidate Statistics
            $charts8Arr = $this->addSpecificTopic($chart,$educationalFellapp,$addChart8Arr,$charts8Arr);

//            if( $chart->getName() == '55. Number of reminder emails sent per month (linked)' ) {
//                foreach($chart->getTopics() as $topic) {
//                    echo $chart->getName().": topic=".$topic->getTreeName()."<br>";
//                }
//            }
        }

//        if( count($charts1Arr) != count($addChart1Arr) ) {
//            exit("Count error 1");
//        }
//        if( count($charts2Arr) != count($addChart2Arr) ) {
//            exit("Count error 2");
//        }
//        if( count($charts3Arr) != count($addChart3Arr) ) {
//            exit("Count error 3");
//        }
//        if( count($charts4Arr) != count($addChart4Arr) ) {
//            exit("Count error 4");
//        }
//        if( count($charts5Arr) != count($addChart5Arr) ) {
//            exit("Count error 5");
//        }
//        if( count($charts6Arr) != count($addChart6Arr) ) {
//            exit("Count error 6");
//        }
//        if( count($charts7Arr) != count($addChart7Arr) ) {
//            exit("Count error 7");
//        }

        //echo "";


        $count = count($charts1Arr) + count($charts2Arr) + count($charts3Arr) + count($charts4Arr) + count($charts5Arr)
        +count($charts6Arr)+count($charts7Arr)+count($charts8Arr);

        if( $count > 0 ) {
            if( $testing ) {
                dump($charts1Arr);
                dump($charts2Arr);
                dump($charts3Arr);
                dump($charts4Arr);
                dump($charts5Arr);
                dump($charts6Arr);
                dump($charts7Arr);
                dump($charts8Arr);
            } else {
                $this->em->flush();
            }
        }

        //exit('Added Topics count='.$count);
        return $count;
    }
    function addSpecificTopic( $chart, $addTopic, $chartPartialNameArr, $chartsResArr ) {
        $chartName = $chart->getName();
        foreach( $chartPartialNameArr as $partialName ) {
            if( $this->compareChartName($chartName,$partialName) ) {
                if( $this->chartHasTopic($chart,$addTopic) === false ) {
                    $chart->addTopic($addTopic);
                    $chartsResArr[] = $chartName;
                }

                break;
            }
        }
        return $chartsResArr;
    }
    function compareChartName( $chartName, $partialName ) {
        $partNameArr = explode(". ",$chartName);
        $firstPartName = $partNameArr[0]; //65
        $firstPartName = $firstPartName . ". ";
        //echo "Compare: [$partialName]?=[$firstPartName] <br>";
        if( $partialName == $firstPartName ) {
            return true;
        }
        return false;
    }
    function chartHasTopic( $chart, $topic ) {

        $topics = $chart->getTopics();
        if( $topic && $topics->contains($topic) ) {
            return true;
        }

//        foreach($chart->getTopics() as $thisTopic) {
//            //echo $chart->getName().": topic=".$topic->getTreeName()."<br>";
//            if( $topic->getId() === $thisTopic->getId() ) {
//                return true;
//            }
//        }

        return false;
    }

    //8- Add all roles listed in the previous ticket
    function assignRolesToCharts( $testing=false ) {
        $count = 0;

        //1) 9- Assign all charts except 57, 58, 59, 62, 63 the roles of
        // “Dashboards-Administrator-of-Research-Department-Of-Pathology” and
        // “Dashboards-Administrator-Department-Of-Pathology” and
        // “Dashboards-Chairman-Department-Of-Pathology”
        // to enable uses with those roles to view the dashboards.
        $charts1Arr = array();
        $addChart1Arr = array(57, 58, 59, 62, 63);
        $roles1Arr = array(
            //Abbreviation
            "Dashboards-Administrator-of-Research-Department-Of-Pathology",
            "Dashboards-Administrator-Department-Of-Pathology",
            "Dashboards-Chairman-Department-Of-Pathology"
        );

        //10- Give the charts 57, 58, 59, 62, 63 the role of
        // “Dashboards-Administrator-Department-Of-Pathology” and
        // “Dashboards-Chairman-Department-Of-Pathology”
        $charts2Arr = array();
        $addChart2Arr = array(57, 58, 59, 62, 63);
        $roles2Arr = array(
            "Dashboards-Administrator-Department-Of-Pathology",
            "Dashboards-Chairman-Department-Of-Pathology"
        );

        //13- Give the charts 62, 63 the role of “Dashboards-Vice-Chair-of-Clinical-Pathology-Department-Of-Pathology”
        $charts3Arr = array();
        $addChart3Arr = array(62, 63);
        $roles3Arr = array(
            "Dashboards-Vice-Chair-of-Clinical-Pathology-Department-Of-Pathology"
        );

        //23- Give all charts the role of “Dashboards-Associate-Administrator-Department-Of-Pathology”
        $roleAdminAbbreviation = "Dashboards-Associate-Administrator-Department-Of-Pathology";
        $roleAdmin = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByAbbreviation($roleAdminAbbreviation);
        if( !$roleAdmin ) {
            exit("Role not found by abbreviation=".$roleAdminAbbreviation);
        }
        $resAdminArr = array();

        $charts = $this->getCharts();
        foreach( $charts as $chart ) {
            $charts1Arr = $this->addSpecificRoles($chart,$roles1Arr,$addChart1Arr,$charts1Arr);
            $charts2Arr = $this->addSpecificRoles($chart,$roles2Arr,$addChart2Arr,$charts2Arr);
            $charts3Arr = $this->addSpecificRoles($chart,$roles3Arr,$addChart3Arr,$charts3Arr);

            //23- Give all charts the role of “Dashboards-Associate-Administrator-Department-Of-Pathology”
            if( $chart->addAccessRole($roleAdmin) ) {
                //echo $chart.": add admin role <br>";
                $resAdminArr[] = $chart->getName();
            }
        }

        $count = count($charts1Arr) + count($charts2Arr) + count($charts3Arr) + count($resAdminArr);

        if( $count > 0 ) {
            if( $testing ) {
                dump($charts1Arr);
                dump($charts2Arr);
                dump($charts3Arr);
                dump($resAdminArr);
            } else {
                $this->em->flush();
            }
        }

        //exit('Added Roles count='.$count);
        return $count;
    }
    function addSpecificRoles( $chart, $rolesArr, $chartPartialNameArr, $chartsResArr ) {
        $chartName = $chart->getName();
        foreach( $chartPartialNameArr as $partialName ) {
            if( $this->compareChartNameByInt($chartName,$partialName) ) {
                foreach($rolesArr as $roleAbbreviation) {
                    $role = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByAbbreviation($roleAbbreviation);
                    if( !$role ) {
                        exit("Role not found by abbreviation=".$roleAbbreviation);
                    }
                    if ($this->chartHasRole($chart, $role) === false) {
                        $chart->addAccessRole($role);
                        $chartsResArr[] = $chartName;
                    }
                }

                break;
            }
        }
        return $chartsResArr;
    }
    function compareChartNameByInt( $chartName, $partialName ) {
        //$chartName: 55. Number of reminder emails sent per month (linked)
        //$partialName: 55
        $partNameArr = explode(". ",$chartName);
        $firstPartName = $partNameArr[0]; //55
        $firstPartName = $firstPartName . "";
        //echo "Compare: [$partialName]?=[$firstPartName] <br>";
        if( $partialName == $firstPartName ) {
            return true;
        }
        return false;
    }
    //check if role is in accessRoles
    function chartHasRole( $chart, $role ) {
        $roles = $chart->getAccessRoles();
        if( $role && $roles->contains($role) ) {
            return true;
        }
        return false;
    }

    function assignAdditionalTopicsAndRolesToCharts( $testing=false ) {

        //From #252 (12)
        //add the nine charts 55, 56, 57, 58, 59, 60, 61, 62, 63 (with IDs of 1 through 9) to the topic of “Site Utilization”,
        // associated with the Organizational Group of “Department of Pathology” under WCMC and “Department of Pathology” under NYP”,
        // visible to the these roles:
        //Dashboards-Site-Administrator-Department-Of-Pathology
        //Dashboards-Chairman-Department-Of-Pathology
        //Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology
        //Dashboards-Administrator-Department-Of-Pathology
        //Dashboards-Associate-Administrator-Department-Of-Pathology
        //Dashboards-Financial-Administrator-Department-Of-Pathology

        //From #252 (7)
        //Add the Site Utilization charts into this list (accessible and downloadable):
        //        Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology
        //        Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology
        //        Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology
        //        Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology

        //exit('assignAdditionalTopicsAndRolesToCharts disable');

        $em = $this->em;

        $roles = array(
            "Dashboards-Site-Administrator-Department-Of-Pathology",
            "Dashboards-Chairman-Department-Of-Pathology",
            "Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology",
            "Dashboards-Administrator-Department-Of-Pathology",
            "Dashboards-Associate-Administrator-Department-Of-Pathology",
            "Dashboards-Financial-Administrator-Department-Of-Pathology",

            "Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology",
        );

        $rolesArr = array();
        foreach($roles as $role) {
            $roleEntity = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByAbbreviation($role);
            if( !$roleEntity ) {
                exit("Can not find role by abbreviation '$role'");
            }

            $rolesArr[] = $roleEntity;
        }

        $siteUtilizationTopic = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName("Site Utilization");
        if( !$siteUtilizationTopic ) {
            exit("TopicList not found by name 'Site Utilization'");
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );
        $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('No Institution: "WCM"');
        }
        if( $wcmc->getLevel() != 0 ) {
            exit('Institution "WCM" level is not 0');
        }
        $pathology = $em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );
        if( !$pathology ) {
            exit('No Institution: "WCM Pathology and Laboratory Medicine"');
        }

        //“Department of Pathology” under NYP”
        $nyp = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        if( !$nyp ) {
            exit('No Institution: "NYP"');
        }
        if( $nyp->getLevel() != 0 ) {
            exit('Institution "NYP" level is not 0');
        }
        $nypPathology = $em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $nyp,
            $mapper
        );
        if( !$nypPathology ) {
            exit('No Institution: "NYP Pathology and Laboratory Medicine"');
        }

                     //55, 56, 57, 58, 59, 60, 61, 62, 63
        $names = array(55, 56, 57, 58, 59, 60, 61, 62, 63);
        //$names = array(55, 56, 57, 58, 59, 62, 63);

        $repository = $em->getRepository('AppDashboardBundle:ChartList');
        $dql =  $repository->createQueryBuilder("list");

        $dql->leftJoin('list.topics','topics');
        $dql->leftJoin('list.institutions','institutions');
        $dql->leftJoin('list.accessRoles','accessRoles');
        $dql->leftJoin('list.downloadRoles','downloadRoles');

        $selectArr = array();
        foreach($names as $name) {
            $selectArr[] = "list.name LIKE '".$name.".%'";
        }

        $selectWhere = implode(" OR ",$selectArr);

        $dql->where($selectWhere);

        if( 0 ) {
            $dql->andWhere("topics IS NULL");
            $dql->andWhere("institutions IS NULL");
            $dql->andWhere("accessRoles IS NULL");
            $dql->andWhere("downloadRoles IS NULL");
        }

        $query = $dql->getQuery();

        $charts = $query->getResult();
        echo "charts count=".count($charts)."<br>";

        $totalCount = 0;
        $processedCharts = array();

        foreach($charts as $chart) {
            echo "<br>Process chart '$chart' <br>";

            $count = 0;

            //add topic
            $resTopic = $chart->addTopic($siteUtilizationTopic);
            if( $resTopic ) {
                echo "Topic added ";
                $count++;
            }

            //add institution
            $resInst = $chart->addInstitution($pathology);
            if( $resInst ) {
                echo "WCM Pathology added ";
                $count++;
            }

            $resInst = $chart->addInstitution($nypPathology);
            if( $resInst ) {
                echo "NYP Pathology added ";
                $count++;
            }

            //assign roles accessRoles, downloadRoles
            foreach ($rolesArr as $role) {
                $resRolesThis = $chart->addAccessRole($role);
                if( $resRolesThis ) {
                    echo "Access role $role added ";
                    $count++;
                }
                $resRolesThis = $chart->addDownloadRole($role);
                if( $resRolesThis ) {
                    echo "Download role $role added ";
                    $count++;
                }
            }

            if( $count > 0 ) {
                $processedCharts[] = $chart->getName();
                $totalCount++;
            }

        }//foreach

        if( $totalCount > 0 ) {
            if( !$testing ) {
                $em->flush();
            } else {
                dump($processedCharts);
            }
        }

        //exit("EOF assignAdditionalTopicsAndRolesToCharts: totalCount=$totalCount");
        return $totalCount;
    }

    //Set chart types (Line, Bar ...)
    function assignTypesToCharts( $testing=false ) {

        //exit('assignTypesToCharts disable');

        $em = $this->em;
        $dashboardUtil = $this->container->get('dashboard_util');

        $now = new \DateTime('now');
        $endDate = $now->format('m/d/Y');
        $startDate = $now->modify('-1 year')->format('m/d/Y');

        $charts = $dashboardUtil->getChartTypes();

        $chartsArray = array();
        $chartTypeInvalidArr = array();

        $processedCharts = array();
        $count = 0;

        foreach($charts as $chartType) {

            $chartType = $chartType."";
            $type = "";
            //echo "chartType=".$chartType."<br>";

            $parametersArr = array(
                'startDate' => $startDate,
                'endDate' => $endDate,
                'projectSpecialty' => NULL,
                'showLimited' => NULL,
                'chartType' => $chartType,
                'productservice' => NULL,
                'quantityLimit' => NULL
            );

            $chartsArray = $dashboardUtil->getDashboardChart(NULL,$parametersArr);
            //dump($chartsArray); exit('111');

            if( isset($chartsArray['data']) ) {
                $data = $chartsArray['data'];
                if( isset($data[0]['type']) ) {
                    $type = $data[0]['type'];
                    $type = ucfirst($type);
                    //echo $count.": chartType=".$chartType.", type=$type <br>";
                    //dump($data); exit('111');
                }
            } else {
                echo "Chart invalid: chartType=".$chartType."<br>";
                $chartTypeInvalidArr[] = $chartType;
                continue;
            }

            //find ChartList by $chartType
            $chartEntity = $em->getRepository('AppDashboardBundle:ChartList')->findOneByAbbreviation($chartType);
            if( !$chartEntity ) {
                exit("ChartList not find by abbreviation $chartType");
            }

            //check if chart type already set
            if( count($chartEntity->getChartTypes()) > 0 ) {
                echo $count.": $chartEntity already has a type!!! <br>";
                continue;
            }

            //echo "type=$type <br>";
            //find ChartTypeList by $chartType
            $chartTypeEntity = $em->getRepository('AppDashboardBundle:ChartTypeList')->findOneByName($type);
            if( !$chartTypeEntity ) {
                exit("ChartTypeList not find by name $type");
            }

            $res = $chartEntity->addChartType($chartTypeEntity);
            if( $res ) {
                $processedCharts[] = $chartEntity->getName();
                $count++;
            }

            //testing
            if(1) {
                $thisChartTypeStr = NULL;
                foreach ($chartEntity->getChartTypes() as $thisChartType) {
                    $thisChartTypeStr = $thisChartTypeStr . $thisChartType->getName() . "";
                }
                echo "ID ".$chartEntity->getId()." - ". $chartEntity->getName().
                    " (" . $chartEntity->getAbbreviation() . "): ChartType=" . $thisChartTypeStr . "<br>";
            }


            if( $count > 200 ) {
                break;
            }

        }//foreach

        if( $count > 0 ) {
            if( !$testing ) {
                $em->flush();
            } else {
                dump($processedCharts);
                dump($chartTypeInvalidArr);
            }
        }

        //dump($chartsArray);
        //exit('eof setChartTypesAction:<br>'.$event);
        return $count;

    }

}
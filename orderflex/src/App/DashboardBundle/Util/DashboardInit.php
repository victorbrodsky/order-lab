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
            "ROLE_DASHBOARD_" => array(
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


    public function initCharts() {
        $res = $this->assignInstitutionToCharts();
    }

    public function assignInstitutionToCharts() {
        //4- Set all charts except 57, 58, 59, 62, 63
        //to the “Institution” of:
        //Weill Cornell Medical College > Pathology and Laboratory Medicine > Center for Translational Pathology


    }

}
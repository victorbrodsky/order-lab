<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use App\Migration\PostgresMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625142253 extends PostgresMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE vacreq_business_document (business_id INT NOT NULL, document_id INT NOT NULL, PRIMARY KEY(business_id, document_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4F248723A89DB457 ON vacreq_business_document (business_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4F248723C33F7837 ON vacreq_business_document (document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_business_document ADD CONSTRAINT FK_4F248723A89DB457 FOREIGN KEY (business_id) REFERENCES vacreq_business (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_business_document ADD CONSTRAINT FK_4F248723C33F7837 FOREIGN KEY (document_id) REFERENCES user_document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_coverletter DROP CONSTRAINT fellapp_fellapp_coverletter_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_coverletter ADD PRIMARY KEY (fellapp_id, coverletter_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_cv DROP CONSTRAINT fellapp_fellapp_cv_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_cv ADD PRIMARY KEY (fellapp_id, cv_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_document DROP CONSTRAINT fellapp_fellapp_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_document ADD PRIMARY KEY (fellapp_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_avatar DROP CONSTRAINT fellapp_fellapp_avatar_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_avatar ADD PRIMARY KEY (fellapp_id, avatar_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_examination DROP CONSTRAINT fellapp_fellowshipapplication_examination_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_examination ADD PRIMARY KEY (fellowshipapplication_id, examination_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_citizenship DROP CONSTRAINT fellapp_fellowshipapplication_citizenship_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_citizenship ADD PRIMARY KEY (fellowshipapplication_id, citizenship_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_boardcertification DROP CONSTRAINT fellapp_fellowshipapplication_boardcertification_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_boardcertification ADD PRIMARY KEY (fellowshipapplication_id, boardcertification_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_googleformconfig_fellowshipsubspecialty DROP CONSTRAINT fellapp_googleformconfig_fellowshipsubspecialty_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_googleformconfig_fellowshipsubspecialty ADD PRIMARY KEY (googleformconfig_id, fellowshipsubspecialty_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_reference_document DROP CONSTRAINT fellapp_reference_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_reference_document ADD PRIMARY KEY (reference_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calllog_calllogentrymessage_document DROP CONSTRAINT calllog_calllogentrymessage_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calllog_calllogentrymessage_document ADD PRIMARY KEY (message_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_15b668721aca1422 RENAME TO IDX_5AFC0F4BCD46F646
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_156240fb3d3c30d3 RENAME TO IDX_DADF79673D3C30D3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX encounter_unique00000 RENAME TO encounter_unique
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_encounter DROP CONSTRAINT scan_message_encounter_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_encounter ADD PRIMARY KEY (message_id, encounter_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_accession DROP CONSTRAINT scan_message_accession_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_accession ADD PRIMARY KEY (message_id, accession_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_block DROP CONSTRAINT scan_message_block_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_block ADD PRIMARY KEY (message_id, block_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_imaging DROP CONSTRAINT scan_message_imaging_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_imaging ADD PRIMARY KEY (message_id, imaging_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_6700c13e537a1329 RENAME TO IDX_E5F1439D537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_79d11d14537a1329 RENAME TO IDX_FB209FB7537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_3ec324c3537a1329 RENAME TO IDX_BC32A660537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_editors DROP CONSTRAINT scan_message_editors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_editors ADD PRIMARY KEY (message_id, editorInfo_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_input DROP CONSTRAINT scan_message_input_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_input ADD PRIMARY KEY (message_id, input_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_associations DROP CONSTRAINT scan_message_associations_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_associations ADD PRIMARY KEY (message_id, association_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_destination DROP CONSTRAINT scan_message_destination_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_destination ADD PRIMARY KEY (message_id, destination_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_messagecategory_formnode DROP CONSTRAINT scan_messagecategory_formnode_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_messagecategory_formnode ADD PRIMARY KEY (messageCategory_id, formNode_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_4aae944a88dbad51 RENAME TO IDX_9160929476694CD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_8e61413f88dbad51 RENAME TO IDX_9FD813AB476694CD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_partpaper_document DROP CONSTRAINT scan_partpaper_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_partpaper_document ADD PRIMARY KEY (partpaper_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX part_unique00000 RENAME TO part_unique
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f6616091373182ea RENAME TO IDX_70FDEF46F88CBB76
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_persitesettings_institution DROP CONSTRAINT scan_persitesettings_institution_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_persitesettings_institution ADD PRIMARY KEY (perSiteSettings_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d956bfe410405986 RENAME TO IDX_3D7C119510405986
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d58d7e02bcfb922f RENAME TO IDX_D84E5574B2A22366
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_a24210c9bcfb922f RENAME TO IDX_B57D6BB4B2A22366
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX procedure_unique00000 RENAME TO procedure_unique
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_b7ab567ba66bd30d RENAME TO IDX_F8E1314271E73169
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f64015367909e1ed RENAME TO IDX_39FD2CAA7909E1ED
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_antibody_document DROP CONSTRAINT transres_antibody_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_antibody_document ADD PRIMARY KEY (request_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_invoice_document DROP CONSTRAINT transres_invoice_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_invoice_document ADD PRIMARY KEY (invoice_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_principalinvestigator DROP CONSTRAINT transres_project_principalinvestigator_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_principalinvestigator ADD PRIMARY KEY (project_id, principalinvestigator_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_coinvestigator DROP CONSTRAINT transres_project_coinvestigator_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_coinvestigator ADD PRIMARY KEY (project_id, coinvestigator_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_pathologist DROP CONSTRAINT transres_project_pathologist_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_pathologist ADD PRIMARY KEY (project_id, pathologist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_contact DROP CONSTRAINT transres_project_contact_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_contact ADD PRIMARY KEY (project_id, contact_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_document DROP CONSTRAINT transres_project_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_document ADD PRIMARY KEY (project_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_irbapprovalletter DROP CONSTRAINT transres_project_irbapprovalletter_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_irbapprovalletter ADD PRIMARY KEY (project_id, irbApprovalLetters_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f11d06226072379a RENAME TO IDX_3BD57BCC72698C7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_humantissueform DROP CONSTRAINT transres_project_humantissueform_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_humantissueform ADD PRIMARY KEY (project_id, humanTissueForm_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_117b33b4debe2636 RENAME TO IDX_FE149F5AA27D545F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_22b6422d166d1f9c RENAME TO IDX_C3A8494B166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_c4c1047e166d1f9c RENAME TO IDX_B7C3DE2166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_principalinvestigator DROP CONSTRAINT transres_request_principalinvestigator_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_principalinvestigator ADD PRIMARY KEY (request_id, principalinvestigator_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_document DROP CONSTRAINT transres_request_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_document ADD PRIMARY KEY (request_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_packingslippdf DROP CONSTRAINT transres_request_packingslippdf_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_packingslippdf ADD PRIMARY KEY (request_id, packingSlipPdf_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f2120a63ff061bbc RENAME TO IDX_5E2751FB7F71D5A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_oldpackingslippdf DROP CONSTRAINT transres_request_oldpackingslippdf_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_oldpackingslippdf ADD PRIMARY KEY (request_id, oldPackingSlipPdf_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_2a7da8f1d8164bd RENAME TO IDX_10E9AF556BD350A1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_antibody DROP CONSTRAINT transres_request_antibody_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_antibody ADD PRIMARY KEY (request_id, antibody_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_businesspurpose DROP CONSTRAINT transres_request_businesspurpose_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_businesspurpose ADD PRIMARY KEY (request_id, businessPurpose_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_45e86e982b3ab653 RENAME TO IDX_8A5557046467B583
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX siteparameters_unique00000 RENAME TO siteParameters_unique
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transressiteparameters_transreslogo DROP CONSTRAINT transres_transressiteparameters_transreslogo_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transressiteparameters_transreslogo ADD PRIMARY KEY (transResSiteParameter_id, transresLogo_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_77e35b187408ff6 RENAME TO IDX_F898B8B948FDB66A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transressiteparameters_transrespackingsliplogo DROP CONSTRAINT transres_transressiteparameters_transrespackingsliplogo_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transressiteparameters_transrespackingsliplogo ADD PRIMARY KEY (transResSiteParameter_id, transresPackingSlipLogo_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_e1d041ec7a315316 RENAME TO IDX_7ECB11F76E565D6D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_accountrequest_institution DROP CONSTRAINT user_accountrequest_institution_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_accountrequest_institution ADD PRIMARY KEY (request_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_465c3939834995b1 RENAME TO IDX_BF2A5B6F834995B1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_documentcontainer_document DROP CONSTRAINT user_documentcontainer_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_documentcontainer_document ADD PRIMARY KEY (documentcontainer_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_fb465c67c5b7a34a RENAME TO IDX_F172E05AB974D123
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipsubspecialty_coordinator DROP CONSTRAINT user_fellowshipsubspecialty_coordinator_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipsubspecialty_coordinator ADD PRIMARY KEY (fellowshipSubspecialty_id, coordinator_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_8e5bd5b6e7877946 RENAME TO IDX_708D2BCCE7877946
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipsubspecialty_director DROP CONSTRAINT user_fellowshipsubspecialty_director_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipsubspecialty_director ADD PRIMARY KEY (fellowshipSubspecialty_id, director_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_68324fb8899fb366 RENAME TO IDX_FFFA6A60899FB366
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_e8b806bc8e87796 RENAME TO IDX_166EF8C6802D4908
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_researchlabs DROP CONSTRAINT user_users_researchlabs_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_researchlabs ADD PRIMARY KEY (user_id, researchlab_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_grants DROP CONSTRAINT user_users_grants_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_grants ADD PRIMARY KEY (user_id, grant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_publications DROP CONSTRAINT user_users_publications_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_publications ADD PRIMARY KEY (user_id, publication_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_books DROP CONSTRAINT user_users_books_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_books ADD PRIMARY KEY (user_id, book_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_collaborationinstitution_collaboration DROP CONSTRAINT user_collaborationinstitution_collaboration_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_collaborationinstitution_collaboration ADD PRIMARY KEY (collaborationInstitution_id, collaboration_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_33047118ef1544ce RENAME TO IDX_832A3FC4EF1544CE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_location_assistant DROP CONSTRAINT user_location_assistant_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_location_assistant ADD PRIMARY KEY (location_id, assistant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_logger_institutions DROP CONSTRAINT user_logger_institutions_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_logger_institutions ADD PRIMARY KEY (logger_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_medicaltitle_medicalspeciality DROP CONSTRAINT user_medicaltitle_medicalspeciality_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_medicaltitle_medicalspeciality ADD PRIMARY KEY (medicaltitle_id, medicalspeciality_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_759d120b38d5860e RENAME TO IDX_D1ADC86DC1A3E458
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_3681a952ca5ecd96 RENAME TO IDX_52955BA8DC6F0D8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_permittedinstitutionalphiscope DROP CONSTRAINT user_organizationalgroupdefault_permittedinstitutionalphiscope_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_permittedinstitutionalphiscope ADD PRIMARY KEY (permittedInstitutionalPHIScope_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_205b297510405986 RENAME TO IDX_78626FE310405986
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_language DROP CONSTRAINT user_organizationalgroupdefault_language_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_language ADD PRIMARY KEY (organizationalgroupdefault_id, languagelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_a4cc6e35d88ec86e RENAME TO IDX_243B3090D88EC86E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_locationtype DROP CONSTRAINT user_organizationalgroupdefault_locationtype_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalgroupdefault_locationtype ADD PRIMARY KEY (organizationalgroupdefault_id, locationtypelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_fc2629a3d296b97 RENAME TO IDX_A5107C4D3D296B97
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_permission_institution DROP CONSTRAINT user_permission_institution_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_permission_institution ADD PRIMARY KEY (permission_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d4bb041eb9556f54 RENAME TO IDX_8CD97CB3C5961D3D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX platformlist_unique00000 RENAME TO platformlist_unique
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userpreferences_languages DROP CONSTRAINT user_userpreferences_languages_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userpreferences_languages ADD PRIMARY KEY (userpreferences_id, languagelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_preferences_institutions DROP CONSTRAINT user_preferences_institutions_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_preferences_institutions ADD PRIMARY KEY (preferences_id, institution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles_attributes DROP CONSTRAINT user_roles_attributes_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles_attributes ADD PRIMARY KEY (roles_id, roleattributelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_floors DROP CONSTRAINT user_rooms_floors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_floors ADD PRIMARY KEY (roomlist_id, floorlist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_buildings DROP CONSTRAINT user_rooms_buildings_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_buildings ADD PRIMARY KEY (roomlist_id, buildinglist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sites_lowestroles DROP CONSTRAINT user_sites_lowestroles_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sites_lowestroles ADD PRIMARY KEY (site_id, role_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_64afd0fed60322ac RENAME TO IDX_A56EFFFAD60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_site_document DROP CONSTRAINT user_site_document_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_site_document ADD PRIMARY KEY (site_id, document_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_platformlogo DROP CONSTRAINT user_siteparameter_platformlogo_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_platformlogo ADD PRIMARY KEY (siteParameter_id, platformLogo_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_89f2aef6ea5be894 RENAME TO IDX_29C001C825E6D108
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_emailcriticalerrorexceptionuser DROP CONSTRAINT user_siteparameter_emailcriticalerrorexceptionuser_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_emailcriticalerrorexceptionuser ADD PRIMARY KEY (siteparameter_id, exceptionuser_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_floors DROP CONSTRAINT user_suites_floors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_floors ADD PRIMARY KEY (suitelist_id, floorlist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_buildings DROP CONSTRAINT user_suites_buildings_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_buildings ADD PRIMARY KEY (suitelist_id, buildinglist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_majors DROP CONSTRAINT user_trainings_majors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_majors ADD PRIMARY KEY (training_id, majortraininglist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_minors DROP CONSTRAINT user_trainings_minors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_minors ADD PRIMARY KEY (training_id, minortraininglist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_honors DROP CONSTRAINT user_trainings_honors_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_honors ADD PRIMARY KEY (training_id, honortraininglist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userpositions_positiontypes DROP CONSTRAINT user_userpositions_positiontypes_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userpositions_positiontypes ADD PRIMARY KEY (userposition_id, positiontypelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_a29abe84aae046c8 RENAME TO IDX_61BE9D18AAE046C8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_settings_user DROP CONSTRAINT vacreq_settings_user_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_settings_user ADD PRIMARY KEY (settings_id, emailuser_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_business_document DROP CONSTRAINT FK_4F248723A89DB457
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_business_document DROP CONSTRAINT FK_4F248723C33F7837
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vacreq_business_document
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX procedure_unique RENAME TO procedure_unique00000
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__ce4d3a9f97fedef6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_researchlabs ADD PRIMARY KEY (researchlab_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX encounter_unique RENAME TO encounter_unique00000
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sit__db00a856bd763053
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_platformLogo ADD PRIMARY KEY (platformlogo_id, siteparameter_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_29c001c825e6d108 RENAME TO IDX_89F2AEF6EA5BE894
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__31f366e3bcc33919
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_publications ADD PRIMARY KEY (publication_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_9160929476694cd RENAME TO IDX_4AAE944A88DBAD51
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sit__f5766ba66a7a5b55
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_siteparameter_emailcriticalerrorexceptionuser ADD PRIMARY KEY (exceptionuser_id, siteparameter_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__3327d6c1bcbc2e61
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_grants ADD PRIMARY KEY (grant_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_org__2798b6cee2fa03dc
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalGroupDefault_permittedInstitutionalPHIScope ADD PRIMARY KEY (institution_id, permittedinstitutionalphiscope_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_78626fe310405986 RENAME TO IDX_205B297510405986
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_52955ba8dc6f0d8 RENAME TO IDX_3681A952CA5ECD96
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_org__5fe4fc345e1141a6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalGroupDefault_locationtype ADD PRIMARY KEY (locationtypelist_id, organizationalgroupdefault_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_a5107c4d3d296b97 RENAME TO IDX_FC2629A3D296B97
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_per__0b7d9b53a84a7d17
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_permission_institution ADD PRIMARY KEY (institution_id, permission_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__565ed6eabd7c780d
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_principalinvestigator ADD PRIMARY KEY (principalinvestigator_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_fel__28db425da1707d96
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipSubspecialty_coordinator ADD PRIMARY KEY (coordinator_id, fellowshipsubspecialty_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_708d2bcce7877946 RENAME TO IDX_8E5BD5B6E7877946
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d1adc86dc1a3e458 RENAME TO IDX_759D120B38D5860E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_dadf79673d3c30d3 RENAME TO IDX_156240FB3D3C30D3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_9fd813ab476694cd RENAME TO IDX_8E61413F88DBAD51
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sui__49566268fd665a3c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_buildings ADD PRIMARY KEY (buildinglist_id, suitelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sit__654f4d96ed048b52
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sites_lowestRoles ADD PRIMARY KEY (role_id, site_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_a56efffad60322ac RENAME TO IDX_64AFD0FED60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_b57d6bb4b2a22366 RENAME TO IDX_A24210C9BCFB922F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__29a021d4af61d6c6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transResSiteParameters_transresPackingSlipLogo ADD PRIMARY KEY (transrespackingsliplogo_id, transressiteparameter_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_7ecb11f76e565d6d RENAME TO IDX_E1D041EC7A315316
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___57e065a2d50023cc
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_reference_document ADD PRIMARY KEY (document_id, reference_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__88f7f6574d8245ca
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_transResSiteParameters_transresLogo ADD PRIMARY KEY (transreslogo_id, transressiteparameter_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f898b8b948fdb66a RENAME TO IDX_77E35B187408FF6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_org__9da457f1e95d5a3d
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_organizationalGroupDefault_language ADD PRIMARY KEY (languagelist_id, organizationalgroupdefault_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_243b3090d88ec86e RENAME TO IDX_A4CC6E35D88EC86E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f8e1314271e73169 RENAME TO IDX_B7AB567BA66BD30D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_doc__2253520cf77fb24d
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_documentcontainer_document ADD PRIMARY KEY (document_id, documentcontainer_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_med__689a97da1484ea19
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_medicaltitle_medicalspeciality ADD PRIMARY KEY (medicalspeciality_id, medicaltitle_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_loc__35bcd52c208294ed
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_location_assistant ADD PRIMARY KEY (assistant_id, location_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sit__6b49b54038d4a002
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_site_document ADD PRIMARY KEY (document_id, site_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___1e656710b5f511b5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_coverletter ADD PRIMARY KEY (coverletter_id, fellapp_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d84e5574b2a22366 RENAME TO IDX_D58D7E02BCFB922F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__19312a38df2593d2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_irbApprovalLetter ADD PRIMARY KEY (irbapprovalletters_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_3bd57bcc72698c7a RENAME TO IDX_F11D06226072379A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__63a44e26c1580c92
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_pathologist ADD PRIMARY KEY (pathologist_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__2ceb93c3334224d5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_invoice_document ADD PRIMARY KEY (document_id, invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___2bad743366caf130
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_cv ADD PRIMARY KEY (cv_id, fellapp_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___4ab74df40c4b8cc9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_boardcertification ADD PRIMARY KEY (boardcertification_id, fellowshipapplication_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_per__666b204c33f2b2f0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_perSiteSettings_institution ADD PRIMARY KEY (institution_id, persitesettings_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_3d7c119510405986 RENAME TO IDX_D956BFE410405986
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_70fdef46f88cbb76 RENAME TO IDX_F6616091373182EA
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__03e9258de8f3c1ee
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_humanTissueForm ADD PRIMARY KEY (humantissueform_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_fe149f5aa27d545f RENAME TO IDX_117B33B4DEBE2636
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__calllog___d2d9006cd26843c2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calllog_calllogentrymessage_document ADD PRIMARY KEY (document_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__2b00b9da0588eb04
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_oldPackingSlipPdf ADD PRIMARY KEY (oldpackingslippdf_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_10e9af556bd350a1 RENAME TO IDX_2A7DA8F1D8164BD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_pre__4536d4e84049c021
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_preferences_institutions ADD PRIMARY KEY (institution_id, preferences_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__cc5d79b7365852fa
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_contact ADD PRIMARY KEY (contact_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__b8a8b8ef0400bab2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_businessPurpose ADD PRIMARY KEY (businesspurpose_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_8a5557046467b583 RENAME TO IDX_45E86E982B3AB653
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__c1b5d78589dcf2e9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_document ADD PRIMARY KEY (document_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__651ff0956882d397
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_document ADD PRIMARY KEY (document_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___8efd92876c0d4efa
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_document ADD PRIMARY KEY (document_id, fellapp_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX part_unique RENAME TO part_unique00000
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___a7ce995aacd4854f
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_citizenship ADD PRIMARY KEY (citizenship_id, fellowshipapplication_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX platformlist_unique RENAME TO platformlist_unique00000
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__f2f4f1fa47f2f505
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_principalinvestigator ADD PRIMARY KEY (principalinvestigator_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_par__557c9e9ef2622a88
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_partpaper_document ADD PRIMARY KEY (document_id, partpaper_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_166ef8c6802d4908 RENAME TO IDX_E8B806BC8E87796
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_bf2a5b6f834995b1 RENAME TO IDX_465C3939834995B1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_acc__f69d38a6ebc64f4d
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_accountrequest_institution ADD PRIMARY KEY (institution_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_c3a8494b166d1f9c RENAME TO IDX_22B6422D166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___88a87edecd56af33
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellowshipapplication_examination ADD PRIMARY KEY (examination_id, fellowshipapplication_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__946aa66016ca2554
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userpreferences_languages ADD PRIMARY KEY (languagelist_id, userpreferences_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_fel__07eb659e4a39ae1b
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_fellowshipSubspecialty_director ADD PRIMARY KEY (director_id, fellowshipsubspecialty_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_fffa6a60899fb366 RENAME TO IDX_68324FB8899FB366
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_tra__813e488d7f13a8d3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_honors ADD PRIMARY KEY (honortraininglist_id, training_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_8cd97cb3c5961d3d RENAME TO IDX_D4BB041EB9556F54
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__87e3c6fcd2c2530c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_packingSlipPdf ADD PRIMARY KEY (packingslippdf_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_5e2751fb7f71d5a RENAME TO IDX_F2120A63FF061BBC
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__bd2ee6a18eb4edb3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_users_books ADD PRIMARY KEY (book_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_tra__5c60edaced31c275
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_minors ADD PRIMARY KEY (minortraininglist_id, training_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_tra__ae3b80e435df1f7b
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_trainings_majors ADD PRIMARY KEY (majortraininglist_id, training_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_39fd2caa7909e1ed RENAME TO IDX_F64015367909E1ED
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_f172e05ab974d123 RENAME TO IDX_FB465C67C5B7A34A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__b1b06a8356f708f9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_request_antibody ADD PRIMARY KEY (antibody_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_rol__5f8f6d8eb114b79e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles_attributes ADD PRIMARY KEY (roleattributelist_id, roles_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_roo__c8b3c08035703210
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_buildings ADD PRIMARY KEY (buildinglist_id, roomlist_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___256da0502a31d670
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_googleformconfig_fellowshipsubspecialty ADD PRIMARY KEY (fellowshipsubspecialty_id, googleformconfig_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_log__97ffb833a182e135
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_logger_institutions ADD PRIMARY KEY (institution_id, logger_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__4e177218167dde59
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_messageCategory_formNode ADD PRIMARY KEY (formnode_id, messagecategory_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_use__56c84e47dcdd4347
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_userPositions_positionTypes ADD PRIMARY KEY (positiontypelist_id, userposition_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_61be9d18aae046c8 RENAME TO IDX_A29ABE84AAE046C8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_5afc0f4bcd46f646 RENAME TO IDX_15B668721ACA1422
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_fb209fb7537a1329 RENAME TO IDX_79D11D14537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_e5f1439d537a1329 RENAME TO IDX_6700C13E537A1329
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__0eef7bdf3ae540b7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_destination ADD PRIMARY KEY (destination_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__159b6203a22c0690
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_accession ADD PRIMARY KEY (accession_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__vacreq_s__17bd0de8d9078ca0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vacreq_settings_user ADD PRIMARY KEY (emailuser_id, settings_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__44f76bebfb8170ef
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_imaging ADD PRIMARY KEY (imaging_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_b7c3de2166d1f9c RENAME TO IDX_C4C1047E166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__c1b5d785d13af751
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_antibody_document ADD PRIMARY KEY (document_id, request_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__338f038ae84631cf
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_editors ADD PRIMARY KEY (editorinfo_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_roo__ac9af705947b0756
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rooms_floors ADD PRIMARY KEY (floorlist_id, roomlist_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__transres__0b11e36ce7ca5d70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transres_project_coinvestigator ADD PRIMARY KEY (coinvestigator_id, project_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_sui__2d7f55ed9f48fa8b
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_suites_floors ADD PRIMARY KEY (floorlist_id, suitelist_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__40cebaf52dc1a81e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_associations ADD PRIMARY KEY (association_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_bc32a660537a1329 RENAME TO IDX_3EC324C3537A1329
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__6b87801903e45e2f
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_input ADD PRIMARY KEY (input_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__c1d888a170878e78
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_block ADD PRIMARY KEY (block_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__scan_mes__e7607da6e5168bbc
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE scan_message_encounter ADD PRIMARY KEY (encounter_id, message_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__fellapp___09b4914b61e7e62e
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fellapp_fellapp_avatar ADD PRIMARY KEY (avatar_id, fellapp_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX siteparameters_unique RENAME TO siteparameters_unique00000
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX pk__user_col__c48da81b7b714316
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_collaborationInstitution_collaboration ADD PRIMARY KEY (collaboration_id, collaborationinstitution_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_832a3fc4ef1544ce RENAME TO IDX_33047118EF1544CE
        SQL);
    }
}

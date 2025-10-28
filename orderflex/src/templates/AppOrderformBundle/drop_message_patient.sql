--     Copyright 2017 Cornell University
--
--     Licensed under the Apache License, Version 2.0 (the "License");
--     you may not use this file except in compliance with the License.
--     You may obtain a copy of the License at
--
--     http://www.apache.org/licenses/LICENSE-2.0
--
--     Unless required by applicable law or agreed to in writing, software
--     distributed under the License is distributed on an "AS IS" BASIS,
--     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
--     See the License for the specific language governing permissions and
--     limitations under the License.


-- Drop message and patient hierarchy


-- Educational
DROP TABLE [ScanOrder].[dbo].[scan_educational_userWrapper];
DROP TABLE [ScanOrder].[dbo].[scan_courseTitleTree_userWrapper];
DROP TABLE [ScanOrder].[dbo].[scan_courseTitleTree];
DROP TABLE [ScanOrder].[dbo].[scan_educational];

-- Research
DROP TABLE [ScanOrder].[dbo].[scan_research_userWrapper];
DROP TABLE [ScanOrder].[dbo].[scan_projectTitleTree_userWrapper];
DROP TABLE [ScanOrder].[dbo].[scan_projectTitleTree];
DROP TABLE [ScanOrder].[dbo].[scan_research];

-- Imaging
DROP TABLE [ScanOrder].[dbo].[scan_relevantScans];
DROP TABLE [ScanOrder].[dbo].[scan_imaging];

-- Stain
DROP TABLE [ScanOrder].[dbo].[scan_stainOrder];
DROP TABLE [ScanOrder].[dbo].[scan_stain];

-- Slide
DROP TABLE [ScanOrder].[dbo].[scan_slideReturnRequest];
DROP TABLE [ScanOrder].[dbo].[scan_slideText];
DROP TABLE [ScanOrder].[dbo].[scan_slideOrder];
DROP TABLE [ScanOrder].[dbo].[scan_slide];

-- Block
DROP TABLE [ScanOrder].[dbo].[scan_blockBlockname];
DROP TABLE [ScanOrder].[dbo].[scan_blockSectionsource];
DROP TABLE [ScanOrder].[dbo].[scan_blockSpecialStains];
DROP TABLE [ScanOrder].[dbo].[scan_blockOrder];
DROP TABLE [ScanOrder].[dbo].[scan_block];

-- Part
DROP TABLE [ScanOrder].[dbo].[scan_partPartname];
DROP TABLE [ScanOrder].[dbo].[scan_part];

-- Accession
DROP TABLE [ScanOrder].[dbo].[scan_accessionDate];
DROP TABLE [ScanOrder].[dbo].[scan_accessionaccession];
DROP TABLE [ScanOrder].[dbo].[scan_accession];

-- Procedure
DROP TABLE [ScanOrder].[dbo].[scan_procedureDate];
DROP TABLE [ScanOrder].[dbo].[scan_procedureLocation];
DROP TABLE [ScanOrder].[dbo].[scan_procedurename];
DROP TABLE [ScanOrder].[dbo].[scan_procedureNumber];
DROP TABLE [ScanOrder].[dbo].[scan_procedureOrder];
DROP TABLE [ScanOrder].[dbo].[scan_procedure];

-- Encounter
DROP TABLE [ScanOrder].[dbo].[scan_encounterDate];
DROP TABLE [ScanOrder].[dbo].[scan_encounterInpatientinfo];
DROP TABLE [ScanOrder].[dbo].[scan_encounterList];
DROP TABLE [ScanOrder].[dbo].[scan_encounterLocation];
DROP TABLE [ScanOrder].[dbo].[scan_encounterName];
DROP TABLE [ScanOrder].[dbo].[scan_encounterNumber];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatage];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatfirstname];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPathistory];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatlastname];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatmiddlename];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatsex];
DROP TABLE [ScanOrder].[dbo].[scan_encounterPatsuffix];
DROP TABLE [ScanOrder].[dbo].[scan_encounter];

-- Patient
DROP TABLE [ScanOrder].[dbo].[scan_patientclinicalHistory];
DROP TABLE [ScanOrder].[dbo].[scan_patientDeceased];
DROP TABLE [ScanOrder].[dbo].[scan_patientdob];
DROP TABLE [ScanOrder].[dbo].[scan_patientfirstname];
DROP TABLE [ScanOrder].[dbo].[scan_patientlasttname];
DROP TABLE [ScanOrder].[dbo].[scan_patientmiddlename];
DROP TABLE [ScanOrder].[dbo].[scan_patientmrn];
DROP TABLE [ScanOrder].[dbo].[scan_patientRace];
DROP TABLE [ScanOrder].[dbo].[scan_patientsex];
DROP TABLE [ScanOrder].[dbo].[scan_patientSuffix];
DROP TABLE [ScanOrder].[dbo].[scan_patientType];
DROP TABLE [ScanOrder].[dbo].[scan_patientType_system];
DROP TABLE [ScanOrder].[dbo].[scan_patient];

-- Message
DROP TABLE [ScanOrder].[dbo].[scan_message_accession];
DROP TABLE [ScanOrder].[dbo].[scan_message_associations];
DROP TABLE [ScanOrder].[dbo].[scan_message_block];
DROP TABLE [ScanOrder].[dbo].[scan_message_destination];
DROP TABLE [ScanOrder].[dbo].[scan_endpoint];
DROP TABLE [ScanOrder].[dbo].[scan_message_encounte];
DROP TABLE [ScanOrder].[dbo].[scan_message_imaging];
DROP TABLE [ScanOrder].[dbo].[scan_message_input];
DROP TABLE [ScanOrder].[dbo].[user_generalEntity];
DROP TABLE [ScanOrder].[dbo].[scan_message_orderRecipient];
DROP TABLE [ScanOrder].[dbo].[scan_message_organizationRecipient];
DROP TABLE [ScanOrder].[dbo].[scan_message_output];
DROP TABLE [ScanOrder].[dbo].[scan_message_part];
DROP TABLE [ScanOrder].[dbo].[scan_message_patient];
DROP TABLE [ScanOrder].[dbo].[scan_message_procedure];
DROP TABLE [ScanOrder].[dbo].[scan_message_reportRecipient];
DROP TABLE [ScanOrder].[dbo].[scan_message_slide];
DROP TABLE [ScanOrder].[dbo].[scan_message_source];
DROP TABLE [ScanOrder].[dbo].[scan_message_user];
DROP TABLE [ScanOrder].[dbo].[scan_message];

-- ObjectAbstract
DROP TABLE [ScanOrder].[dbo].[user_tracker];
DROP TABLE [ScanOrder].[dbo].[user_spot];



-- TRUNCATE TABLE

TRUNCATE TABLE [ScanOrder].[dbo].[scan_slideReturnRequest];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_slideText];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_slide];

TRUNCATE TABLE [ScanOrder].[dbo].[scan_blockBlockname];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_blockSectionsource];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_blockSpecialStains];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_block];

TRUNCATE TABLE [ScanOrder].[dbo].[scan_partPartname];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_part];

TRUNCATE TABLE [ScanOrder].[dbo].[scan_accessionDate];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_accessionaccession];
TRUNCATE TABLE [ScanOrder].[dbo].[scan_accession];
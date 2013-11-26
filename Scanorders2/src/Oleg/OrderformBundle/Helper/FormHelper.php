<?php

namespace Oleg\OrderformBundle\Helper;

class FormHelper {
    
    public function getStains() {
        $arr1 = array(
            'H&E'=>'H&E','2-Oct'=>'2-Oct','4-Oct'=>'4-Oct','A103 (Melan-A)'=>'A103 (Melan-A)'
        );
        $arr = array(
            "H&E","A103 (Melan-A)","
a-1-ACT	","
a-1-IAT	","
a-B-Crystallin	","
ACTH	","
Adenovirus	","
AFB	","
AFP (mono)	","
Alcian Blue	","
Alcian Blue/PAS	","
Alcian Yellow	","
ALK-1	","
Alport MAB1, 3, 5	","
Amylin	","
Amyloid A	","
Anti-p63	","
AR	","
Arginase	","
a-Synuclein	","
B-2-Microglobulin	","
B72.3	","
B-Amyloid	","
B-Catenin	","
BCL-1	","
BCL-2	","
BCL-6	","
B-Dystroglycan	","
BerEP4	","
BF-1	","
BK Virus	","
BLIMP-1	","
BOB-1	","
BRAF	","
Brst-1	","
Brst-2	","
C1q	","
C3	","
C3d	","
C4d	","
C4d (m) & (poly)	","
C5b-9	","
CA 19.9	","
CA125	","
CAIX	","
Calcitonin	","
Caldesmon	","
Calponin	","
Calretinin	","
CAM 5.2	","
CD 74 LL1	","
CD10	","
CD117	","
CD11c	","
CD123	","
CD138	","
CD14	","
CD15	","
CD16	","
CD163	","
CD183	","
CD1a	","
CD2	","
CD20	","
CD21	","
CD22	","
CD23	","
CD246	","
CD25	","
CD27	","
CD3	","
CD30	","
CD31	","
CD34	","
CD35	","
CD38	","
CD40	","
CD42b	","
CD43	","
CD4-368	","
CD45	","
CD45 RA	","
CD45 RO	","
CD5	","
CD56	","
CD56 (504)	","
CD56 (SE)	","
CD57	","
CD61	","
CD62L	","
CD68 PGM1	","
CD7	","
CD7 Dem	","
CD74 LN2	","
CD79a	","
CD8	","
CD83	","
CD99	","
CDK6	","
CDX-2	","
CEA (m)&(poly)	","
c-erbB-2	","
CHR	","
Chromogranin	","
CK 5/6	","
CK AE1/AE3	","
CK MNF116	","
CK14	","
CK15	","
CK17	","
CK19	","
CK20	","
CK5	","
CK7	","
CKIT	","
CLUSTERIN	","
CMV	","
c-Myc	","
Collagen IV	","
Colloidal Iron	","
Congo Red	","
Cromogranin A	","
Crystal Violet	","
CXCR3	","
CYCLIN D1	","
CYCLIN D2	","
CYCLIN D3	","
Cyclin E	","
D2-40	","
DBA.44	","
Desmin	","
Diff Quik	","
EBER	","
EBER In Situ	","
ECAD	","
E-Cadherin	","
EGFR	","
EGFR FISH	","
EGFR Mutation Analysis	","
Elastic	","
EMA	","
Endothelial Cell Assay	","
Epstein-Barr Virus	","
ER	","
ERA	","
ERG	","
F13a	","
F8	","
Fascin	","
Fibrinogen	","
Fite Faraco	","
Fontana Masson	","
Fox P1	","
Fox P3	","
FSH	","
FSL	","
Galectin-3	","
Gastrin	","
GCDFP- 15	","
GCET	","
GFAP	","
Giemsa	","
Glucagon	","
Glutamine	","
GLYCO A	","
GLYCO C	","
GMS	","
GMS-H&E	","
Gomoris Methenamine Silver	","
GPC3	","
Gram	","
Granzyme B	","
H. Pylori	","
HAM 5.6	","
HBME-1	","
HCG	","
HECA 452	","
Helicobacter Pylori	","
Hemoglobin A	","
Hepatocyte	","
HepBcAg	","
HepBsAg	","
HER2	","
Her-2 FISH (Breast)	","
Her-2 FISH GI	","
Her-2 Neu	","
hGH	","
HIV p24	","
HLA-DR	","
HMB45	","
HPAP	","
HPL	","
HPV	","
HPV 16/18	","
HPV 31/33/51	","
HPV 6/11	","
HPV In Situ	","
HSV I	","
HSV II	","
Human Herpesvirus 8	","
Human Mesothelial Cell	","
Human Prealbumin	","
IDH1	","
IgA	","
IgD	","
IgG	","
IgG4	","
IgM	","
Inhibin	","
INI	","
Insulin	","
Iron	","
J-Chain	","
K1-67	","
K903	","
Kappa	","
Kappa In Situ	","
Ki-67	","
KP1	","
K-RAS Mutation Analysis	","
Lambda	","
Laminin	","
LANA	","
Langerin	","
LH	","
LMP-1	","
LNGFR (p75)	","
Luxol Fast Blue	","
LYSO	","
M75	","
MAC 387	","
Mammaglobin	","
MCK	","
MCT	","
MITF	","
MLH-1	","
MNF116	","
MPO	","
MSA	","
MSH-2	","
MSH-6	","
Mucicarmine	","
MUM-1	","
MxA	","
Myeloperoxidase	","
MYO D1	","
MYOGENIN	","
MYOGLOBIN	","
MYOSIN	","
Napsin A	","
NC	","
NC HER-2	","
Neg RTU	","
Negative	","
Negative Control	","
Neurofilament	","
NG Smear	","
Non-Specific Esterase	","
NSE	","
2-Oct	","
4-Oct	","
Oil Red O	","
p-120	","
p16	","
P18	","
P21	","
P27	","
P501s	","
P504S	","
p53	","
p57	","
p63	","
PAMS	","
Panc. Polypep.	","
Panker-D	","
Pap	","
Parvovirus	","
PAS Reaction with Diastase	","
PAX-5	","
PAX-8	","
P-Component	","
PCP	","
PCR BCL2-IGH	","
PCR EBV	","
PCR HTLV-1	","
PCR IGH FR3	","
PCR IGK	","
PCR JAK2 V617F	","
PCR KSHV	","
PCR p190 BCR-ABL	","
PCR p210 BCR-ABL	","
PCR TCR Gamma	","
PD-1	","
PE-10	","
Pemphgoid/Pemphigus	","
Periodic Acid Schiff	","
Periodic Methenamine Silver	","
PGM-1	","
PGR	","
Phospho-Rb	","
Phosphotungstic Acid Hematoxylin	","
PIN-4	","
Placental Alkaline Phosphatase	","
PLAP	","
PMS2	","
POLY	","
PR	","
PRA	","
Prolactin	","
PSA	","
PsAP	","
PSMA	","
pSRb	","
PTEN	","
PTH	","
PU.1	","
R21	","
Rb	","
Reticulin	","
Rhodanine	","
S100	","
Salt Split Skin Assay	","
Serotonin	","
SKP-2	","
SMA	","
Somatostatin	","
Steiner	","
Surfactant Protein A	","
SV40	","
Synaptophysin	","
TAU-2	","
TCL-1	","
TCL-30	","
TCR	","
TdT	","
Thyroglobulin	","
TIA-1	","
Touch Prep	","
Toxoplasma	","
TRAcP	","
Trichrome	","
Triple Stain	","
TSH	","
TTF-1	","
TTR	","
Ubiquitin	","
Varicella	","
Varicella Zoster	","
VEGF	","
vIL-6	","
Vimentin	","
Von Kossa	","
Warthin Starry	","
WT-1	","
ZAP-70	"          
        );
        
        return $arr;
    }
    
public function getSourceOrgan() {
        $arr = array( "Abdomen	","Adenoid	",
            "Adrenal gland	","
Amniotic Fluid	","
Ankle	","
Anus	","
Anus - anal verge	","
Aorta	","
Appendix - vermiform	","
Arm	","
Artery	","
Ascites	","
Axilla	","
Back	","
Bile	","
Bladder	","
Blood	","
Bone	","
Bone Marrow	","
Brain	","
Breast	","
Breast - nipple	","
Buttock	","
Calf	","
Cerebrospinal Fluid	","
Cervix	","
Chest	","
Colon	","
Colon - ascending	","
Colon - cecum	","
Colon - descending	","
Colon - hepatic flexure	","
Colon - left	","
Colon - rectosigmoid	","
Colon - rectum	","
Colon - right	","
Colon - sigmoid	","
Colon - splenic flexure	","
Colon - transverse	","
Common Bile Duct	","
Diaphragm	","
Duodenum	","
Duodenum - ampulla	","
Duodenum - bulb	","
Dura	","
Ear	","
Elbow	","
Esophagus	","
Eye	","
Eye - cornea	","
Eye - iris	","
Eye - lens	","
Eye - vitreous	","
Face	","
Face - cheek	","
Face - chin	","
Face - eyelid	","
Face - forehead	","
Face - jaw	","
Face - lip	","
Face - nose	","
Face - nose - nasal cavity	","
Face - nose - nasal septum	","
Face - temple	","
Fallopian tube	","
Femur	","
Fetus	","
Fibia	","
Fibula	","
Finger	","
Foot	","
Forearm	","
Foreign Body	","
Foreskin	","
Gallbladder	","
Gastroesophageal junction	","
Gonad	","
Groin	","
Hair	","
Hand	","
Heart	","
Heart - aortic valve	","
Heart - atrium - left	","
Heart - atrium - right	","
Heart - mitral valve	","
Heart - pulmonic valve	","
Heart - tricuspid valve	","
Heart - valve	","
Heart - ventricle - left	","
Heart - ventricle - right	","
Heel	","
Hemorrhoid	","
Hip	","
Humerus	","
Ileocecal valve	","
Ileum	","
Intervertebral disc	","
Intestine	","
Jejunum	","
Jugular vein	","
Kidney	","
Kidney - renal pelvis	","
Knee	","
Lacrimal gland	","
Lamina	","
Larynx	","
Leg	","
Ligament	","
Liver	","
Lung	","
Lung - bronchus	","
Lung - hilum	","
Lung - left	","
Lung - left - lower lobe	","
Lung - left - upper lobe	","
Lung - right	","
Lung - right - middle lobe	","
Lung - right - upper lobe	","
Lung - right lower lobe	","
Lymph Node	","
Mandible	","
Mass	","
Mastoid	","
Maxilla	","
Mouth	","
Muscle	","
Nail Clipping	","
Nasopharynx	","
Neck	","
Nerve	","
Omentum	","
Oral cavity	","
Oropharynx	","
Other	","
Ovary	","
Palate	","
Pancreas	","
Parathyroid gland	","
Parotid gland	","
Pelvis	","
Penis	","
Pericardial Fluid	","
Pericardium	","
Perineum	","
Peritoneal Fluid	","
Peritoneum	","
Pharynx	","
Pituitary gland	","
Placenta	","
Pleura	","
Pleural Fluid	","
Product Of Conception	","
Prostate	","
Rectum	","
Rib	","
Sacrum	","
Scalp	","
Scrotum	","
Seminal vesicle	","
Shoulder	","
Sinus	","
Sinus - Ethmoid	","
Sinus - Frontal	","
Sinus - Maxillary	","
Sinus - Sphenoid	","
Skin	","
Skull	","
Small intestine	","
Small intestine - duodenum	","
Small intestine - ileum	","
Small intestine - jejunum	","
Spermatic Cord	","
Spermatic Vein	","
Spine	","
Spleen	","
Sputum	","
Stomach	","
Stomach - antrum	","
Stomach - body	","
Stomach - cardia	","
Stomach - fundus	","
Stomach - pylorus	","
Stool	","
Sublingual gland	","
Submandibular gland	","
Synovial Fluid	","
Synovium	","
Temporal Artery	","
Test	","
Testis	","
Thigh	","
Thumb	","
Thymus	","
Thyroid	","
Toe	","
Tongue	","
Tonsil	","
Tooth	","
Trachea	","
Turbinate	","
Umbilical cord	","
Umbilicus	","
Ureter	","
Urethra	","
Urine	","
Uterus	","
Uterus - endometrium	","
Uvula	","
Vagina	","
Vas deferens	","
Vein	","
Vocal cord	","
Vocal fold	","
Vulva	","
Wrist	");

        return $arr;
            
    }
    
    public function getPathologyService() {              
        $arr = array(
            "Accessioning","
Autopsy","
Breast Pathology","
Central Laboratory and Point of Care Services","
Cytogenetics","
Cytopathology","
Dermatopathology","
Gastrointestinal Pathology","
Gastrointestinal Pathology / Pediatric Pathology / Autopsy","
Genitourinary Pathology","
Gross Room","
Gynecologic Pathology","
Gynecologic Pathology / Perinatal Pathology / Autopsy","
Head & Neck Pathology","
Hematopathology","
Histology","
Immunopathology","
Informatics","
Microbiology","
Molecular Hematopathology","
Molecular Pathology","
Neuropathology","
Renal Pathology","
Transfusion Medicine and Cellular Therapy","
Translational Research"       
                );
        
        return $arr;
    }


    public function getUserPathology($email) {

        $arr = array(
            'rbaergen@med.cornell.edu' => 'Gynecologic Pathology / Perinatal Pathology / Autopsy',
            'dmb2007@med.cornell.edu' => 'Gastrointestinal Pathology / Pediatric Pathology / Autopsy',
            'vib9020@med.cornell.edu' => 'Informatics',
            'ytchen@med.cornell.edu' => 'Gastrointestinal Pathology',
            'tid9007@med.cornell.edu' => 'Breast Pathology',
            'gad9013@med.cornell.edu' => 'Dermatopathology',
            'lhellens@med.cornell.edu' => 'Gynecologic Pathology',
            'sahoda@med.cornell.edu' => 'Breast Pathology',
            'joj9034@med.cornell.edu' => 'Gastrointestinal Pathology',
            'jhkoizum@med.cornell.edu' => 'Cytopathology',
            'ehl2005@med.cornell.edu' => 'Neuropathology',
            'cym2003@med.cornell.edu' => 'Dermatopathology',
            'jmm9018@med.cornell.edu' => 'Genitourinary Pathology',
            'nan9030@med.cornell.edu' => 'Genitourinary Pathology',
            'nip9020@med.cornell.edu' => 'Gastrointestinal Pathology',
            'ecpirog@med.cornell.edu' => 'Gynecologic Pathology',
            'liq9005@med.cornell.edu' => 'Gastrointestinal Pathology',
            'ner9004@med.cornell.edu' => 'Autopsy',
            'brr2006@med.cornell.edu' => 'Genitourinary Pathology',
            'rubinma@med.cornell.edu' => 'Genitourinary Pathology',
            'sts9057@med.cornell.edu' => 'Renal Pathology',
            'ams2033@med.cornell.edu' => 'Cytopathology',
            'ths9004@med.cornell.edu' => 'Head & Neck Pathology',
            'svs2002@med.cornell.edu' => 'Renal Pathology',
            'rhoda@med.cornell.edu' => 'Cytopathology',
            'mas2090@med.cornell.edu' => 'Genitourinary Pathology',
            'sjshin@med.cornell.edu' => 'Breast Pathology',
            'gry2001@med.cornell.edu' => 'Cytopathology',
            'rhy2001@med.cornell.edu' => 'Gastrointestinal Pathology',
            'sua9036@nyp.org' => 'Cytopathology',
            'lia9040@nyp.org' => 'Histology',
            'smb9005@med.cornell.edu' => 'Renal Pathology',
            'lcervino@nyp.org' => 'Gross Room',
            'mtg2003@med.cornell.edu' => 'Renal Pathology',
            'neg2003@med.cornell.edu' => 'Autopsy',
            'rog2015@med.cornell.edu' => 'Dermatopathology',
            'cah2028@med.cornell.edu' => 'Dermatopathology',
            'reh9002@nyp.org' => 'Informatics',
            'hhp2001@nyp.org' => 'Accessioning',
            'mor2007@med.cornell.edu' => 'Breast Pathology',
            'jos9115@nyp.org' => 'Informatics',
            'aat2001@med.cornell.edu' => 'Autopsy',
            'yaa2004@med.cornell.edu' => 'Central Laboratory and Point of Care Services',
            'sca2002@med.cornell.edu' => 'Transfusion Medicine and Cellular Therapy',
            'mec2013@med.cornell.edu' => 'Transfusion Medicine and Cellular Therapy',
            'chg9006@med.cornell.edu' => 'Transfusion Medicine and Cellular Therapy',
            'stj2005@med.cornell.edu' => 'Microbiology',
            'dgl2001@med.cornell.edu' => 'Molecular Pathology',
            'sum2001@med.cornell.edu' => 'Cytogenetics',
            'vep2004@med.cornell.edu' => 'Cytogenetics',
            'har2006@med.cornell.edu' => 'Molecular Pathology',
            'mir9079@med.cornell.edu' => 'Central Laboratory and Point of Care Services',
            'ans9112@med.cornell.edu' => 'Microbiology',
            'shs9100@med.cornell.edu' => 'Cytogenetics',
            'ecesarm@med.cornell.edu' => 'Molecular Hematopathology',
            'sae2001@med.cornell.edu' => 'Immunopathology',
            'jut9021@med.cornell.edu' => 'Immunopathology',
            'dknowles@med.cornell.edu' => 'Immunopathology',
            'ato9002@med.cornell.edu' => 'Immunopathology',
            'sop9018@med.cornell.edu' => 'Immunopathology',
            'ris9004@med.cornell.edu' => 'Immunopathology',
            'wtam@med.cornell.edu' => 'Immunopathology',
            'lyw2001@med.cornell.edu' => 'Molecular Hematopathology',
            'anb9154@nyp.org' => 'Gastrointestinal Pathology',
            'sed9044@nyp.org' => 'Dermatopathology',
            'jeg9101@nyp.org' => 'Transfusion Medicine and Cellular Therapy',
            'jjh7002@nyp.org' => 'Cytopathology',
            'kak9058@nyp.org' => 'Gastrointestinal Pathology',
            'rco9001@nyp.org' => 'Hematopathology',
            'sov9008@nyp.org' => 'Breast Pathology',
            'xuw9004@nyp.org' => 'Dermatopathology',
            'jiy9006@nyp.org' => 'Hematopathology',
            'rok2011@med.cornell.edu' => 'Translational Research',
            'shb2016@med.cornell.edu' => 'Translational Research',
            //'oli2002@med.cornell.edu' => 'Gynecologic Pathology / Perinatal Pathology / Autopsy'
        );

        $service = null;

        if (array_key_exists($email, $arr)) {
            $service = $arr[$email];
        }

        return $service;
    }
    
    
    public function getMags() {        
        $arr = array( '20X'=>'20X', '40X'=>'40X' );
        //$arr = array( '20X', '40X' );
        
        return $arr;
    }
    
    public function getPriority() {        
        $arr1 = array( 'Routine'=>'Routine', 'Stat'=>'Stat' );
        $arr = array( 'Routine', 'Stat' );
        
        return $arr;
    }
    
    public function getSlideDelivery() {        
        $arr1 = array( 
            'I will drop ...'=>'I will drop ...', "I'll give slides to .."=>"I'll give slides to .." );
       
        $arr = array(
            "I'll give slides to Noah - ST1015E (212) 746-2993",
            "I have given slides to Noah already",
            "I will drop the slides off at F540 (212) 746-6406",
            "I have handed the slides to Liza already",
            "I will write S on the slide & submit as a consult",
            "I will write S4 on the slide & submit as a consult",
            "I will email slidescan@med.cornell.edu about it",
            "Please e-mail me to set the time & pick up slides",
        );
        
        return $arr;
    }
    
    public function getReturnSlide() {        
        $arr = array( 'Filing Room', "Me" );
        
        return $arr;
    }

    public function getProcedure() {
        $arr = array( 'Biopsy', 'Excision', 'Fine Needle Aspiration' );

        return $arr;
    }
    
    public function getScanRegion() {          
        $arr = array(
            "Entire Slide",
            "Any one of the levels",
            "Region circled by marker"
        );
    
        return $arr;
    }
                
    public function getBlock() {        
        $arr = array();
        
        for( $i=1; $i<=400; $i++ ) {
            //array_push($arr, $i);
            $arr[$i] = $i;
        }
        
        return $arr;
    }

    // Generate 100 Parts: A to CW
    public function getPart() {        
        $arr = array();
        $letters = range('A', 'Z');
        $count = 0;
        //echo "START<br>";
        for( $i=0; $i<=100; $i++ ) {
            //echo "i=".$i.", index=". $i%26 .", count=".$count.", arr=".$letters[$i%26]."<br>";
            if( $count == 0 ) {
//                array_push( $arr, $letters[$i%26] );
                $arr[$letters[$i%26]]  = $letters[$i%26];
                //echo $letters[$i%26]."<br>";
            } else {
//              array_push( $arr, $arr[$count-1].$letters[$i%26] );
                $letter = $letters[$count-1].$letters[$i%26];
                $arr[$letter]  = $letter;
                //echo $letter."<br>";
            }       
            if(  ($i % 26) == 25 ) $count++; //1,2,3,4...26
        }          
        
        return $arr;
    }


    public function getSlideType() {
        $arr = array(
            'Permanent Section',
            'Cytopathology',
            'Frozen Section',
            'Smear',
            'Touch Prep',
            'Squash Prep',
            'Scrape Prep',
            'Drag Prep',
            'Cell Block',
            'TMA'
        );

        return $arr;
    }
    
}
?>

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

namespace App\CtpBundle\Controller;





use App\CtpBundle\Entity\PageContentList;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
use App\OrderformBundle\Entity\Message;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\UserdirectoryBundle\Entity\ObjectTypeText;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends OrderAbstractController
{
    #[Route(path: '/about', name: 'ctp_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('ctp.sitename'));
    }

    #[Route(path: '/', name: 'ctp_home', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/home.html.twig')]
    public function indexAction( Request $request ) {

//        if( false == $this->isGranted('ROLE_CTP_USER') ){
//            return $this->redirect( $this->generateUrl('ctp-nopermission') );
//        }

        $title = 'Center for Translational Pathology';
        $pageName = 'ctp_home';
        $csrfTokenId = 'ctp_home_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit home page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP home page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);   
            $em->flush();

            return $this->redirectToRoute('ctp_home');
        }

        return array(
            'title' => $title,
            'homePageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    private function getPageContentEntity($pageName, $createIfMissing=false)
    {
        $em = $this->getDoctrine()->getManager();

        $pageContentEntity = $em->getRepository(PageContentList::class)->findOneBy(['name' => $pageName]);

        if( !$pageContentEntity && $createIfMissing ) {
            $pageContentEntity = new PageContentList($this->getUser());
            $pageContentEntity->setName($pageName);
            $pageContentEntity->setType('default');

            if( $this->getUser() instanceof User ) {
                $pageContentEntity->setCreator($this->getUser());
            }
        }

        return $pageContentEntity;
    }

    #[Route(path: '/publications', name: 'ctp_publications', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/publications.html.twig')]
    public function publicationsAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/new-project-inquiry', name: 'ctp_new-project-inquiry', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/new-project-inquiry.html.twig')]
    public function newProjectInquiryAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        $activeInquiryType = 'wcm';
        $validationErrors = array();

        if( $request->isMethod('POST') ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid('ctp_new_project_inquiry_submit', $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP new project inquiry submission');
            }

            $em = $this->getDoctrine()->getManager();
            $inquiryType = $this->getTrimmedRequestValue($request, 'inquiryType') ?? 'wcm';
            if( $inquiryType !== 'external' ) {
                $inquiryType = 'wcm';
            }
            $activeInquiryType = $inquiryType;

            $validationErrors = $this->getRequiredInquiryFieldErrors($request, $inquiryType);
            if( count($validationErrors) > 0 ) {
                return array(
                    'title' => $title,
                    'activeInquiryType' => $activeInquiryType,
                    'validationErrors' => $validationErrors,
                );
            }

            $project = new Project($this->getUser() instanceof User ? $this->getUser() : null);
            $project->setVersion(1);

            //Set state.
            $project->setState('draft');
            //$project->setState('irb_review');

            $projectSpecialtyName = "Investigator's Initial Project Inquiry";
            $projectSpecialty = $em->getRepository(SpecialtyList::class)->findOneBy(['name' => $projectSpecialtyName]);
            if( !$projectSpecialty ) {
                $projectSpecialty = $em->getRepository(SpecialtyList::class)->findOneBy(['friendlyname' => $projectSpecialtyName]);
            }
            if( !$projectSpecialty ) {
                $projectSpecialty = $em->getRepository(SpecialtyList::class)->findOneBy(['abbreviation' => 'init']);
            }
            if( !$projectSpecialty ) {
                $projectSpecialty = $em->getRepository(SpecialtyList::class)->findOneBy(['shortname' => 'INIT']);
            }
            if( $projectSpecialty ) {
                $project->setProjectSpecialty($projectSpecialty);
            }

            $inquiryDate = $this->getTrimmedRequestValue($request, 'inquiryDate');
            if( $inquiryDate ) {
                try {
                    $project->setCreateDate(new \DateTime($inquiryDate));
                } catch( \Exception $e ) {
                }
            }

            $projectTitle = $this->getTrimmedRequestValue($request, 'projectTitle');
            if( $projectTitle ) {
                $project->setTitle($projectTitle);
            } elseif( $inquiryType === 'external' ) {
                $project->setTitle('External Collaboration Project Inquiry');
            }

            $background = $this->getTrimmedRequestValue($request, 'background');
            if( $background ) {
                $project->setEssentialInfo($background);
                $project->setDescription($background);
            }

            $experimentalPlanSummary = $this->getTrimmedRequestValue($request, 'experimentalPlanSummary');
            if( $experimentalPlanSummary ) {
                $project->setStrategy($experimentalPlanSummary);
                $project->setObjective($experimentalPlanSummary);
            }

            $fundingSource = $this->getTrimmedRequestValue($request, 'fundingSource');
            if( $fundingSource ) {
                $project->setFundDescription($fundingSource);
            }

            $department = $this->getTrimmedRequestValue($request, 'department');
            if( $department ) {
                $project->setCollDepartment($department);

                $institution = $em->getRepository(Institution::class)->findOneBy(['name' => $department]);
                if( $institution ) {
                    $project->setInstitution($institution);
                }
            }

            $inquirySummary = array();
            $inquirySummary[] = 'Inquiry Type: '.($inquiryType === 'external' ? 'External Collaboration Project Inquiry' : 'WCM Investigator Project Inquiry');

            $inquirySummary[] = "Department: ".$department;

            if( $inquiryType === 'external' ) {
                $externalContactName = $this->getTrimmedRequestValue($request, 'externalContactName');
                $externalContactEmail = $this->getTrimmedRequestValue($request, 'externalContactEmail');
                $externalInstitution = $this->getTrimmedRequestValue($request, 'externalInstitution');
                if( !$externalInstitution ) {
                    $externalInstitution = $this->getTrimmedRequestValue($request, 'institution');
                }
                $externalPhone = $this->getTrimmedRequestValue($request, 'externalPhone');

                if( $externalContactName ) {
                    $inquirySummary[] = 'External Collaborator Contact Name: '.$externalContactName;
                }
                if( $externalContactEmail ) {
                    $inquirySummary[] = 'External Contact Email: '.$externalContactEmail;

                    $contactUser = $em->getRepository(User::class)->findOneUserByEmail($externalContactEmail);
                    if( $contactUser ) {
                        $project->addContact($contactUser);
                        if( !$project->getSubmitter() ) {
                            $project->setSubmitter($contactUser);
                        }
                    }
                }
                if( $externalInstitution ) {
                    $project->setCollInst($externalInstitution);

                    $institution = $em->getRepository(Institution::class)->findOneBy(['name' => $externalInstitution]);
                    if( $institution ) {
                        $project->setInstitution($institution);
                    }

                    $inquirySummary[] = 'External Institution: '.$externalInstitution;
                }
                if( $externalPhone ) {
                    $inquirySummary[] = 'External Contact Phone Number: '.$externalPhone;
                }
            } else {
                $principalInvestigator = $this->getTrimmedRequestValue($request, 'principalInvestigator');
                $contactName = $this->getTrimmedRequestValue($request, 'contactName');
                $contactEmail = $this->getTrimmedRequestValue($request, 'contactEmail');
                $phone = $this->getTrimmedRequestValue($request, 'phone');

                if( $principalInvestigator ) {
                    $inquirySummary[] = 'Principal Investigator (entered): '.$principalInvestigator;

                    $principalInvestigatorUser = $em->getRepository(User::class)->findOneByAnyNameStr($principalInvestigator);
                    if( $principalInvestigatorUser ) {
                        $project->addPrincipalInvestigator($principalInvestigatorUser);
                    }
                }
                if( $contactName ) {
                    $inquirySummary[] = 'Contact Name: '.$contactName;
                }
                if( $contactEmail ) {
                    $inquirySummary[] = 'Contact Email Address: '.$contactEmail;

                    $contactUser = $em->getRepository(User::class)->findOneUserByEmail($contactEmail);
                    if( $contactUser ) {
                        $project->addContact($contactUser);
                        if( !$project->getSubmitter() ) {
                            $project->setSubmitter($contactUser);
                        }
                    }
                }
                if( $phone ) {
                    $inquirySummary[] = 'Phone: '.$phone;
                }
            }

            if( count($inquirySummary) > 0 ) {
                $project->setOtherResource(implode("\n", $inquirySummary));
            }

            $em->persist($project);
            $em->flush();

            $project->generateOid();
            $em->flush();

            $this->addFlash(
                'notice',
                'New project inquiry has been submitted with ID '.$project->getOid()
            );

            //return $this->redirectToRoute('ctp_new-project-inquiry');
            return $this->redirectToRoute('ctp_home');
        }

        return array(
            'title' => $title,
            'activeInquiryType' => $activeInquiryType,
            'validationErrors' => $validationErrors,
        );
    }

    private function getRequiredInquiryFieldErrors(Request $request, string $inquiryType): array
    {
        $requiredFields = array(
            'inquiryDate' => 'Date',
            'background' => 'Background',
            'experimentalPlanSummary' => 'Experimental Plan Summary',
        );

        if( $inquiryType === 'external' ) {
            $requiredFields = array_merge($requiredFields, array(
                'externalContactName' => 'External Collaborator Contact Name',
                'externalContactEmail' => 'External Contact Email',
                'externalInstitution' => 'Institution',
            ));
        } else {
            $requiredFields = array_merge($requiredFields, array(
                'projectTitle' => 'Project Title',
                'principalInvestigator' => 'Principal Investigator',
                'department' => 'Department',
                'contactName' => 'Contact Name',
                'contactEmail' => 'Contact Email Address',
            ));
        }

        $errors = array();
        foreach( $requiredFields as $fieldName => $label ) {
            $fieldValue = $this->getTrimmedRequestValue($request, $fieldName);
            if( $fieldName === 'externalInstitution' && !$fieldValue ) {
                $fieldValue = $this->getTrimmedRequestValue($request, 'institution');
            }

            if( !$fieldValue ) {
                $errors[] = $label;
            }
        }

        return $errors;
    }

    private function getTrimmedRequestValue(Request $request, string $fieldName): ?string
    {
        $value = trim((string)$request->request->get($fieldName));
        return $value !== '' ? $value : null;
    }

    #[Route(path: '/people', name: 'ctp_people', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/people.html.twig')]
    public function peopleAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_people';
        $csrfTokenId = 'ctp_people_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit people page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP people page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_people');
        }

        return array(
            'title' => $title,
            'peoplePageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/applications', name: 'ctp_applications', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/dashboard.html.twig')]
    public function applicationsAction( Request $request ) {
        if( false == $this->isGranted('ROLE_CTP_USER') ){
            return $this->redirect( $this->generateUrl('ctp-nopermission') );
        }

        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/histocore-and-ihc-lab', name: 'ctp_histocore_and_ihc_lab', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/histocore-and-ihc-lab.html.twig')]
    public function histocoreAndIhcLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_histocore_and_ihc_lab';
        $csrfTokenId = 'ctp_histocore_and_ihc_lab_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit histocore page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP histocore page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_histocore_and_ihc_lab');
        }

        return array(
            'title' => $title,
            'histocorePageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/misi-lab', name: 'ctp_misi-lab', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/misi-lab.html.twig')]
    public function misiLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_misi-lab';
        $csrfTokenId = 'ctp_misi_lab_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit MISI page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP MISI page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_misi-lab');
        }

        return array(
            'title' => $title,
            'misiPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    //Exp Cell Therapy Lab
    #[Route(path: '/experimental-cellular-therapy-lab', name: 'ctp_ect', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/ect.html.twig')]
    public function ectAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_ect';
        $csrfTokenId = 'ctp_ect_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit experimental cellular therapy page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP experimental cellular therapy page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_ect');
        }

        return array(
            'title' => $title,
            'ectPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/genomics-lab', name: 'ctp_genomiclab', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/genomics-lab.html.twig')]
    public function genomicLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_genomiclab';
        $csrfTokenId = 'ctp_genomiclab_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit genomics page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP genomics page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_genomiclab');
        }

        return array(
            'title' => $title,
            'genomiclabPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    //CP Research Lab
    #[Route(path: '/clinical-pathology-research-lab', name: 'ctp_cpresearchlab', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/cp-research-lab.html.twig')]
    public function cpResearchLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_cpresearchlab';
        $csrfTokenId = 'ctp_cpresearchlab_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit clinical pathology research lab page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP clinical pathology research lab page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_cpresearchlab');
        }

        return array(
            'title' => $title,
            'cpresearchlabPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    //Comp Path Lab
    #[Route(path: '/computational-pathology-lab', name: 'ctp_comppathlab', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/comppathlab.html.twig')]
    public function expCellLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_comppathlab';
        $csrfTokenId = 'ctp_comppathlab_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit computational pathology page content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP computational pathology page content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_comppathlab');
        }

        return array(
            'title' => $title,
            'comppathlabPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    //Comp Path Lab
    #[Route(path: '/project-request', name: 'ctp_project_request', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/project_request.html.twig')]
    public function projectRequestAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //ctp_dashboard_tma
    #[Route(path: '/tissue-microarrays', name: 'ctp_dashboard_tma', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/dashboard-tma.html.twig')]
    public function tmaAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //ctp_dashboard_cohort_generator
    #[Route(path: '/cohort-generator', name: 'ctp_dashboard_cohort_generator', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/dashboard-cohort-generator.html.twig')]
    public function cohortGeneratorAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //ctp_dashboard_regulatory_templates
    #[Route(path: '/regulatory-templates', name: 'ctp_dashboard_regulatory_templates', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/regulatory-templates.html.twig')]
    public function regulatoryTemplatesAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //ctp_dashboard_publications
    #[Route(path: '/publication-manager', name: 'ctp_dashboard_publications', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/empty.html.twig')]
    public function publicationsManagerAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //ctp_dashboard_spore
    #[Route(path: '/prostate-cancer-research-data-explorer', name: 'ctp_dashboard_spore', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/empty.html.twig')]
    public function dashboardSporeAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //Investigator engagement guide
    #[Route(path: '/experimental-cellular-therapy-lab/investigator-engagement-guide', name: 'ctp_investigator_engagement_guide', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/investigator-engagement-guide.html.twig')]
    public function investigatorEngagementGuideAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_investigator_engagement_guide';
        $csrfTokenId = 'ctp_investigator_engagement_guide_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit investigator engagement guide content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP investigator engagement guide content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_investigator_engagement_guide');
        }

        return array(
            'title' => $title,
            'investigatorEngagementGuidePageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/irb-ready-workflow-summary', name: 'ctp_irb_ready_workflow_summary', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/irb-ready-workflow-summary.html.twig')]
    public function irbWorkflowSummaryAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_irb_ready_workflow_summary';
        $csrfTokenId = 'ctp_irb_ready_workflow_summary_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit IRB-ready workflow summary content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP IRB-ready workflow summary content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_irb_ready_workflow_summary');
        }

        return array(
            'title' => $title,
            'irbReadyWorkflowSummaryPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/experimental-cellular-therapy-lab/ctem-administrative-service-line-director-profile', name: 'ctp_ctem_administrative_service_line_director_profile', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/ctem-administrative-service-line-director-profile.html.twig')]
    public function ctemAdministrativeServiceLineDirectorProfileAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_ctem_administrative_service_line_director_profile';
        $csrfTokenId = 'ctp_ctem_administrative_service_line_director_profile_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit CTEM administrative service line director profile content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTEM administrative service line director profile content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_ctem_administrative_service_line_director_profile');
        }

        return array(
            'title' => $title,
            'ctemAdministrativeServiceLineDirectorProfilePageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/service-menu', name: 'ctp_ctem_service', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/empty.html.twig')]
    public function ctemServiceAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/service-menu', name: 'ctp_service_menu', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/histocore-service-menu.html.twig')]
    public function serviceMenuAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_service_menu';
        $csrfTokenId = 'ctp_service_menu_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit histocore service menu content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP histocore service menu content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_service_menu');
        }

        return array(
            'title' => $title,
            'serviceMenuPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/sample-submission-checklist', name: 'ctp_sample_submission_checklist', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/sample-submission-checklist.html.twig')]
    public function sampleSubmissionChecklistAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_sample_submission_checklist';
        $csrfTokenId = 'ctp_sample_submission_checklist_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit sample submission checklist content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP sample submission checklist content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_sample_submission_checklist');
        }

        return array(
            'title' => $title,
            'sampleSubmissionChecklistPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/publications', name: 'ctp_histocore_publications', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/Home/histocore-publications.html.twig')]
    public function histocorePublicationsAction( Request $request ) {
        $title = 'Center for Translational Pathology';

        $pageName = 'ctp_histocore_publications';
        $csrfTokenId = 'ctp_histocore_publications_page_content';

        $em = $this->getDoctrine()->getManager();
        $pageContentEntity = $this->getPageContentEntity($pageName, false);
        $isAdmin = $this->isGranted('ROLE_CTP_ADMIN');
        $editMode = $isAdmin && ($request->query->getBoolean('edit') || $request->request->getBoolean('editMode'));

        if( $request->isMethod('POST') && !$isAdmin ) {
            throw $this->createAccessDeniedException('Only CTP admins can edit histocore publications content');
        }

        if( $request->isMethod('POST') && $isAdmin ) {
            $csrfToken = $request->request->get('_token');
            if( !$this->isCsrfTokenValid($csrfTokenId, $csrfToken) ) {
                throw $this->createAccessDeniedException('Invalid CSRF token for CTP histocore publications content update');
            }

            if( !$pageContentEntity ) {
                $pageContentEntity = $this->getPageContentEntity($pageName, true);
            }

            $pageContent = $request->request->get('pageContent');
            $pageContentEntity->setPageContent($pageContent);
            $pageContentEntity->setUpdatedby($this->getUser());

            $em->persist($pageContentEntity);
            $em->flush();

            return $this->redirectToRoute('ctp_histocore_publications');
        }

        return array(
            'title' => $title,
            'histocorePublicationsPageContent' => $pageContentEntity ? $pageContentEntity->getPageContent() : null,
            'isEditMode' => $editMode,
        );
    }

    //TODO:
    //HistoCore & IHC Lab - card does not have border
    //New Project - Initial Inquiry - form has blue surrounding except top - remove it
    //New Project - Initial Inquiry - change top color

//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->isGranted('ROLE_CTP_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->container->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('ctp.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


    //1) https://view-test.med.cornell.edu/center-for-translational-pathology
    //2) login only for dashboard
    //3) add login to footer
    //0) https://view.online/c/wcm/pathology/ -> enable https://view.online/c/wcm/pathology/center-for-translational-pathology
    //1) image Weill Cornell Medicine -> as link to $homeUrl
    //2) text footer Weill Cornell Medicine -> as link to https://weillcornell.org/
    //3) Center for Translational Pathology -> home of Center for Translational Pathology
    //7) External Collaboration / Project Inquiry activate the same behaviour as original site
    //8) http://localhost:3000/project-requests-public: change url to /center-for-translational-pathology/new-project-inquiry
    //9) change urls for 6 squares
    //10) http://localhost:3000/path2path-dashboard-login -> login -> http://localhost:3000/path2path-dashboard
    //#[Route('/{page}', name: 'ctp_home', defaults: ['page' => 'index'])]
    //#[Route('/index', name: 'ctp_index')]
    //#[Route('/{page}', name: 'ctp_home')]
    public function homeAction( Request $request, RouterInterface $router, string $page=null ): Response
    {
        if( $request->get('_route') == 'ctp_index' ) {
            return $this->redirect( $this->generateUrl('ctp_home') );
        }

        //$base = $this->getParameter('kernel.project_dir') . '/public/ctp_site/localhost_3000/';
        $base = $this->getParameter('kernel.project_dir') .
            //'/public/ctp_site/localhost_3000/';
            '/public/orderassets/AppCtpBundle/ctp_site/localhost_3000/';
//            '/ctp_site/localhost_3000/';
//            '/src/App/CtpBundle/Util/ctp_site/localhost_3000/';
//            '/src/templates/AppCtpBundle/ctp_site/localhost_3000/';

        if( !$page ) {
            $page = 'index';
        }

        $file = $base . $page . '.html';
        //exit('$file='.$file);

        if (!file_exists($file)) {
            throw $this->createNotFoundException("Page not found: $page");
        }

        $html = file_get_contents($file);

        //
        // DYNAMIC PREFIX (no hardcoding)
        //
        // Example request path:
        //   /c/wcm/pathology/center-for-translational-pathology/people
        //
        // $page = "people"
        // Remove "/people" → prefix = "/c/wcm/pathology/center-for-translational-pathology"
        //
        //$path = $request->getPathInfo();
        //$prefix = rtrim(substr($path, 0, -strlen($page)), '/');

        //
        // Helper: find real file in _next folder
        //
        $findRealFile = function(string $basename) use ($base) {
            $folder = $base . '_next/';
            $files = scandir($folder);

            foreach ($files as $f) {
                if (str_starts_with($f, pathinfo($basename, PATHINFO_FILENAME))) {
                    return $f; // return first matching file
                }
            }

            return $basename; // fallback
        };

        //exit('$html='.$html);

        //
        // 1. Rewrite internal links
        //
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="/center-for-translational-pathology/$1"',
            $html
        );

        //
        // 1. Rewrite internal links using dynamic prefix
        //
        $basePath = rtrim($request->getBasePath(), '/');
        $prefix   = $basePath . '/center-for-translational-pathology';

        // Case A: "people.html"
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="' . $prefix . '/$1"',
            $html
        );

        // Case B: "/center-for-translational-pathology/people"
        $html = preg_replace(
            '#href="/?center-for-translational-pathology/([^"]*)"#i',
            'href="' . $prefix . '/$1"',
            $html
        );

        //modify footer
        $homeUrl = $router->generate(
            'main_common_home',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $homeUrl = '<a href="'.$homeUrl.'">Home</a>';

        //show login
        if( $this->getUser() ) {
            $loginUrl = $router->generate(
                'employees_logout',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="'.$loginUrl.'">Logout</a>';
        } else {
            $loginUrl = $router->generate(
                'ctp_login',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="' . $loginUrl . '">Login</a>';
        }

//        $wcmLink = '<a href="weillcornell.org/">Weill Cornell Medicine</a>'; //weillcornell.org/
//        //Weill Cornell Medicine · Center for Translational Pathology
        $html = str_replace(
            'Weill Cornell Medicine · Center for Translational Pathology',
            'Weill Cornell Medicine'.' · Center for Translational Pathology · '.$homeUrl . ' · ' . $loginUrl,
            $html
        );

//        //<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"> as link https://weillcornell.org/
//        $wcmUrl = '<a href="https://weillcornell.org/"><img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"></a>';
//        $html = str_replace(
//            '<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto">',
//            $wcmUrl,
//            $html
//        );

        //
        // 2. Rewrite CSS/JS paths
        //
        $html = preg_replace(
            '/(src|href)="(css|js|images|assets)\//i',
            '$1="/ctp_site/localhost_3000/$2/',
            //'$1="/orderassets/AppCtpBundle/ctp_site/localhost_3000/$2/',
            $html
        );

        if(1) {
            //C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\public\orderassets\AppCtpBundle\ctp_site\localhost_3000\faviconbcf9.ico
            //
            // 3. Rewrite Next.js optimized images
            //
            $html = preg_replace_callback(
                //'/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[1]);
                    return '/ctp_site/localhost_3000/_next/' . $real . '"';
                    //return '/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            //
            // 4. Rewrite fallback Next.js JPEGs
            //
            $html = preg_replace_callback(
                '/src="_next\/([^"?]+)\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[2]);
                    return 'src="/ctp_site/localhost_3000/_next/' . $real . '"';
                    //return 'src="/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            //
            // 5. Rewrite any remaining _next/... paths
            //
            $html = preg_replace(
                '/(src|srcset)="_next\//i',
                '$1="/ctp_site/localhost_3000/_next/',
                //'$1="/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/',
                $html
            );
        }

        //
        // 6. Fix accidental leading double slashes
        //
        $html = preg_replace(
            '/(src|srcset)="\/\//i',
            '$1="/',
            $html
        );

//        //<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"> as link https://weillcornell.org/
//        $wcmUrl = '<a href="https://weillcornell.org/"><img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"></a>';
//        $html = str_replace(
//            '<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto">',
//            $wcmUrl,
//            $html
//        );

//        $wcmLink = '<a href="weillcornell.org/">Weill Cornell Medicine</a>'; //weillcornell.org/
//        //Weill Cornell Medicine · Center for Translational Pathology
//        $html = str_replace(
//            'Weill Cornell Medicine · Center for Translational Pathology',
//            $wcmLink.' · Center for Translational Pathology · '.$homeUrl . ' · ' . $loginUrl,
//            $html
//        );

        return $this->render('AppCtpBundle/Default/index.html.twig', [
            'site_html' => $html,
        ]);
    }


}

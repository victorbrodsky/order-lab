<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appProdUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appProdUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);

        if (0 === strpos($pathinfo, '/accession')) {
            // accession
            if (rtrim($pathinfo, '/') === '/accession') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_accession;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'accession');
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::indexAction',  '_route' => 'accession',);
            }
            not_accession:

            // accession_create
            if ($pathinfo === '/accession/') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_accession_create;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::createAction',  '_route' => 'accession_create',);
            }
            not_accession_create:

            // accession_new
            if ($pathinfo === '/accession/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_accession_new;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::newAction',  '_route' => 'accession_new',);
            }
            not_accession_new:

            // accession_show
            if (preg_match('#^/accession/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_accession_show;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'accession_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::showAction',));
            }
            not_accession_show:

            // accession_edit
            if (preg_match('#^/accession/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_accession_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'accession_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::editAction',));
            }
            not_accession_edit:

            // accession_update
            if (preg_match('#^/accession/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_accession_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'accession_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::updateAction',));
            }
            not_accession_update:

            // accession_delete
            if (preg_match('#^/accession/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_accession_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'accession_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\AccessionController::deleteAction',));
            }
            not_accession_delete:

        }

        if (0 === strpos($pathinfo, '/block')) {
            // block
            if (rtrim($pathinfo, '/') === '/block') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_block;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'block');
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::indexAction',  '_route' => 'block',);
            }
            not_block:

            // block_create
            if ($pathinfo === '/block/') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_block_create;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::createAction',  '_route' => 'block_create',);
            }
            not_block_create:

            // block_new
            if ($pathinfo === '/block/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_block_new;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::newAction',  '_route' => 'block_new',);
            }
            not_block_new:

            // block_show
            if (preg_match('#^/block/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_block_show;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'block_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::showAction',));
            }
            not_block_show:

            // block_edit
            if (preg_match('#^/block/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_block_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'block_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::editAction',));
            }
            not_block_edit:

            // block_update
            if (preg_match('#^/block/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_block_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'block_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::updateAction',));
            }
            not_block_update:

            // block_delete
            if (preg_match('#^/block/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_block_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'block_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\BlockController::deleteAction',));
            }
            not_block_delete:

        }

        if (0 === strpos($pathinfo, '/multy')) {
            // multyIndex
            if ($pathinfo === '/multy/index') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_multyIndex;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\MultyScanOrderController::multyIndexAction',  '_route' => 'multyIndex',);
            }
            not_multyIndex:

            if (0 === strpos($pathinfo, '/multy/new')) {
                // multy_create
                if ($pathinfo === '/multy/new') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_multy_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\MultyScanOrderController::multyCreateAction',  '_route' => 'multy_create',);
                }
                not_multy_create:

                // multy_new
                if ($pathinfo === '/multy/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_multy_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\MultyScanOrderController::newMultyAction',  '_route' => 'multy_new',);
                }
                not_multy_new:

            }

            // table
            if ($pathinfo === '/multy/table') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_table;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\MultyScanOrderController::tableAction',  '_route' => 'table',);
            }
            not_table:

        }

        if (0 === strpos($pathinfo, '/orderinfo')) {
            // orderinfo
            if (rtrim($pathinfo, '/') === '/orderinfo') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_orderinfo;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'orderinfo');
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::indexAction',  '_route' => 'orderinfo',);
            }
            not_orderinfo:

            // orderinfo_create
            if ($pathinfo === '/orderinfo/') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_orderinfo_create;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::createAction',  '_route' => 'orderinfo_create',);
            }
            not_orderinfo_create:

            // orderinfo_new
            if ($pathinfo === '/orderinfo/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_orderinfo_new;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::newAction',  '_route' => 'orderinfo_new',);
            }
            not_orderinfo_new:

            // orderinfo_show
            if (preg_match('#^/orderinfo/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_orderinfo_show;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'orderinfo_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::showAction',));
            }
            not_orderinfo_show:

            // orderinfo_edit
            if (preg_match('#^/orderinfo/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_orderinfo_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'orderinfo_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::editAction',));
            }
            not_orderinfo_edit:

            // orderinfo_update
            if (preg_match('#^/orderinfo/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_orderinfo_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'orderinfo_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::updateAction',));
            }
            not_orderinfo_update:

            // orderinfo_delete
            if (preg_match('#^/orderinfo/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_orderinfo_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'orderinfo_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\OrderInfoController::deleteAction',));
            }
            not_orderinfo_delete:

        }

        if (0 === strpos($pathinfo, '/pa')) {
            if (0 === strpos($pathinfo, '/part')) {
                // part
                if (rtrim($pathinfo, '/') === '/part') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_part;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'part');
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::indexAction',  '_route' => 'part',);
                }
                not_part:

                // part_create
                if ($pathinfo === '/part/') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_part_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::createAction',  '_route' => 'part_create',);
                }
                not_part_create:

                // part_new
                if ($pathinfo === '/part/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_part_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::newAction',  '_route' => 'part_new',);
                }
                not_part_new:

                // part_show
                if (preg_match('#^/part/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_part_show;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'part_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::showAction',));
                }
                not_part_show:

                // part_edit
                if (preg_match('#^/part/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_part_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'part_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::editAction',));
                }
                not_part_edit:

                // part_update
                if (preg_match('#^/part/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_part_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'part_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::updateAction',));
                }
                not_part_update:

                // part_delete
                if (preg_match('#^/part/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_part_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'part_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PartController::deleteAction',));
                }
                not_part_delete:

            }

            if (0 === strpos($pathinfo, '/patient')) {
                // patient
                if (rtrim($pathinfo, '/') === '/patient') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_patient;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'patient');
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::indexAction',  '_route' => 'patient',);
                }
                not_patient:

                // patient_create
                if ($pathinfo === '/patient/') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_patient_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::createAction',  '_route' => 'patient_create',);
                }
                not_patient_create:

                // patient_new
                if ($pathinfo === '/patient/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_patient_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::newAction',  '_route' => 'patient_new',);
                }
                not_patient_new:

                // patient_show
                if (preg_match('#^/patient/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_patient_show;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'patient_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::showAction',));
                }
                not_patient_show:

                // patient_edit
                if (preg_match('#^/patient/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_patient_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'patient_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::editAction',));
                }
                not_patient_edit:

                // patient_update
                if (preg_match('#^/patient/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_patient_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'patient_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::updateAction',));
                }
                not_patient_update:

                // patient_delete
                if (preg_match('#^/patient/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_patient_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'patient_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\PatientController::deleteAction',));
                }
                not_patient_delete:

            }

        }

        if (0 === strpos($pathinfo, '/scan')) {
            // scan
            if (rtrim($pathinfo, '/') === '/scan') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_scan;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'scan');
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::indexAction',  '_route' => 'scan',);
            }
            not_scan:

            // scan_create
            if ($pathinfo === '/scan/') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_scan_create;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::createAction',  '_route' => 'scan_create',);
            }
            not_scan_create:

            // scan_new
            if ($pathinfo === '/scan/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_scan_new;
                }

                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::newAction',  '_route' => 'scan_new',);
            }
            not_scan_new:

            // scan_show
            if (preg_match('#^/scan/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_scan_show;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'scan_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::showAction',));
            }
            not_scan_show:

            // scan_edit
            if (preg_match('#^/scan/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_scan_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'scan_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::editAction',));
            }
            not_scan_edit:

            // scan_update
            if (preg_match('#^/scan/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_scan_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'scan_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::updateAction',));
            }
            not_scan_update:

            // scan_delete
            if (preg_match('#^/scan/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_scan_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'scan_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanController::deleteAction',));
            }
            not_scan_delete:

        }

        // index
        if ($pathinfo === '/index') {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_index;
            }

            return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::indexAction',  '_route' => 'index',);
        }
        not_index:

        // singleorder_create
        if ($pathinfo === '/') {
            if ($this->context->getMethod() != 'POST') {
                $allow[] = 'POST';
                goto not_singleorder_create;
            }

            return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::createAction',  '_route' => 'singleorder_create',);
        }
        not_singleorder_create:

        // scanorder_new
        if (rtrim($pathinfo, '/') === '') {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_scanorder_new;
            }

            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'scanorder_new');
            }

            return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::newAction',  '_route' => 'scanorder_new',);
        }
        not_scanorder_new:

        // scanorder_show
        if (preg_match('#^/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_scanorder_show;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'scanorder_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::showAction',));
        }
        not_scanorder_show:

        // scanorder_edit
        if (preg_match('#^/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_scanorder_edit;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'scanorder_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::editAction',));
        }
        not_scanorder_edit:

        // scanorder_update
        if (preg_match('#^/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
            if ($this->context->getMethod() != 'PUT') {
                $allow[] = 'PUT';
                goto not_scanorder_update;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'scanorder_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::updateAction',));
        }
        not_scanorder_update:

        // scanorder_delete
        if (preg_match('#^/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
            if ($this->context->getMethod() != 'DELETE') {
                $allow[] = 'DELETE';
                goto not_scanorder_delete;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'scanorder_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::deleteAction',));
        }
        not_scanorder_delete:

        // scanorder_status
        if (preg_match('#^/(?P<id>[^/]++)/(?P<status>[^/]++)/status$#s', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_scanorder_status;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'scanorder_status')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::statusAction',));
        }
        not_scanorder_status:

        // thanks
        if ($pathinfo === '/thanks') {
            return array (  'orderid' => '',  '_controller' => 'Oleg\\OrderformBundle\\Controller\\ScanOrderController::thanksAction',  '_route' => 'thanks',);
        }

        if (0 === strpos($pathinfo, '/log')) {
            if (0 === strpos($pathinfo, '/login')) {
                // login
                if ($pathinfo === '/login') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_login;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SecurityController::loginAction',  '_route' => 'login',);
                }
                not_login:

                // login_check
                if ($pathinfo === '/login_check') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_login_check;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SecurityController::loginCheckAction',  '_route' => 'login_check',);
                }
                not_login_check:

            }

            // logout
            if ($pathinfo === '/logout') {
                return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SecurityController::logoutAction',  '_route' => 'logout',);
            }

        }

        if (0 === strpos($pathinfo, '/s')) {
            if (0 === strpos($pathinfo, '/slide')) {
                // slide
                if (rtrim($pathinfo, '/') === '/slide') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_slide;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'slide');
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::indexAction',  '_route' => 'slide',);
                }
                not_slide:

                // slide_create
                if ($pathinfo === '/slide/') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_slide_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::createAction',  '_route' => 'slide_create',);
                }
                not_slide_create:

                // slide_new
                if ($pathinfo === '/slide/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_slide_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::newAction',  '_route' => 'slide_new',);
                }
                not_slide_new:

                // slide_show
                if (preg_match('#^/slide/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_slide_show;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'slide_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::showAction',));
                }
                not_slide_show:

                // slide_edit
                if (preg_match('#^/slide/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_slide_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'slide_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::editAction',));
                }
                not_slide_edit:

                // slide_update
                if (preg_match('#^/slide/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_slide_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'slide_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::updateAction',));
                }
                not_slide_update:

                // slide_delete
                if (preg_match('#^/slide/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_slide_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'slide_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SlideController::deleteAction',));
                }
                not_slide_delete:

            }

            if (0 === strpos($pathinfo, '/specimen')) {
                // specimen
                if (rtrim($pathinfo, '/') === '/specimen') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_specimen;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'specimen');
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::indexAction',  '_route' => 'specimen',);
                }
                not_specimen:

                // specimen_create
                if ($pathinfo === '/specimen/') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_specimen_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::createAction',  '_route' => 'specimen_create',);
                }
                not_specimen_create:

                // specimen_new
                if ($pathinfo === '/specimen/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_specimen_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::newAction',  '_route' => 'specimen_new',);
                }
                not_specimen_new:

                // specimen_show
                if (preg_match('#^/specimen/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_specimen_show;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'specimen_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::showAction',));
                }
                not_specimen_show:

                // specimen_edit
                if (preg_match('#^/specimen/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_specimen_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'specimen_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::editAction',));
                }
                not_specimen_edit:

                // specimen_update
                if (preg_match('#^/specimen/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_specimen_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'specimen_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::updateAction',));
                }
                not_specimen_update:

                // specimen_delete
                if (preg_match('#^/specimen/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_specimen_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'specimen_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\SpecimenController::deleteAction',));
                }
                not_specimen_delete:

            }

            if (0 === strpos($pathinfo, '/stain')) {
                // stain
                if (rtrim($pathinfo, '/') === '/stain') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stain;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'stain');
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::indexAction',  '_route' => 'stain',);
                }
                not_stain:

                // stain_create
                if ($pathinfo === '/stain/') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_stain_create;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::createAction',  '_route' => 'stain_create',);
                }
                not_stain_create:

                // stain_new
                if ($pathinfo === '/stain/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stain_new;
                    }

                    return array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::newAction',  '_route' => 'stain_new',);
                }
                not_stain_new:

                // stain_show
                if (preg_match('#^/stain/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stain_show;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'stain_show')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::showAction',));
                }
                not_stain_show:

                // stain_edit
                if (preg_match('#^/stain/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stain_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'stain_edit')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::editAction',));
                }
                not_stain_edit:

                // stain_update
                if (preg_match('#^/stain/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_stain_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'stain_update')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::updateAction',));
                }
                not_stain_update:

                // stain_delete
                if (preg_match('#^/stain/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_stain_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'stain_delete')), array (  '_controller' => 'Oleg\\OrderformBundle\\Controller\\StainController::deleteAction',));
                }
                not_stain_delete:

            }

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}

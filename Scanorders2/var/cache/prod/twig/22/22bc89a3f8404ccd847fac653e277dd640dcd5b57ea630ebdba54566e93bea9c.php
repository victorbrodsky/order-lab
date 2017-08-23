<?php

/* OlegUserdirectoryBundle:Profile:show_user.html.twig */
class __TwigTemplate_61ccfe5f8b3674ea845e20fd2d91d0b212d5231d2ff99615f23cffeb25cb9ce7 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 17
        $this->parent = $this->loadTemplate("OlegUserdirectoryBundle::Default/base.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 17);
        $this->blocks = array(
            'header' => array($this, 'block_header'),
            'additionalcss' => array($this, 'block_additionalcss'),
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
            'additionaljs' => array($this, 'block_additionaljs'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegUserdirectoryBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 61
        $context["userform"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/userformmacros.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 61);
        // line 17
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 19
    public function block_header($context, array $blocks = array())
    {
        // line 20
        echo "
    ";
        // line 21
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "fellapp")) {
            // line 22
            echo "        ";
            $this->loadTemplate("OlegFellAppBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 22)->display($context);
            // line 23
            echo "    ";
        }
        // line 24
        echo "
    ";
        // line 25
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "scan")) {
            // line 26
            echo "        ";
            $this->loadTemplate("OlegOrderformBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 26)->display($context);
            // line 27
            echo "    ";
        }
        // line 28
        echo "
    ";
        // line 29
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "employees")) {
            // line 30
            echo "        ";
            $this->loadTemplate("OlegUserdirectoryBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 30)->display($context);
            // line 31
            echo "    ";
        }
        // line 32
        echo "
    ";
        // line 33
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "deidentifier")) {
            // line 34
            echo "        ";
            $this->loadTemplate("OlegDeidentifierBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 34)->display($context);
            // line 35
            echo "        ";
            // line 36
            echo "            ";
            // line 37
            echo "        ";
            // line 38
            echo "        ";
            // line 39
            echo "            ";
            // line 40
            echo "        ";
            // line 41
            echo "        ";
            // line 42
            echo "            ";
            // line 43
            echo "        ";
            // line 44
            echo "        ";
            // line 45
            echo "    ";
        }
        // line 46
        echo "
    ";
        // line 47
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "vacreq")) {
            // line 48
            echo "        ";
            $this->loadTemplate("OlegVacReqBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 48)->display($context);
            // line 49
            echo "    ";
        }
        // line 50
        echo "
    ";
        // line 51
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "calllog")) {
            // line 52
            echo "        ";
            $this->loadTemplate("OlegCallLogBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 52)->display($context);
            // line 53
            echo "    ";
        }
        // line 54
        echo "
    ";
        // line 55
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "translationalresearch")) {
            // line 56
            echo "        ";
            $this->loadTemplate("OlegTranslationalResearchBundle:Default:navbar.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 56)->display($context);
            // line 57
            echo "    ";
        }
        // line 58
        echo "
";
    }

    // line 64
    public function block_additionalcss($context, array $blocks = array())
    {
        // line 65
        echo "    ";
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "3a5881f_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3a5881f_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/3a5881f_steve-snapshot_1.css");
            // line 68
            echo "        <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "3a5881f"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_3a5881f") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("css/3a5881f.css");
            echo "        <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
        // line 69
        echo "       
";
    }

    // line 72
    public function block_title($context, array $blocks = array())
    {
        // line 73
        echo "    ";
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "
";
    }

    // line 77
    public function block_content($context, array $blocks = array())
    {
        // line 78
        echo "
";
        // line 79
        if (array_key_exists("entity", $context)) {
            // line 80
            echo "        
    ";
            // line 81
            if (array_key_exists("customh", $context)) {
                // line 82
                echo "        ";
                $context["showusermacros"] = $this;
                // line 83
                echo "        ";
                echo $context["showusermacros"]->getsnapshotcustomh((isset($context["entity"]) ? $context["entity"] : null), (isset($context["sitename"]) ? $context["sitename"] : null), (isset($context["cycle"]) ? $context["cycle"] : null), (isset($context["getOriginalname"]) ? $context["getOriginalname"] : null), (isset($context["getAbsoluteUploadFullPath"]) ? $context["getAbsoluteUploadFullPath"] : null), (isset($context["getUsernameOptimal"]) ? $context["getUsernameOptimal"] : null), (isset($context["getHeadInfo"]) ? $context["getHeadInfo"] : null));
                echo "
    ";
            } else {
                // line 84
                echo "    
        ";
                // line 85
                echo $context["userform"]->getsnapshot_steve((isset($context["entity"]) ? $context["entity"] : null), (isset($context["sitename"]) ? $context["sitename"] : null), (isset($context["cycle"]) ? $context["cycle"] : null));
                echo "
    ";
            }
            // line 87
            echo "
    ";
            // line 88
            if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN")) {
                // line 89
                echo "        <br>
        <div class=\"well well-sm\">
            <strong>Roles (shown only to Platform Admin):</strong><br>
            ";
                // line 92
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["entity"]) ? $context["entity"] : null), "roles", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                    // line 93
                    echo "                ";
                    if (($context["role"] != "ROLE_USER")) {
                        // line 94
                        echo "                    ";
                        // line 95
                        echo "                    ";
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getRoleAliasByName", array(0 => $context["role"]), "method"), "html", null, true);
                        echo "<br>
                ";
                    }
                    // line 97
                    echo "            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 98
                echo "        </div>
    ";
            }
            // line 100
            echo "
    <br>

    ";
            // line 104
            echo "    ";
            // line 105
            echo "
    <button id=\"btnShowUserOnly\" class=\"btn btn-primary\" name=\"btnShowUserOnly\" type=\"button\" onclick=\"showUserDetailsAjax(";
            // line 106
            echo twig_escape_filter($this->env, (isset($context["user_id"]) ? $context["user_id"] : null), "html", null, true);
            echo ")\">View Details</button>

    ";
            // line 109
            echo "        ";
            // line 110
            echo "        ";
            // line 111
            echo "    ";
            // line 112
            echo "
    ";
            // line 114
            echo "
    <div id=\"user-details\"></div>

    ";
        }
        // line 118
        echo "    
";
    }

    // line 123
    public function block_additionaljs($context, array $blocks = array())
    {
        // line 124
        echo "
";
        // line 126
        echo "    ";
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "e343d1c_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_e343d1c_0") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/e343d1c_hinclude_1.js");
            // line 129
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "e343d1c"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_assetic_e343d1c") : $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("js/e343d1c.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 131
        echo "        
    <script>
    
        var cycle = 'show';
    
        \$(document).ready(function() {           

            //alert(\"yay!\");
            //console.log(\"show_user.html ready\");                
            
            //setTimeout(function(){ 
                //alert(\"Hello\"); 
            //    initUserForm();
            //}, 10000);
            
            var timesRun = 0;
            var intervalHinclude = setInterval( function(){
                //console.log(\"timesRun=\"+timesRun);
                timesRun++;
                //if(timesRun === 60){
                if( \$(\"#user-profile-form\").length || timesRun > 60 ) {  //60 sec total waiting time              
                    initUserForm();                   
                    clearInterval(intervalHinclude);
                }
                //do whatever here..
            }, 1000); 

        });


        function showUserDetailsAjax( userid ) {
            //console.log('userid=' + userid);
            //var btn = \$('#btnShowUserOnly');
            var btn = document.getElementById(\"btnShowUserOnly\");

            var lbtn = Ladda.create(btn);
            lbtn.start();

            var url = Routing.generate('employees_showuser_only_ajax');
            url = url + \"?userid=\"+userid;
            //console.log(\"url=\"+url);

            \$.ajax({
                url: url,
                timeout: _ajaxTimeout,
                type: \"GET\",
                //type: \"POST\",
                //data: {id: userid },
                dataType: 'json',
                async: asyncflag
            }).success(function(response) {
                //console.log(response);
                var template = response;
                \$('#user-details').html(template); //Change the html of the div with the id = \"your_div\"
                \$('#btnShowUserOnly').hide();
                //initUserForm();
            }).done(function() {
                lbtn.stop();
            }).error(function(jqXHR, textStatus, errorThrown) {
                console.log('Error : ' + errorThrown);
            });

        }

        
        //init only js related to the attached user form
        function initUserForm() {
            //console.log('user form ready');
    
            //checkBrowserComptability();

            //setCicleShow();

            //\$(this).scrollTop(0);

            //setNavBar();

            //fieldInputMask();

            //tooltip
            //\$(\".element-with-tooltip\").tooltip();
            //initTooltips();

            //initConvertEnterToTab();

            initDatepicker();

            expandTextarea();

            \$('.panel-collapse').collapse({'toggle': false});

            regularCombobox();

            initTreeSelect();

            //composite tree as combobox select2 view
            getComboboxCompositetree();

            //jstree in admin page for Institution tree
            //getJstree('UserdirectoryBundle','Institution');
            //getJstree('UserdirectoryBundle','CommentTypeList');

            //home page institution with user leafs
            //displayInstitutionUserTree();
            //getJstree('UserdirectoryBundle','Institution_User','nomenu','nosearch','closeall');

            getComboboxResidencyspecialty();

            //getComboboxCommentType();

            //init generic comboboxes
            initAllComboboxGeneric();

            //processEmploymentStatusRemoveButtons();

            positionTypeListener();

            initUpdateExpectedPgy();

            //initFileUpload();

            //windowCloseAlert();

            //confirmDeleteWithExpired();

            initDatetimepicker();

            //userCloneListener();

            identifierTypeListener();

            researchLabListener();

            grantListener();

            //initTypeaheadUserSiteSearch();

            degreeListener();

            //generalConfirmAction();
        }

    </script>    
    
";
    }

    // line 283
    public function getsnapshotcustomh($__user__ = null, $__sitename__ = null, $__cycle__ = null, $__getOriginalname__ = null, $__getAbsoluteUploadFullPath__ = null, $__getUsernameOptimal__ = null, $__getHeadInfo__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "user" => $__user__,
            "sitename" => $__sitename__,
            "cycle" => $__cycle__,
            "getOriginalname" => $__getOriginalname__,
            "getAbsoluteUploadFullPath" => $__getAbsoluteUploadFullPath__,
            "getUsernameOptimal" => $__getUsernameOptimal__,
            "getHeadInfo" => $__getHeadInfo__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 284
            echo "
    ";
            // line 285
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle:Profile:show_user.html.twig", 285);
            // line 286
            echo "
    <div align=\"center\">

    <div class=\"snapshot-all\">

        ";
            // line 292
            echo "        <div class=\"snapshot\">

            <div class=\"left\">

                <div class=\"image-box\">
                    ";
            // line 298
            echo "                    ";
            // line 299
            echo "                    ";
            if ((isset($context["getOriginalname"]) ? $context["getOriginalname"] : null)) {
                // line 300
                echo "                        ";
                // line 302
                echo "                        <a href=\"";
                echo twig_escape_filter($this->env, (isset($context["getAbsoluteUploadFullPath"]) ? $context["getAbsoluteUploadFullPath"] : null), "html", null, true);
                echo "\" target=\"_blank\">
                        <img alt=\"";
                // line 303
                echo twig_escape_filter($this->env, (isset($context["getOriginalname"]) ? $context["getOriginalname"] : null), "html", null, true);
                echo "\"
                             src=\"";
                // line 304
                echo twig_escape_filter($this->env, (isset($context["getAbsoluteUploadFullPath"]) ? $context["getAbsoluteUploadFullPath"] : null), "html", null, true);
                echo "\"
                             alt=\"Avatar\"
                             ";
                // line 306
                echo "                            
                        />
                        </a>
                    ";
            } else {
                // line 310
                echo "                        ";
                $context["avatarImage"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/fengyuanchen-image-cropper/img/Placeholder-User-Glyph-Icon.png");
                // line 311
                echo "                        <img src=\"";
                echo twig_escape_filter($this->env, (isset($context["avatarImage"]) ? $context["avatarImage"] : null), "html", null, true);
                echo "\" alt=\"Avatar\" style=\"max-width:100%; max-height:100%;\">
                    ";
            }
            // line 313
            echo "                    ";
            // line 314
            echo "                </div>

                <!--<img src=\"profile-pic.jpg\"> -->

                <div class=\"left-text\">
                    <h2>
                        ";
            // line 321
            echo "                        ";
            echo twig_escape_filter($this->env, (isset($context["getUsernameOptimal"]) ? $context["getUsernameOptimal"] : null), "html", null, true);
            echo "
                    </h2>

                    ";
            // line 325
            echo "                    ";
            // line 326
            echo "                    ";
            $context["headInfos"] = (isset($context["getHeadInfo"]) ? $context["getHeadInfo"] : null);
            // line 327
            echo "                    ";
            if ((twig_length_filter($this->env, (isset($context["headInfos"]) ? $context["headInfos"] : null)) > 0)) {
                // line 328
                echo "                        <div style=\"padding-bottom: 10px;\">
                        <h4>
                            ";
                // line 331
                echo "                            ";
                $context["index"] = 1;
                // line 332
                echo "                            ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable((isset($context["headInfos"]) ? $context["headInfos"] : null));
                foreach ($context['_seq'] as $context["_key"] => $context["headInfoArr"]) {
                    // line 333
                    echo "                                ";
                    if (!twig_in_filter("break", $context["headInfoArr"])) {
                        // line 334
                        echo "                                    ";
                        $context["hrefname"] = $this->getAttribute($context["headInfoArr"], "name", array(), "array");
                        // line 335
                        echo "                                    ";
                        if (($this->getAttribute($context["headInfoArr"], "tablename", array(), "array") == "Institution")) {
                            // line 336
                            echo "                                        ";
                            $context["hrefname"] = (("<small>" . $this->getAttribute($context["headInfoArr"], "name", array(), "array")) . "</small>");
                            // line 337
                            echo "                                    ";
                        }
                        // line 338
                        echo "                                    ";
                        // line 339
                        echo "                                        <a href=\"";
                        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((                        // line 340
(isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => $this->getAttribute(                        // line 341
$context["headInfoArr"], "tablename", array(), "array"), "id" => $this->getAttribute(                        // line 342
$context["headInfoArr"], "id", array(), "array"), "name" => $this->getAttribute(                        // line 343
$context["headInfoArr"], "name", array(), "array"))), "html", null, true);
                        // line 345
                        echo "\">";
                        echo (isset($context["hrefname"]) ? $context["hrefname"] : null);
                        echo "
                                        </a>
                                    ";
                        // line 348
                        echo "                                        ";
                        // line 349
                        echo "                                    ";
                        // line 350
                        echo "                                    <br>
                                ";
                    } elseif ((                    // line 351
$context["headInfoArr"] == "break-hr")) {
                        // line 352
                        echo "                                    ";
                        // line 353
                        echo "                                        ";
                        // line 354
                        echo "                                        <br>
                                    ";
                        // line 356
                        echo "                                ";
                    } elseif (($context["headInfoArr"] == "break-br")) {
                        // line 357
                        echo "                                    ";
                        // line 358
                        echo "                                ";
                    } else {
                        // line 359
                        echo "
                                ";
                    }
                    // line 361
                    echo "                                ";
                    $context["index"] = ((isset($context["index"]) ? $context["index"] : null) + 1);
                    // line 362
                    echo "                            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['headInfoArr'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 363
                echo "                        </h4>
                        </div>
                    ";
            }
            // line 366
            echo "
                    ";
            // line 368
            echo "                    ";
            // line 369
            echo "                        ";
            // line 370
            echo "                            ";
            // line 371
            echo "                                ";
            // line 372
            echo "                                ";
            // line 373
            echo "                                ";
            // line 374
            echo "                            ";
            // line 375
            echo "                        ";
            // line 376
            echo "                    ";
            // line 377
            echo "
                </div>
            </div>

            <div class=\"right\">
                <div class=\"right-text\">

                    ";
            // line 385
            echo "                    ";
            if (($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "email25", array(), "array") || $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "preferredPhone24", array(), "array"))) {
                // line 386
                echo "
                        <div class=\"prefferedinfo\">
                            <h4>Preferred Contact Info:</h4>
                            <table>
                                ";
                // line 390
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "email25", array())) {
                    // line 391
                    echo "                                    <tr class=\"row-withspace\">
                                        <td class=\"left-column\">email:</td>
                                        <td>
                                            ";
                    // line 395
                    echo "                                            <a href=\"mailto:";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "email25", array()), "html", null, true);
                    echo "\" target=\"_top\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["user"]) ? $context["user"] : null), "email25", array()), "html", null, true);
                    echo "</a>
                                        </td>
                                    </tr>
                                ";
                }
                // line 399
                echo "                                ";
                if ($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "preferredPhone24", array())) {
                    // line 400
                    echo "                                    <tr>
                                        <td class=\"left-column\">ph:</td>
                                        <td>
                                            ";
                    // line 404
                    echo "                                            ";
                    echo $context["usermacros"]->getphoneHref($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "preferredPhone24", array()));
                    echo "
                                        </td>
                                    </tr>
                                ";
                }
                // line 408
                echo "                            </table>
                        </div>

                    ";
            }
            // line 412
            echo "
";
            // line 413
            if (array_key_exists("ffff", $context)) {
                // line 414
                echo "                    ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user"]) ? $context["user"] : null), "getLocations", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                    // line 415
                    echo "
                        ";
                    // line 416
                    if (($this->getAttribute($context["location"], "hasLocationTypeName", array(0 => "Employee Home"), "method") == false)) {
                        // line 417
                        echo "
                            <div class=\"contact\">
                                <h4>";
                        // line 419
                        echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "getLocationTypesStr", array()), "html", null, true);
                        echo ":</h4>
                                <table>
                                    ";
                        // line 421
                        if ($this->getAttribute($context["location"], "room", array())) {
                            // line 422
                            echo "                                        <tr>
                                            <td class=\"left-column\">room:</td>
                                            <td>
                                                ";
                            // line 426
                            echo "                                                <a href=\"";
                            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_search_same_object"), array("tablename" => "room", "id" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "id", array()), "name" => $this->getAttribute($this->getAttribute($context["location"], "room", array()), "name", array()))), "html", null, true);
                            echo "\">";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "room", array()), "html", null, true);
                            echo "</a>
                                            </td>
                                        </tr>
                                    ";
                        }
                        // line 430
                        echo "
                                    ";
                        // line 431
                        if ($this->getAttribute($context["location"], "phone", array())) {
                            // line 432
                            echo "                                        <tr>
                                            <td class=\"left-column\">ph:</td>
                                            <td>
                                                ";
                            // line 436
                            echo "                                                ";
                            echo $context["usermacros"]->getphoneHref($this->getAttribute($context["location"], "phone", array()));
                            echo "
                                            </td>
                                        </tr>
                                    ";
                        }
                        // line 440
                        echo "
                                    ";
                        // line 441
                        if ($this->getAttribute($context["location"], "pager", array())) {
                            // line 442
                            echo "                                        <tr>
                                            <td class=\"left-column\">pager:</td>
                                            <td>";
                            // line 444
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "pager", array()), "html", null, true);
                            echo "</td>
                                        </tr>
                                    ";
                        }
                        // line 447
                        echo "
                                    ";
                        // line 448
                        if ($this->getAttribute($context["location"], "ic", array())) {
                            // line 449
                            echo "                                        <tr>
                                            <td class=\"left-column\">i/c:</td>
                                            <td>";
                            // line 451
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "ic", array()), "html", null, true);
                            echo "</td>
                                        </tr>
                                    ";
                        }
                        // line 454
                        echo "
                                    ";
                        // line 455
                        if ($this->getAttribute($context["location"], "fax", array())) {
                            // line 456
                            echo "                                        <tr>
                                            <td class=\"left-column\">fax:</td>
                                            <td>";
                            // line 458
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "fax", array()), "html", null, true);
                            echo "</td>
                                        </tr>
                                    ";
                        }
                        // line 461
                        echo "
                                    ";
                        // line 462
                        if ($this->getAttribute($context["location"], "email", array())) {
                            // line 463
                            echo "                                        <tr>
                                            <td class=\"left-column\">email:</td>
                                            <td>
                                                ";
                            // line 467
                            echo "                                                <a href=\"mailto:";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "email", array()), "html", null, true);
                            echo "\" target=\"_top\">";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["location"], "email", array()), "html", null, true);
                            echo "</a>
                                            </td>
                                        </tr>
                                    ";
                        }
                        // line 471
                        echo "
                                </table>
                            </div>


                            ";
                        // line 476
                        if ((twig_length_filter($this->env, $this->getAttribute($context["location"], "getAssistant", array())) > 0)) {
                            // line 477
                            echo "                                <div class=\"assistant\">
                                    <h4>Assistant:</h4>

                                    ";
                            // line 480
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["location"], "getAssistant", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["assistant"]) {
                                // line 481
                                echo "
                                        <table style=\"padding-bottom: 10px;\">
                                            <tr>
                                                <td class=\"left-column\">name:</td>
                                                <td>
                                                    ";
                                // line 487
                                echo "                                                    <a href=\"";
                                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($context["assistant"], "id", array()))), "html", null, true);
                                echo "\">";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["assistant"], "getUsernameOptimal", array(), "method"), "html", null, true);
                                echo "</a>
                                                </td>
                                            </tr>
                                            ";
                                // line 491
                                echo "                                            <tr>
                                                <td class=\"left-column\" valign=\"top\">ph:</td>
                                                <td>
                                                    <p>
                                                    ";
                                // line 495
                                $context['_parent'] = $context;
                                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["assistant"], "getAllPhones", array(), "method"));
                                foreach ($context['_seq'] as $context["_key"] => $context["phone"]) {
                                    // line 496
                                    echo "                                                        ";
                                    echo twig_escape_filter($this->env, $this->getAttribute($context["phone"], "prefix", array(), "array"), "html", null, true);
                                    echo $context["usermacros"]->getphoneHref($this->getAttribute($context["phone"], "phone", array(), "array"));
                                    echo "<br>
                                                    ";
                                }
                                $_parent = $context['_parent'];
                                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['phone'], $context['_parent'], $context['loop']);
                                $context = array_intersect_key($context, $_parent) + $_parent;
                                // line 498
                                echo "                                                    </p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class=\"left-column\" valign=\"top\">email:</td>
                                                <td>
                                                    ";
                                // line 506
                                echo "                                                    ";
                                // line 507
                                echo "                                                    ";
                                $context['_parent'] = $context;
                                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["assistant"], "getAllEmail", array(), "method"));
                                foreach ($context['_seq'] as $context["_key"] => $context["email"]) {
                                    // line 508
                                    echo "                                                        <a href=\"mailto:";
                                    echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                    echo "\" target=\"_top\">";
                                    echo twig_escape_filter($this->env, $this->getAttribute($context["email"], "email", array(), "array"), "html", null, true);
                                    echo "</a><br>
                                                    ";
                                }
                                $_parent = $context['_parent'];
                                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['email'], $context['_parent'], $context['loop']);
                                $context = array_intersect_key($context, $_parent) + $_parent;
                                // line 510
                                echo "                                                </td>
                                            </tr>

                                        </table>

                                    ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['assistant'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 516
                            echo "
                                </div>
                            ";
                        }
                        // line 519
                        echo "
                        ";
                    }
                    // line 520
                    echo " ";
                    // line 521
                    echo "
                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
            }
            // line 524
            echo "
                </div>
            </div>
        </div>
    </div>

    </div>

";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Profile:show_user.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  945 => 524,  937 => 521,  935 => 520,  931 => 519,  926 => 516,  915 => 510,  904 => 508,  899 => 507,  897 => 506,  888 => 498,  878 => 496,  874 => 495,  868 => 491,  859 => 487,  852 => 481,  848 => 480,  843 => 477,  841 => 476,  834 => 471,  824 => 467,  819 => 463,  817 => 462,  814 => 461,  808 => 458,  804 => 456,  802 => 455,  799 => 454,  793 => 451,  789 => 449,  787 => 448,  784 => 447,  778 => 444,  774 => 442,  772 => 441,  769 => 440,  761 => 436,  756 => 432,  754 => 431,  751 => 430,  741 => 426,  736 => 422,  734 => 421,  729 => 419,  725 => 417,  723 => 416,  720 => 415,  715 => 414,  713 => 413,  710 => 412,  704 => 408,  696 => 404,  691 => 400,  688 => 399,  678 => 395,  673 => 391,  671 => 390,  665 => 386,  662 => 385,  653 => 377,  651 => 376,  649 => 375,  647 => 374,  645 => 373,  643 => 372,  641 => 371,  639 => 370,  637 => 369,  635 => 368,  632 => 366,  627 => 363,  621 => 362,  618 => 361,  614 => 359,  611 => 358,  609 => 357,  606 => 356,  603 => 354,  601 => 353,  599 => 352,  597 => 351,  594 => 350,  592 => 349,  590 => 348,  584 => 345,  582 => 343,  581 => 342,  580 => 341,  579 => 340,  577 => 339,  575 => 338,  572 => 337,  569 => 336,  566 => 335,  563 => 334,  560 => 333,  555 => 332,  552 => 331,  548 => 328,  545 => 327,  542 => 326,  540 => 325,  533 => 321,  525 => 314,  523 => 313,  517 => 311,  514 => 310,  508 => 306,  503 => 304,  499 => 303,  494 => 302,  492 => 300,  489 => 299,  487 => 298,  480 => 292,  473 => 286,  471 => 285,  468 => 284,  450 => 283,  302 => 131,  288 => 129,  283 => 126,  280 => 124,  277 => 123,  272 => 118,  266 => 114,  263 => 112,  261 => 111,  259 => 110,  257 => 109,  252 => 106,  249 => 105,  247 => 104,  242 => 100,  238 => 98,  232 => 97,  226 => 95,  224 => 94,  221 => 93,  217 => 92,  212 => 89,  210 => 88,  207 => 87,  202 => 85,  199 => 84,  193 => 83,  190 => 82,  188 => 81,  185 => 80,  183 => 79,  180 => 78,  177 => 77,  170 => 73,  167 => 72,  162 => 69,  148 => 68,  143 => 65,  140 => 64,  135 => 58,  132 => 57,  129 => 56,  127 => 55,  124 => 54,  121 => 53,  118 => 52,  116 => 51,  113 => 50,  110 => 49,  107 => 48,  105 => 47,  102 => 46,  99 => 45,  97 => 44,  95 => 43,  93 => 42,  91 => 41,  89 => 40,  87 => 39,  85 => 38,  83 => 37,  81 => 36,  79 => 35,  76 => 34,  74 => 33,  71 => 32,  68 => 31,  65 => 30,  63 => 29,  60 => 28,  57 => 27,  54 => 26,  52 => 25,  49 => 24,  46 => 23,  43 => 22,  41 => 21,  38 => 20,  35 => 19,  31 => 17,  29 => 61,  11 => 17,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Profile:show_user.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle\\Resources\\views\\Profile\\show_user.html.twig");
    }
}

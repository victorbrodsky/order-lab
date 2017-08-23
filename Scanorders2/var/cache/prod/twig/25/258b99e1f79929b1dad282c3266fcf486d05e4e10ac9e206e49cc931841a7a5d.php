<?php

/* OlegUserdirectoryBundle:Default:navbar.html.twig */
class __TwigTemplate_56b99382c318cb0328da872bec7898879ed5a75a28447e2656b5ee1c2b81f3c3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'navbar' => array($this, 'block_navbar'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 16
        echo "
";
        // line 18
        echo "
";
        // line 81
        echo "
";
        // line 82
        $context["usernavbar"] = $this;
        // line 83
        echo "
";
        // line 84
        $this->displayBlock('navbar', $context, $blocks);
        // line 318
        echo "
";
    }

    // line 84
    public function block_navbar($context, array $blocks = array())
    {
        // line 85
        echo "
    ";
        // line 86
        $context["pendingadminreview"] = $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment(Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:pendingAdminReview"));
        // line 87
        echo "
    ";
        // line 88
        $context["hasRoleSimpleView"] = $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "hasRole", array(0 => "ROLE_USERDIRECTORY_SIMPLEVIEW"), "method");
        // line 89
        echo "
    <nav class=\"navbar navbar-default\" role=\"navigation\">

    <div class=\"container-fluid\">

        <div class=\"navbar-header\">
            <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\".navbar-ex1-collapse\">
                <span class=\"sr-only\">Toggle navigation</span>
                <span class=\"icon-bar\"></span>
                <span class=\"icon-bar\"></span>
                <span class=\"icon-bar\"></span>
            </button>
            ";
        // line 101
        if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
            // line 102
            echo "                <a class=\"navbar-brand visible-xs visible-sm\" href=\"";
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_home"));
            echo "\">Home</a> <!-- this is for mobile devices - top menu -->
            ";
        }
        // line 104
        echo "        </div>


        <div class=\"collapse navbar-collapse navbar-ex1-collapse\" style=\"max-height:none;\">


            ";
        // line 110
        if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
            // line 111
            echo "
                <ul class=\"nav navbar-nav navbar-left\">

                    ";
            // line 114
            $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle:Default:navbar.html.twig", 114);
            // line 115
            echo "                    ";
            echo $context["usermacros"]->getsiteSwitch();
            echo "

                    ";
            // line 117
            if ( !array_key_exists("minimum", $context)) {
                // line 118
                echo "
                        <li id=\"nav-bar-userhome\" class=\"hidden-xs divider-vertical\">
                            <a href=\"";
                // line 120
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_home"));
                echo "\">
                                <img src=\"";
                // line 121
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/oleguserdirectory/form/img/users-1-64x64.png"), "html", null, true);
                echo "\" alt=\"Employee Directory\" height=\"18\" width=\"18\">
                                Home
                            </a>
                        </li>

                        <li id=\"nav-bar-userlist\" class=\"dropdown\">
                            ";
                // line 127
                if ((array_key_exists("pendingadminreview", $context) && ((isset($context["pendingadminreview"]) ? $context["pendingadminreview"] : null) > 0))) {
                    // line 128
                    echo "                                <a id=\"incoming-orders-menu-title\" href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">List Current<b class=\"caret\"></b></a><!--
                                            --><a id=\"incoming-orders-menu-badge\"
                                                  class=\"element-with-tooltip-always\"
                                                  title=\"Pending Administrative Review\"
                                                  data-toggle=\"tooltip\"
                                                  data-placement=\"bottom\"
                                                  href=\"";
                    // line 134
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_listusers"), array("filter" => "Pending Administrative Review"));
                    echo "\"
                                                ><span class=\"badge\">";
                    // line 135
                    echo twig_escape_filter($this->env, (isset($context["pendingadminreview"]) ? $context["pendingadminreview"] : null), "html", null, true);
                    echo "</span></a>
                            ";
                } else {
                    // line 137
                    echo "                                <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">List Current<b class=\"caret\"></b></a>
                            ";
                }
                // line 139
                echo "                            ";
                echo $context["usernavbar"]->getlistUsers(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_listusers"), ((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_list_common_locations"), (isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null));
                echo "
                        </li>

                        ";
                // line 142
                if (($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR") &&  !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null))) {
                    // line 143
                    echo "                            <li id=\"nav-bar-userlist-previous\" class=\"dropdown\">
                                <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">List Previous<b class=\"caret\"></b></a>
                                ";
                    // line 145
                    echo $context["usernavbar"]->getlistUsers(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_listusers_previous"), null, (isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null));
                    echo "
                            </li>
                        ";
                }
                // line 148
                echo "
                        ";
                // line 149
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR")) {
                    // line 150
                    echo "                            <li id=\"nav-bar-add\" class=\"dropdown\">
                                <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Add<b class=\"caret\"></b></a>
                                <ul class=\"dropdown-menu\">
                                    <li id=\"nav-bar-adduser\" class=\"divider-vertical\"><a href=\"";
                    // line 153
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_new_user"));
                    echo "\">Employee</a></li>
                                    <li id=\"nav-bar-addlocation\" class=\"divider-vertical\"><a href=\"";
                    // line 154
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_locations_pathaction_new_standalone"));
                    echo "\">Location</a></li>
                                </ul>
                            </li>
                        ";
                }
                // line 158
                echo "
                        <li id=\"nav-bar-add\" class=\"dropdown\">
                            <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Download<b class=\"caret\"></b></a>
                            <ul class=\"dropdown-menu\">
                                <li id=\"nav-bar-userlist-download-excel\">
                                    <a href=\"";
                // line 163
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_userlist_download_excel"));
                echo "\">WCM Pathology Directory</a>
                                </li>
                                <li id=\"nav-bar-users-label-download-excel\">
                                    <a href=\"";
                // line 166
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_users_label_preview"));
                echo "\" target=\"_blank\">WCM Pathology Mailing Labels</a>
                                </li>
                            </ul>
                        </li>
                    ";
            }
            // line 171
            echo "
                </ul>

            ";
        }
        // line 175
        echo "
           

            <ul class=\"nav navbar-nav navbar-right\">

                ";
        // line 180
        if ( !array_key_exists("minimum", $context)) {
            // line 181
            echo "
                    ";
            // line 183
            echo "                    ";
            if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_OBSERVER")) {
                // line 184
                echo "                        ";
                if (( !array_key_exists("urltype", $context) || ((isset($context["urltype"]) ? $context["urltype"] : null) != "home"))) {
                    // line 185
                    echo "                            <li id=\"nav-bar-usersearch\">
                            ";
                    // line 187
                    echo "                            ";
                    if ( !array_key_exists("search", $context)) {
                        // line 188
                        echo "                                ";
                        $context["search"] = "";
                        // line 189
                        echo "                            ";
                    }
                    // line 190
                    echo "                            <div>
                            <form class=\"navbar-form navbar-left user-typeahead-search-form\" role=\"search\" id=\"navbar-user-typeahead-search-form\" name=\"usertypeaheadsearchform\" action=\"";
                    // line 191
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_home"));
                    echo "\" method=\"get\">
                                <div class=\"form-group\">
                                    <div id=\"navbar-multiple-datasets-typeahead-search\" class=\"multiple-datasets-typeahead-search\">

                                        ";
                    // line 196
                    echo "                                        ";
                    // line 197
                    echo "                                            ";
                    // line 198
                    echo "                                            ";
                    // line 199
                    echo "                                            ";
                    // line 200
                    echo "                                            ";
                    // line 201
                    echo "                                            ";
                    // line 202
                    echo "                                            ";
                    // line 203
                    echo "
                                        ";
                    // line 205
                    echo "                                        ";
                    // line 206
                    echo "                                            ";
                    // line 207
                    echo "                                                ";
                    // line 208
                    echo "                                                ";
                    // line 209
                    echo "                                                ";
                    // line 210
                    echo "                                                ";
                    // line 211
                    echo "                                                ";
                    // line 212
                    echo "                                            ";
                    // line 213
                    echo "                                            ";
                    // line 214
                    echo "                                                ";
                    // line 215
                    echo "                                            ";
                    // line 216
                    echo "                                        ";
                    // line 217
                    echo "
                                        ";
                    // line 219
                    echo "                                        <div class=\"form-group-typeahead\">
                                            <div class=\"form-group has-feedback\">
                                                <input
                                                        type=\"text\"
                                                        class=\"typeahead submit-on-enter-field form-control\"
                                                        name=\"search\" value=\"";
                    // line 224
                    echo twig_escape_filter($this->env, (isset($context["search"]) ? $context["search"] : null), "html", null, true);
                    echo "\"
                                                        placeholder=\"Search\"
                                                        style=\"font-size: 14px !important;\"
                                                        aria-describedby=\"inputSuccess2Status\">
                                                <span style=\"top:0;\" class=\"glyphicon glyphicon-search form-control-feedback btn\" onclick=\"document.usertypeaheadsearchform.submit();\" aria-hidden=\"true\"></span>
                                                <span id=\"inputSuccess2Status\" class=\"sr-only\">(success)</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                ";
                    // line 236
                    echo "                                ";
                    // line 237
                    echo "                                    ";
                    // line 238
                    echo "                                ";
                    // line 239
                    echo "                                ";
                    // line 240
                    echo "                            </form>
                            </div>
                            </li>
                        ";
                }
                // line 244
                echo "                    ";
            }
            // line 245
            echo "
                    ";
            // line 246
            if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR")) {
                // line 247
                echo "
                        <li id=\"nav-bar-admin\" class=\"dropdown\">
                            <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Admin<b class=\"caret\"></b></a>
                            <ul class=\"dropdown-menu\">

                                <li><a href=\"";
                // line 252
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_accessrequest_list"));
                echo "\">Access Requests</a></li>
                                <li><a href=\"";
                // line 253
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_accountrequest"));
                echo "\">Account Requests</a></li>
                                <li><a href=\"";
                // line 254
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_authorized_users"));
                echo "\">Authorized Users</a></li>

                                ";
                // line 256
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_ADMIN")) {
                    // line 257
                    echo "                                    <li class=\"divider\"></li>
                                    ";
                    // line 259
                    echo "                                    <li><a href=\"";
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_admin_index");
                    echo "\">List Manager</a></li>
                                    <li><a href=\"";
                    // line 260
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("platformlistmanager-list");
                    echo "\">Platform List Manager</a></li>
                                    <li><a href=\"";
                    // line 261
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_admin_hierarchy_index");
                    echo "\">Hierarchy Manager</a></li>
                                    <li><a href=\"";
                    // line 262
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_logger"));
                    echo "\">Event Log</a></li>
                                ";
                }
                // line 264
                echo "
                                ";
                // line 265
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN")) {
                    // line 266
                    echo "                                    <li class=\"divider\"></li>
                                    <li><a href=\"";
                    // line 267
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_import_users_excel"));
                    echo "\">Import Users</a></li>
                                    <li><a href=\"";
                    // line 268
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_siteparameters"));
                    echo "\">Site Settings</a></li>
                                    <li><a
                                            general-data-confirm=\"Are you sure you would like to cleare cahce and update assets? This action will logout all authenticated users.\"
                                            href=\"";
                    // line 271
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("user_update_system_cache_assets");
                    echo "\">Update System's Cache and Assets</a>
                                    </li>
                                ";
                }
                // line 274
                echo "
                                ";
                // line 275
                if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_PLATFORM_ADMIN")) {
                    // line 276
                    echo "                                    <li class=\"divider\"></li>
                                    <li><a href=\"";
                    // line 277
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_data_backup_management"));
                    echo "\">Data Backup Management</a></li>
                                ";
                }
                // line 279
                echo "
                            </ul>
                        </li>

                    ";
            }
            // line 284
            echo "
                ";
        }
        // line 286
        echo "
                ";
        // line 287
        if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USER")) {
            // line 288
            echo "
                    <li id=\"nav-bar-user\" class=\"dropdown\">
                        <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-user\"></span><b class=\"caret\"></b></a>
                        <ul class=\"dropdown-menu\">

                            ";
            // line 293
            if ( !array_key_exists("pendinguser", $context)) {
                // line 294
                echo "                                <li><a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array(), "method"))), "html", null, true);
                echo "\">My Profile (";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getUserNameShortStr", array(), "method"), "html", null, true);
                echo ")</a></li>
                            ";
            }
            // line 296
            echo "
                            ";
            // line 297
            if ( !array_key_exists("pendinguser", $context)) {
                // line 298
                echo "                                <li><a href=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_about_page"));
                echo "\">About</a></li>
                            ";
            }
            // line 300
            echo "
                            <li><a href=\"";
            // line 301
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_logout"));
            echo "\">Log Out</a></li>

                        </ul>
                    </li>

                ";
        }
        // line 307
        echo "
            </ul>


        </div><!-- /.navbar-collapse -->

    </div><!-- /.container-fluid -->

    </nav>
    
";
    }

    // line 19
    public function getlistUsers($__pathlink__ = null, $__pathlink_loc__ = null, $__hasRoleSimpleView__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "pathlink" => $__pathlink__,
            "pathlink_loc" => $__pathlink_loc__,
            "hasRoleSimpleView" => $__hasRoleSimpleView__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 20
            echo "    <ul class=\"dropdown-menu\">

        <li><a href=\"";
            // line 22
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null));
            echo "\">Employees</a></li>
        <li><a href=\"";
            // line 23
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Employees"));
            echo "\">WCM Pathology Employees</a></li>
        <li><a href=\"";
            // line 24
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Faculty"));
            echo "\">WCM Pathology Faculty</a></li>
        <li><a href=\"";
            // line 25
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Clinical Faculty"));
            echo "\">WCM Pathology Clinical Faculty</a></li>

        ";
            // line 27
            if ( !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null)) {
                // line 28
                echo "            <li><a href=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Physicians"));
                echo "\">WCM Pathology Physicians</a></li>
        ";
            }
            // line 30
            echo "
        <hr style=\"margin-bottom:0; margin-top:0;\">

        <li><a href=\"";
            // line 33
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Research Faculty"));
            echo "\">WCM Pathology Research Faculty</a></li>

        ";
            // line 35
            if ( !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null)) {
                // line 36
                echo "            <li><a href=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Principal Investigators of Research Labs"));
                echo "\"> - WCM Pathology Principal Investigators of Research Labs</a></li>
            <li><a href=\"";
                // line 37
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Faculty in Research Labs"));
                echo "\"> - WCM Pathology Faculty in Research Labs</a></li>
        ";
            }
            // line 39
            echo "
        <hr style=\"margin-bottom:0; margin-top:0;\">

        <li><a href=\"";
            // line 42
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Pathology Staff"));
            echo "\">WCM Pathology Staff</a></li>
        <li><a href=\"";
            // line 43
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "NYP Pathology Staff"));
            echo "\">NYP Pathology Staff</a></li>

        ";
            // line 45
            if ( !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null)) {
                // line 46
                echo "            <li><a href=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP Pathology Staff in Research Labs"));
                echo "\"> - WCM or NYP Pathology Staff in Research Labs</a></li>
        ";
            }
            // line 48
            echo "
        <hr style=\"margin-bottom:0; margin-top:0;\">

        <li><a href=\"";
            // line 51
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Anatomic Pathology Faculty"));
            echo "\">WCM Anatomic Pathology Faculty</a></li>
        <li><a href=\"";
            // line 52
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Laboratory Medicine Faculty"));
            echo "\">WCM Laboratory Medicine Faculty</a></li>

        <hr style=\"margin-bottom:0; margin-top:0;\">

        <li><a href=\"";
            // line 56
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP Pathology Residents"));
            echo "\">WCM or NYP Pathology Residents</a></li>

        ";
            // line 58
            if ( !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null)) {
                // line 59
                echo "            <li><a href=\"";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP AP/CP Residents"));
                echo "\"> - WCM or NYP AP/CP Residents</a></li>
            <li><a href=\"";
                // line 60
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP AP Residents"));
                echo "\"> - WCM or NYP AP Residents</a></li>
            <li><a href=\"";
                // line 61
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP AP Only Residents"));
                echo "\"> - WCM or NYP AP Only Residents</a></li>
            <li><a href=\"";
                // line 62
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP CP Residents"));
                echo "\"> - WCM or NYP CP Residents</a></li>
            <li><a href=\"";
                // line 63
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP CP Only Residents"));
                echo "\"> - WCM or NYP CP Only Residents</a></li>
        ";
            }
            // line 65
            echo "
        <li><a href=\"";
            // line 66
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM or NYP Pathology Fellows"));
            echo "\">WCM or NYP Pathology Fellows</a></li>
        <li><a href=\"";
            // line 67
            echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink"]) ? $context["pathlink"] : null), array("filter" => "WCM Non-academic Faculty"));
            echo "\">WCM Non-academic Faculty</a></li>

        ";
            // line 69
            if ((array_key_exists("pathlink_loc", $context) && (isset($context["pathlink_loc"]) ? $context["pathlink_loc"] : null))) {
                // line 70
                echo "            <hr style=\"margin-bottom:0; margin-top:0;\">
            <li><a href=\"";
                // line 71
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink_loc"]) ? $context["pathlink_loc"] : null), array("filter" => "Common Locations"));
                echo "\">Common Locations</a></li>
            ";
                // line 72
                if ( !(isset($context["hasRoleSimpleView"]) ? $context["hasRoleSimpleView"] : null)) {
                    // line 73
                    echo "                <li><a href=\"";
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink_loc"]) ? $context["pathlink_loc"] : null), array("filter" => "WCM & NYP Pathology Common Locations"));
                    echo "\">WCM & NYP Pathology Common Locations</a></li>
                <li><a href=\"";
                    // line 74
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink_loc"]) ? $context["pathlink_loc"] : null), array("filter" => "WCM Pathology Common Locations"));
                    echo "\">WCM Pathology Common Locations</a></li>
                <li><a href=\"";
                    // line 75
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath((isset($context["pathlink_loc"]) ? $context["pathlink_loc"] : null), array("filter" => "NYP Pathology Common Locations"));
                    echo "\">NYP Pathology Common Locations</a></li>
            ";
                }
                // line 77
                echo "        ";
            }
            // line 78
            echo "
    </ul>
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
        return "OlegUserdirectoryBundle:Default:navbar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  657 => 78,  654 => 77,  649 => 75,  645 => 74,  640 => 73,  638 => 72,  634 => 71,  631 => 70,  629 => 69,  624 => 67,  620 => 66,  617 => 65,  612 => 63,  608 => 62,  604 => 61,  600 => 60,  595 => 59,  593 => 58,  588 => 56,  581 => 52,  577 => 51,  572 => 48,  566 => 46,  564 => 45,  559 => 43,  555 => 42,  550 => 39,  545 => 37,  540 => 36,  538 => 35,  533 => 33,  528 => 30,  522 => 28,  520 => 27,  515 => 25,  511 => 24,  507 => 23,  503 => 22,  499 => 20,  485 => 19,  471 => 307,  462 => 301,  459 => 300,  453 => 298,  451 => 297,  448 => 296,  440 => 294,  438 => 293,  431 => 288,  429 => 287,  426 => 286,  422 => 284,  415 => 279,  410 => 277,  407 => 276,  405 => 275,  402 => 274,  396 => 271,  390 => 268,  386 => 267,  383 => 266,  381 => 265,  378 => 264,  373 => 262,  369 => 261,  365 => 260,  360 => 259,  357 => 257,  355 => 256,  350 => 254,  346 => 253,  342 => 252,  335 => 247,  333 => 246,  330 => 245,  327 => 244,  321 => 240,  319 => 239,  317 => 238,  315 => 237,  313 => 236,  299 => 224,  292 => 219,  289 => 217,  287 => 216,  285 => 215,  283 => 214,  281 => 213,  279 => 212,  277 => 211,  275 => 210,  273 => 209,  271 => 208,  269 => 207,  267 => 206,  265 => 205,  262 => 203,  260 => 202,  258 => 201,  256 => 200,  254 => 199,  252 => 198,  250 => 197,  248 => 196,  241 => 191,  238 => 190,  235 => 189,  232 => 188,  229 => 187,  226 => 185,  223 => 184,  220 => 183,  217 => 181,  215 => 180,  208 => 175,  202 => 171,  194 => 166,  188 => 163,  181 => 158,  174 => 154,  170 => 153,  165 => 150,  163 => 149,  160 => 148,  154 => 145,  150 => 143,  148 => 142,  141 => 139,  137 => 137,  132 => 135,  128 => 134,  120 => 128,  118 => 127,  109 => 121,  105 => 120,  101 => 118,  99 => 117,  93 => 115,  91 => 114,  86 => 111,  84 => 110,  76 => 104,  70 => 102,  68 => 101,  54 => 89,  52 => 88,  49 => 87,  47 => 86,  44 => 85,  41 => 84,  36 => 318,  34 => 84,  31 => 83,  29 => 82,  26 => 81,  23 => 18,  20 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Default:navbar.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle\\Resources\\views\\Default\\navbar.html.twig");
    }
}

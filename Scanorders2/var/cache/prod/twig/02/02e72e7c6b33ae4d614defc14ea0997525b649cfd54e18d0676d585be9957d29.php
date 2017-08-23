<?php

/* OlegUserdirectoryBundle:Default:home.html.twig */
class __TwigTemplate_dafca6f64b2ea281560c6af847b0042f7331cac6339c9552cfc97bae89beb9b7 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 19
        $this->parent = $this->loadTemplate("OlegUserdirectoryBundle::Default/base.html.twig", "OlegUserdirectoryBundle:Default:home.html.twig", 19);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
            'contentleft' => array($this, 'block_contentleft'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegUserdirectoryBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 17
        $context["urltype"] = "home";
        // line 21
        $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle:Default:home.html.twig", 21);
        // line 19
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 24
    public function block_title($context, array $blocks = array())
    {
        // line 25
        echo "    Employee Directory
";
    }

    // line 29
    public function block_content($context, array $blocks = array())
    {
        // line 30
        echo "
    <div style=\"margin-top:25px;\">

        <form class=\"navbar-form user-typeahead-search-form\" role=\"search\" id=\"user-typeahead-search-form\" action=\"";
        // line 33
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_home"));
        echo "\" method=\"get\">
            <div class=\"form-group\">
                <div id=\"multiple-datasets-typeahead-search\" class=\"multiple-datasets-typeahead-search\">
                    <input
                           style=\"width: 460px;\"
                           type=\"text\"
                           class=\"typeahead submit-on-enter-field\"
                           name=\"search\" value=\"";
        // line 40
        echo twig_escape_filter($this->env, (isset($context["search"]) ? $context["search"] : null), "html", null, true);
        echo "\"
                           placeholder=\"Search for a name, service, division, etc\"
                    >
                </div>
            </div>
            <button type=\"submit\" class=\"btn btn-lg\" style=\"height: 50px\">Search</button>
            ";
        // line 47
        echo "        </form>

        <br>


        ";
        // line 52
        $context["treemacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Tree/treemacros.html.twig", "OlegUserdirectoryBundle:Default:home.html.twig", 52);
        // line 53
        echo "        <a role=\"button\" data-toggle=\"collapse\" href=\"#collapseInstitutionUserTree\" onclick=\"displayInstitutionUserTree('default');\">Organizational Tree</a>
        <div id=\"collapseInstitutionUserTree\" class=\"panel-collapse collapse\">
            ";
        // line 55
        echo $context["treemacros"]->getjstreemacros("composite-tree", "UserdirectoryBundle", "Institution_User", "employees_showuser", "nosearch");
        echo "
        </div>

        <br>

        ";
        // line 61
        echo "        ";
        $context["locationShowed"] = false;
        // line 62
        echo "        ";
        if ((isset($context["locations"]) ? $context["locations"] : null)) {
            // line 63
            echo "            ";
            if ((twig_length_filter($this->env, (isset($context["locations"]) ? $context["locations"] : null)) > 0)) {
                // line 64
                echo "                ";
                $this->loadTemplate("OlegUserdirectoryBundle::Location/locations-content-search.html.twig", "OlegUserdirectoryBundle:Default:home.html.twig", 64)->display(array_merge($context, array("sitename" => (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null))));
                // line 65
                echo "                <br>
                ";
                // line 66
                $context["locationShowed"] = true;
                // line 67
                echo "            ";
            }
            // line 68
            echo "        ";
        }
        // line 69
        echo "
        ";
        // line 71
        echo "        ";
        $context["userShowed"] = false;
        // line 72
        echo "        ";
        if ((((isset($context["entities"]) ? $context["entities"] : null) && (twig_length_filter($this->env, (isset($context["entities"]) ? $context["entities"] : null)) > 0)) || (array_key_exists("sameusers", $context) && (isset($context["sameusers"]) ? $context["sameusers"] : null)))) {
            // line 73
            echo "            ";
            $this->loadTemplate("OlegUserdirectoryBundle::Admin/users-content.html.twig", "OlegUserdirectoryBundle:Default:home.html.twig", 73)->display(array_merge($context, array("sitename" => (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null))));
            // line 74
            echo "            ";
            $context["userShowed"] = true;
            // line 75
            echo "        ";
        }
        // line 76
        echo "
        ";
        // line 78
        echo "        ";
        if (((isset($context["search"]) ? $context["search"] : null) && ( !(isset($context["userShowed"]) ? $context["userShowed"] : null) &&  !(isset($context["locationShowed"]) ? $context["locationShowed"] : null)))) {
            // line 79
            echo "            <br><br>
            <h5 class=\"text-info\">No results found.</h5>
        ";
        }
        // line 82
        echo "

    </div>

";
    }

    // line 90
    public function block_contentleft($context, array $blocks = array())
    {
        // line 91
        echo "
    ";
        // line 92
        if ((( !(isset($context["entities"]) ? $context["entities"] : null) &&  !(isset($context["locations"]) ? $context["locations"] : null)) &&  !array_key_exists("sameusers", $context))) {
            // line 93
            echo "
        <div style=\"margin-top:50px;\">

            <p>
                Welcome to the Employee Directory!
            </p>

            ";
            // line 100
            if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getEmail", array()) == "")) {
                // line 101
                echo "                <p>
                    If you would like to receive email notifications regarding your orders, please take a moment to update
                    <a href=\"";
                // line 103
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array()))), "html", null, true);
                echo "\">your profile</a>
                    by adding your email account.
                </p>
            ";
            }
            // line 107
            echo "

            <p>
                Please review and update your <a href=\"";
            // line 110
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array()))), "html", null, true);
            echo "\">profile</a>.
            </p>


            <br>

            <p>

                ";
            // line 118
            if ($this->env->getExtension('Symfony\Bridge\Twig\Extension\SecurityExtension')->isGranted("ROLE_USERDIRECTORY_EDITOR")) {
                // line 119
                echo "
                    ";
                // line 120
                $context["pendingadminreview"] = $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment(Symfony\Bridge\Twig\Extension\HttpKernelExtension::controller("OlegUserdirectoryBundle:User:pendingAdminReview"));
                // line 121
                echo "
                    There are:

                    <ul>

                        <li>
                            <a href=\"";
                // line 127
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_accessrequest_list"));
                echo "\">";
                echo twig_escape_filter($this->env, (isset($context["accessreqs"]) ? $context["accessreqs"] : null), "html", null, true);
                echo " unprocessed access request(s).</a>
                        </li>

                        <li>
                            <a href=\"";
                // line 131
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["employees_sitename"]) ? $context["employees_sitename"] : null) . "_listusers"), array("filter" => "Pending Administrative Review"));
                echo "\">";
                echo twig_escape_filter($this->env, (isset($context["pendingadminreview"]) ? $context["pendingadminreview"] : null), "html", null, true);
                echo " user profile(s) with data pending administrative review and approval.</a>
                        </li>

                    </ul>

                ";
            }
            // line 137
            echo "
            </p>


        </div>

        <br>

        ";
            // line 146
            echo "        ";
            // line 147
            echo "
        ";
            // line 149
            echo "        ";
            // line 150
            echo "            ";
            // line 151
            echo "        ";
            // line 152
            echo "        ";
            // line 153
            echo "
        ";
            // line 154
            echo $context["usermacros"]->getuserTeamAjax($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "user", array()), "getId", array()), "home", "My Team", (isset($context["employees_sitename"]) ? $context["employees_sitename"] : null));
            echo "

    ";
        }
        // line 157
        echo "
";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:Default:home.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  264 => 157,  258 => 154,  255 => 153,  253 => 152,  251 => 151,  249 => 150,  247 => 149,  244 => 147,  242 => 146,  232 => 137,  221 => 131,  212 => 127,  204 => 121,  202 => 120,  199 => 119,  197 => 118,  186 => 110,  181 => 107,  174 => 103,  170 => 101,  168 => 100,  159 => 93,  157 => 92,  154 => 91,  151 => 90,  143 => 82,  138 => 79,  135 => 78,  132 => 76,  129 => 75,  126 => 74,  123 => 73,  120 => 72,  117 => 71,  114 => 69,  111 => 68,  108 => 67,  106 => 66,  103 => 65,  100 => 64,  97 => 63,  94 => 62,  91 => 61,  83 => 55,  79 => 53,  77 => 52,  70 => 47,  61 => 40,  51 => 33,  46 => 30,  43 => 29,  38 => 25,  35 => 24,  31 => 19,  29 => 21,  27 => 17,  11 => 19,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:Default:home.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle\\Resources\\views\\Default\\home.html.twig");
    }
}

<?php

/* OlegUserdirectoryBundle:AccessRequest:authorized_users.html.twig */
class __TwigTemplate_ad5ea9f72c234bf8af888d5944f0bdbed37cc37b99f457fecb30621a77f027e9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        // line 35
        return $this->loadTemplate((isset($context["extendStr"]) ? $context["extendStr"] : null), "OlegUserdirectoryBundle:AccessRequest:authorized_users.html.twig", 35);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 19
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "employees")) {
            // line 20
            $context["extendStr"] = "OlegUserdirectoryBundle::Default/base.html.twig";
        } elseif ((        // line 21
(isset($context["sitename"]) ? $context["sitename"] : null) == "fellapp")) {
            // line 22
            $context["extendStr"] = "OlegFellAppBundle::Default/base.html.twig";
        } elseif ((        // line 23
(isset($context["sitename"]) ? $context["sitename"] : null) == "deidentifier")) {
            // line 24
            $context["extendStr"] = "OlegDeidentifierBundle::Default/base.html.twig";
        } elseif ((        // line 25
(isset($context["sitename"]) ? $context["sitename"] : null) == "scan")) {
            // line 26
            $context["extendStr"] = "OlegOrderformBundle::Default/base.html.twig";
        } elseif ((        // line 27
(isset($context["sitename"]) ? $context["sitename"] : null) == "vacreq")) {
            // line 28
            $context["extendStr"] = "OlegVacReqBundle::Default/base.html.twig";
        } elseif ((        // line 29
(isset($context["sitename"]) ? $context["sitename"] : null) == "calllog")) {
            // line 30
            $context["extendStr"] = "OlegCallLogBundle::Default/base.html.twig";
        } elseif ((        // line 31
(isset($context["sitename"]) ? $context["sitename"] : null) == "translationalresearch")) {
            // line 32
            $context["extendStr"] = "OlegTranslationalResearchBundle::Default/base.html.twig";
        }
        // line 35
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 39
    public function block_title($context, array $blocks = array())
    {
        // line 40
        echo "    Authorized Users for ";
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo "
";
    }

    // line 45
    public function block_content($context, array $blocks = array())
    {
        // line 46
        echo "
    ";
        // line 47
        $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle:AccessRequest:authorized_users.html.twig", 47);
        // line 48
        echo "
    <h3 class=\"text-info\">Authorized Users for ";
        // line 49
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo "</h3>

    ";
        // line 52
        echo "        ";
        // line 53
        echo "    ";
        // line 54
        echo "
    <hr>

    <form id=\"add_authorized_user_form\" action=\"";
        // line 57
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_add_authorized_user"));
        echo "\" method=\"GET\">

        ";
        // line 59
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "keytype", array()));
        echo "
        ";
        // line 60
        echo $context["formmacros"]->getfield($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "primaryPublicUserId", array()));
        echo "

        <p>
            <button type='submit' class=\"btn btn-info btn-sm\">Add Authorized User</button>
        </p>
    </form>

    <hr>

    <br>

    <table class=\"table table-hover table-condensed text-left\">
        <thead>
        <tr>
            ";
        // line 75
        echo "            <th>";
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "Full Name", "infos.displayName");
        echo "</th>
            <th>";
        // line 76
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "User ID Type", "keytype.name");
        echo "</th>
            <th>";
        // line 77
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "User ID", "user.primaryPublicUserId");
        echo "</th>
            <th>";
        // line 78
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "Email", "infos.email");
        echo "</th>
            <th>";
        // line 79
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "Phone Number", "infos.preferredPhone");
        echo "</th>
            <th>";
        // line 80
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "Role(s)", "user.roles");
        echo "</th>
            <th>";
        // line 81
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["users"]) ? $context["users"] : null), "Last Login", "user.lastLogin");
        echo "</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody data-link=\"row\" class=\"rowlink\">
        ";
        // line 86
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["users"]) ? $context["users"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["user"]) {
            // line 87
            echo "
            <td style=\"display: none\">
                <a href=\"";
            // line 89
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_authorization_user_management"), array("id" => $this->getAttribute($context["user"], "id", array()))), "html", null, true);
            echo "\" target=\"_blank\">Authorization Management</a>
            </td>
            <td class=\"rowlink-skip\">
                <a href=\"";
            // line 92
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($context["user"], "id", array()))), "html", null, true);
            echo "\" target=\"_blank\">";
            echo twig_escape_filter($this->env, $context["user"], "html", null, true);
            echo "</a>
            </td>
            <td>";
            // line 94
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "keytype", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 95
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "primaryPublicUserId", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 96
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "email", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 97
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "preferredPhone", array()), "html", null, true);
            echo "</td>
            <td>
                ";
            // line 99
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getUserRolesBySite", array(0 => $context["user"], 1 => (isset($context["sitename"]) ? $context["sitename"] : null), 2 => true), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                // line 100
                echo "                    ";
                if (($this->getAttribute($context["role"], "name", array()) != "ROLE_USER")) {
                    // line 101
                    echo "                        <p>\"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["role"], "alias", array()), "html", null, true);
                    echo "\"</p>
                    ";
                }
                // line 103
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 104
            echo "            </td>
            <td>";
            // line 105
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["user"], "lastLogin", array()), "Y-m-d H:i"), "html", null, true);
            echo "</td>


            <td class=\"rowlink-skip\">

                <div class=\"btn-group\">
                    <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">
                        Action <span class=\"caret\"></span>
                    </button>

                    <ul class=\"dropdown-menu dropdown-menu-right\">

                        <li>
                            <a href=\"";
            // line 118
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($context["user"], "id", array()))), "html", null, true);
            echo "\">View User Details</a>
                        </li>

                        <li class=\"divider\"></li>

                        ";
            // line 124
            echo "                            ";
            // line 125
            echo "                        ";
            // line 126
            echo "
                        <li>
                            <a general-data-confirm=\"Are you sure you would like to stop ";
            // line 128
            echo twig_escape_filter($this->env, $context["user"], "html", null, true);
            echo " from being able to access ";
            echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
            echo "?\"
                               href=\"";
            // line 129
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_authorization_remove"), array("userId" => $this->getAttribute($context["user"], "id", array()))), "html", null, true);
            echo "\">Revoke Access Authorization
                            </a>
                        </li>

                    </ul>

                </div>


            </td>

            </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 142
        echo "        </tbody>
    </table>

    <div class=\"navigation\">
        ";
        // line 146
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->render($this->env, (isset($context["users"]) ? $context["users"] : null));
        echo "
    </div>

";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:authorized_users.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  275 => 146,  269 => 142,  250 => 129,  244 => 128,  240 => 126,  238 => 125,  236 => 124,  228 => 118,  212 => 105,  209 => 104,  203 => 103,  197 => 101,  194 => 100,  190 => 99,  185 => 97,  181 => 96,  177 => 95,  173 => 94,  166 => 92,  160 => 89,  156 => 87,  152 => 86,  144 => 81,  140 => 80,  136 => 79,  132 => 78,  128 => 77,  124 => 76,  119 => 75,  102 => 60,  98 => 59,  93 => 57,  88 => 54,  86 => 53,  84 => 52,  79 => 49,  76 => 48,  74 => 47,  71 => 46,  68 => 45,  61 => 40,  58 => 39,  54 => 35,  51 => 32,  49 => 31,  47 => 30,  45 => 29,  43 => 28,  41 => 27,  39 => 26,  37 => 25,  35 => 24,  33 => 23,  31 => 22,  29 => 21,  27 => 20,  25 => 19,  19 => 35,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:authorized_users.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/authorized_users.html.twig");
    }
}

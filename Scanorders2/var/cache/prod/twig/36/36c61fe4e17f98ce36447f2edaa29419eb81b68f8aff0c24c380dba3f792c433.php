<?php

/* OlegUserdirectoryBundle:AccessRequest:access_request_list_content.html.twig */
class __TwigTemplate_937a5b61c02e0d91936d43510b3573646620e59b1c6c5739dbb63fab77cea560 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 16
        echo "

<h3 class=\"text-info\">Access Requests for the ";
        // line 18
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo " site</h3>

<table class=\"table table-hover table-condensed text-left\">
    <thead>
        <tr>
            <th>";
        // line 23
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Request ID", "accreq.id");
        echo "</th>
            <th>";
        // line 24
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Request Date", "accreq.createdate");
        echo "</th>
            <th>";
        // line 25
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Request Status", "accreq.status");
        echo "</th>
            <th>";
        // line 26
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Full Name", "infos.displayName");
        echo "</th>
            <th>";
        // line 27
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "User ID Type", "keytype.name");
        echo "</th>
            <th>";
        // line 28
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "User ID", "user.primaryPublicUserId");
        echo "</th>
            <th>";
        // line 29
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Email", "infos.email");
        echo "</th>
            <th>";
        // line 30
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Phone Number", "infos.preferredPhone");
        echo "</th>
            <th>";
        // line 31
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Role(s)", "user.roles");
        echo "</th>
            <th>";
        // line 32
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Last Login", "user.lastLogin");
        echo "</th>
            <th>";
        // line 33
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Status Updated On", "accreq.updatedate");
        echo "</th>
            <th>";
        // line 34
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->sortable($this->env, (isset($context["entities"]) ? $context["entities"] : null), "Status Updated By", "updatedbyinfos.displayName");
        echo "</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody data-link=\"row\" class=\"rowlink\">
    ";
        // line 39
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["entities"]) ? $context["entities"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["entity"]) {
            // line 40
            echo "
        ";
            // line 41
            if ((twig_lower_filter($this->env, $this->getAttribute($context["entity"], "getStatusStr", array())) == "active")) {
                // line 42
                echo "            <tr class=\"order-urgent-status\">
        ";
            } elseif ((twig_lower_filter($this->env, $this->getAttribute(            // line 43
$context["entity"], "getStatusStr", array())) == "declined")) {
                // line 44
                echo "            <tr class=\"order-neutral-status\">
        ";
            } elseif ((twig_lower_filter($this->env, $this->getAttribute(            // line 45
$context["entity"], "getStatusStr", array())) == "approved")) {
                // line 46
                echo "            <tr>
        ";
            } elseif ((twig_lower_filter($this->env, $this->getAttribute(            // line 47
$context["entity"], "getStatusStr", array())) == "uncompleted")) {
                // line 48
                echo "            <tr class=\"order-someattention-status\">
        ";
            } else {
                // line 50
                echo "            <tr>
        ";
            }
            // line 52
            echo "
        ";
            // line 54
            echo "
            ";
            // line 56
            echo "            <td style=\"display: none\">
                <a href=\"";
            // line 57
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_accessrequest_management"), array("id" => $this->getAttribute($context["entity"], "id", array()))), "html", null, true);
            echo "\" target=\"_blank\">Review and approve access request</a>
            </td>

            <td class=\"rowlink-skip\">
                <a href=\"";
            // line 61
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "id", array()))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["entity"], "id", array()), "html", null, true);
            echo "</a>
            </td>
            <td>";
            // line 63
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["entity"], "createdate", array()), "Y-m-d h:i A T"), "html", null, true);
            echo "</td>
            <td>";
            // line 64
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, $this->getAttribute($context["entity"], "getStatusStr", array())), "html", null, true);
            echo "</td>
            <td>";
            // line 65
            echo twig_escape_filter($this->env, $this->getAttribute($context["entity"], "user", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 66
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "keytype", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 67
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "primaryPublicUserId", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 68
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "email", array()), "html", null, true);
            echo "</td>
            <td>";
            // line 69
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "preferredPhone", array()), "html", null, true);
            echo "</td>
            <td>
                ";
            // line 71
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getUserRolesBySite", array(0 => $this->getAttribute($context["entity"], "user", array()), 1 => (isset($context["sitename"]) ? $context["sitename"] : null), 2 => true), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                // line 72
                echo "                    ";
                if (($this->getAttribute($context["role"], "name", array()) != "ROLE_USER")) {
                    // line 73
                    echo "                        \"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["role"], "alias", array()), "html", null, true);
                    echo "\"
                    ";
                }
                // line 75
                echo "                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 76
            echo "            </td>
            <td>";
            // line 77
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "lastLogin", array()), "Y-m-d h:i A T"), "html", null, true);
            echo "</td>

            <td>
                ";
            // line 80
            if ($this->getAttribute($context["entity"], "updatedate", array())) {
                // line 81
                echo "                    ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["entity"], "updatedate", array()), "Y-m-d h:i A T"), "html", null, true);
                echo "
                ";
            }
            // line 83
            echo "            </td>

            <td class=\"rowlink-skip\">
                ";
            // line 86
            if ($this->getAttribute($context["entity"], "updatedby", array())) {
                // line 87
                echo "                    <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute($context["entity"], "updatedby", array()), "id", array()))), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["entity"], "updatedby", array()), "html", null, true);
                echo "</a>
                ";
            }
            // line 89
            echo "            </td>


            <td class=\"rowlink-skip\">

                <div class=\"btn-group\">
                    <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">
                      Action <span class=\"caret\"></span>
                    </button>

                    <ul class=\"dropdown-menu dropdown-menu-right\">

                        <li><a href=\"";
            // line 101
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitenameshowuser"]) ? $context["sitenameshowuser"] : null) . "_showuser"), array("id" => $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "id", array()))), "html", null, true);
            echo "\">View User Details</a></li>

                        <li class=\"divider\"></li>

                        ";
            // line 106
            echo "                            ";
            // line 107
            echo "                                   ";
            // line 108
            echo "                            ";
            // line 109
            echo "                        ";
            // line 110
            echo "
                        ";
            // line 112
            echo "                            ";
            // line 113
            echo "                                   ";
            // line 114
            echo "                            ";
            // line 115
            echo "                        ";
            // line 116
            echo "
                        ";
            // line 118
            echo "                            ";
            // line 119
            echo "                                   ";
            // line 120
            echo "                            ";
            // line 121
            echo "                        ";
            // line 122
            echo "

                        <li>
                            <a href=\"";
            // line 125
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_accessrequest_management"), array("id" => $this->getAttribute($context["entity"], "id", array()))), "html", null, true);
            echo "\">Review and approve access request</a>
                        </li>

                        ";
            // line 128
            if ((twig_lower_filter($this->env, $this->getAttribute($context["entity"], "getStatusStr", array())) != "declined")) {
                // line 129
                echo "                            <li>
                                <a
                                    general-data-confirm=\"Are you sure you would like to stop ";
                // line 131
                echo twig_escape_filter($this->env, $this->getAttribute($context["entity"], "user", array()), "html", null, true);
                echo " from being able to access ";
                echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
                echo "?\"
                                    href=\"";
                // line 132
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_accessrequest_remove"), array("userId" => $this->getAttribute($this->getAttribute($context["entity"], "user", array()), "id", array()))), "html", null, true);
                echo "\">Deny access request
                                </a>
                            </li>
                        ";
            }
            // line 136
            echo "

                    </ul>

                </div>


            </td>

        </tr>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['entity'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 147
        echo "    </tbody>
</table>

<div class=\"navigation\">
    ";
        // line 151
        echo $this->env->getExtension('Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension')->render($this->env, (isset($context["entities"]) ? $context["entities"] : null));
        echo "
</div>
";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:access_request_list_content.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  321 => 151,  315 => 147,  299 => 136,  292 => 132,  286 => 131,  282 => 129,  280 => 128,  274 => 125,  269 => 122,  267 => 121,  265 => 120,  263 => 119,  261 => 118,  258 => 116,  256 => 115,  254 => 114,  252 => 113,  250 => 112,  247 => 110,  245 => 109,  243 => 108,  241 => 107,  239 => 106,  232 => 101,  218 => 89,  210 => 87,  208 => 86,  203 => 83,  197 => 81,  195 => 80,  189 => 77,  186 => 76,  180 => 75,  174 => 73,  171 => 72,  167 => 71,  162 => 69,  158 => 68,  154 => 67,  150 => 66,  146 => 65,  142 => 64,  138 => 63,  131 => 61,  124 => 57,  121 => 56,  118 => 54,  115 => 52,  111 => 50,  107 => 48,  105 => 47,  102 => 46,  100 => 45,  97 => 44,  95 => 43,  92 => 42,  90 => 41,  87 => 40,  83 => 39,  75 => 34,  71 => 33,  67 => 32,  63 => 31,  59 => 30,  55 => 29,  51 => 28,  47 => 27,  43 => 26,  39 => 25,  35 => 24,  31 => 23,  23 => 18,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:access_request_list_content.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/access_request_list_content.html.twig");
    }
}

<?php

/* OlegUserdirectoryBundle::Security/login_content.html.twig */
class __TwigTemplate_ef0ac7ca6adf1e67e7f76ac759d0df86be6fdea4cf673d10c71f26cbfe586126 extends Twig_Template
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
";
        // line 17
        $context["usermacros"] = $this->loadTemplate("OlegUserdirectoryBundle::Default/usermacros.html.twig", "OlegUserdirectoryBundle::Security/login_content.html.twig", 17);
        // line 18
        echo $context["usermacros"]->getnonLiveSiteWarning();
        echo "
";
        // line 19
        echo $context["usermacros"]->getbrowserCheck();
        echo "

<h2 class=\"alert alert-info well-lg safary-fix\" align=\"center\">";
        // line 21
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "</h2>

<br>

";
        // line 25
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "flashbag", array()), "get", array(0 => "notice"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 26
            echo "    <div class=\"flash-notice\" align=\"center\">
        ";
            // line 27
            echo $context["flashMessage"];
            echo "
    </div>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 30
        echo "
";
        // line 31
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "flashbag", array()), "get", array(0 => "pnotify"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 32
            echo "    <input type=\"hidden\" id=\"pnotify-notice\" value=\"";
            echo $context["flashMessage"];
            echo "\" />
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "flashbag", array()), "get", array(0 => "pnotify-error"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 35
            echo "    <input type=\"hidden\" id=\"pnotify-notice\" class=\"pnotify-notice-error\" value=\"";
            echo $context["flashMessage"];
            echo "\" />
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 37
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session", array()), "flashbag", array()), "get", array(0 => "pnotify-success"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 38
            echo "    <input type=\"hidden\" id=\"pnotify-notice\" class=\"pnotify-notice-success\" value=\"";
            echo $context["flashMessage"];
            echo "\" />
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 40
        echo "

";
        // line 42
        if ( !array_key_exists("error", $context)) {
            // line 43
            echo "    ";
            $context["error"] = "";
        }
        // line 45
        echo "
";
        // line 46
        if ( !array_key_exists("last_username", $context)) {
            // line 47
            echo "    ";
            $context["last_username"] = "";
        }
        // line 49
        echo "
";
        // line 50
        if ((isset($context["error"]) ? $context["error"] : null)) {
            echo " 
    <p class=\"alert alert-danger\" align=\"middle\">
        ";
            // line 53
            echo "        There was an error with your User Name/Password combination. Please try again.
    </p>
";
        }
        // line 56
        echo "

<input type=\"hidden\" name=\"display_height\" id=\"display_height\" value=\"\" />
<input type=\"hidden\" name=\"display_width\" id=\"display_width\" value=\"\" />


<form id=\"login-form\" class=\"form-signin\" action=\"";
        // line 62
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_login_check"));
        echo "\" method=\"post\">

    <div class=\"row\">
    <div class=\"col-xs-12\">

        <p>
            ";
        // line 69
        echo "            <select id=\"usernametypeid_show\" class=\"combobox limit-font-size\">
                ";
        // line 70
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["usernametypes"]) ? $context["usernametypes"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["usernametype"]) {
            // line 71
            echo "                    ";
            if ((array_key_exists("user_type", $context) && ($this->getAttribute($context["usernametype"], "name", array()) == (isset($context["user_type"]) ? $context["user_type"] : null)))) {
                // line 72
                echo "                        ";
                $context["selected"] = "selected=\"selected\"";
                // line 73
                echo "                    ";
            } else {
                // line 74
                echo "                        ";
                $context["selected"] = "";
                // line 75
                echo "                    ";
            }
            // line 76
            echo "                    <option value=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["usernametype"], "abbreviation", array()), "html", null, true);
            echo "\" ";
            echo twig_escape_filter($this->env, (isset($context["selected"]) ? $context["selected"] : null), "html", null, true);
            echo ">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["usernametype"], "name", array()), "html", null, true);
            echo "</option>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['usernametype'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 78
        echo "            </select>

            ";
        // line 81
        echo "            <input type=\"text\" id=\"username\" name=\"_username\" value=\"";
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : null), "html", null, true);
        echo "\" class=\"form-control limit-font-size\" placeholder=\"Username Hidden\" style=\"display:none;\">
            <input type=\"text\" id=\"display-username\" value=\"";
        // line 82
        echo twig_escape_filter($this->env, (isset($context["last_username"]) ? $context["last_username"] : null), "html", null, true);
        echo "\" class=\"form-control limit-font-size\" placeholder=\"Username\" style=\"";
        echo twig_escape_filter($this->env, (isset($context["inputStyle"]) ? $context["inputStyle"] : null), "html", null, true);
        echo "\">

            <input type=\"password\" id=\"password\" name=\"_password\" class=\"form-control limit-font-size\" placeholder=\"Password\" style=\"";
        // line 84
        echo twig_escape_filter($this->env, (isset($context["inputStyle"]) ? $context["inputStyle"] : null), "html", null, true);
        echo "\">

            ";
        // line 87
        echo "            ";
        // line 88
        echo "
        </p>

    </div>
    </div>

    <button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\" >Log In</button>

</form>


<div class=\"text-center\">

    <br><br>
    <p>
        ";
        // line 103
        $context["loginInstruction"] = $this->getAttribute((isset($context["user_security_utility"]) ? $context["user_security_utility"] : null), "getSiteSettingParameter", array(0 => "loginInstruction"), "method");
        // line 104
        echo "        ";
        if ((isset($context["loginInstruction"]) ? $context["loginInstruction"] : null)) {
            // line 105
            echo "            ";
            echo (isset($context["loginInstruction"]) ? $context["loginInstruction"] : null);
            echo "
        ";
        } else {
            // line 107
            echo "            Please use your
            <a href=\"http://weill.cornell.edu/its/identity-security/identity/cwid/\">CWID</a>
            ";
            // line 109
            if (((isset($context["title"]) ? $context["title"] : null) == "Scan Orders")) {
                // line 110
                echo "                or your
                <a href=\"http://c.med.cornell.edu/\">Aperio eSlide Manager</a> account
            ";
            }
            // line 113
            echo "            to log in.
        ";
        }
        // line 115
        echo "    </p>

    ";
        // line 118
        echo "        ";
        // line 119
        echo "            ";
        // line 120
        echo "        ";
        // line 121
        echo "    ";
        // line 122
        echo "    ";
        // line 123
        echo "        <p>
            <a href=\"";
        // line 124
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_accountrequest_new"));
        echo "\">Request a new account</a> if you can't log in.
        </p>
    ";
        // line 127
        echo "
</div>


<script language=\"Javascript\">
    <!--
        //console.log(\"width=\"+screen.width+\"x\"+screen.height);
        document.getElementById(\"display_height\").value = screen.height;
        document.getElementById(\"display_width\").value = screen.width;
    //-->
</script>";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Security/login_content.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  270 => 127,  265 => 124,  262 => 123,  260 => 122,  258 => 121,  256 => 120,  254 => 119,  252 => 118,  248 => 115,  244 => 113,  239 => 110,  237 => 109,  233 => 107,  227 => 105,  224 => 104,  222 => 103,  205 => 88,  203 => 87,  198 => 84,  191 => 82,  186 => 81,  182 => 78,  169 => 76,  166 => 75,  163 => 74,  160 => 73,  157 => 72,  154 => 71,  150 => 70,  147 => 69,  138 => 62,  130 => 56,  125 => 53,  120 => 50,  117 => 49,  113 => 47,  111 => 46,  108 => 45,  104 => 43,  102 => 42,  98 => 40,  89 => 38,  85 => 37,  76 => 35,  72 => 34,  63 => 32,  59 => 31,  56 => 30,  47 => 27,  44 => 26,  40 => 25,  33 => 21,  28 => 19,  24 => 18,  22 => 17,  19 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Security/login_content.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Security/login_content.html.twig");
    }
}

<?php

/* OlegUserdirectoryBundle:AccessRequest:add_authorized_user_NOTUSED.html.twig */
class __TwigTemplate_07fccf00291f15792e09da1fc76bf3aae7e303b05f63ae531b1ca3c518360c30 extends Twig_Template
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
        // line 30
        return $this->loadTemplate((isset($context["extendStr"]) ? $context["extendStr"] : null), "OlegUserdirectoryBundle:AccessRequest:add_authorized_user_NOTUSED.html.twig", 30);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 18
        if (((isset($context["sitename"]) ? $context["sitename"] : null) == "employees")) {
            // line 19
            $context["extendStr"] = "OlegUserdirectoryBundle::Default/base.html.twig";
        } elseif ((        // line 20
(isset($context["sitename"]) ? $context["sitename"] : null) == "fellapp")) {
            // line 21
            $context["extendStr"] = "OlegFellAppBundle::Default/base.html.twig";
        } elseif ((        // line 22
(isset($context["sitename"]) ? $context["sitename"] : null) == "deidentifier")) {
            // line 23
            $context["extendStr"] = "OlegDeidentifierBundle::Default/base.html.twig";
        } elseif ((        // line 24
(isset($context["sitename"]) ? $context["sitename"] : null) == "scan")) {
            // line 25
            $context["extendStr"] = "OlegOrderformBundle::Default/base.html.twig";
        } elseif ((        // line 26
(isset($context["sitename"]) ? $context["sitename"] : null) == "vacreq")) {
            // line 27
            $context["extendStr"] = "OlegVacReqBundle::Default/base.html.twig";
        }
        // line 30
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 34
    public function block_title($context, array $blocks = array())
    {
        // line 35
        echo "    Add a New Authorized User for ";
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo "
";
    }

    // line 40
    public function block_content($context, array $blocks = array())
    {
        // line 41
        echo "
    ";
        // line 42
        $context["formmacros"] = $this->loadTemplate("OlegOrderformBundle::Default/formmacros.html.twig", "OlegUserdirectoryBundle:AccessRequest:add_authorized_user_NOTUSED.html.twig", 42);
        // line 43
        echo "
    <h3 class=\"text-info\">
        Add a New Authorized User for ";
        // line 45
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo "
    </h3>

    <br>


    <hr>


    <form id=\"add_authorized_user_form\" action=\"";
        // line 54
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_add_authorized_user_submit"));
        echo "\" method=\"POST\">


        ";
        // line 58
        echo "        ";
        // line 59
        echo "

        <hr>

        <br>

        <div class=\"row\">

            <div class=\"col-xs-6\" align=\"right\">
                <button name=\"accessrequest-approve\" type='submit' class=\"btn btn-info btn-sm\">Update</button>
            </div>

            <div class=\"col-xs-6\" align=\"left\">
                <a class=\"btn btn-danger btn-sm\" href=\"";
        // line 72
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath(((isset($context["sitename"]) ? $context["sitename"] : null) . "_authorized_users"));
        echo "\">Cancel</a>
            </div>

        </div>

    </form>



";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:add_authorized_user_NOTUSED.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  107 => 72,  92 => 59,  90 => 58,  84 => 54,  72 => 45,  68 => 43,  66 => 42,  63 => 41,  60 => 40,  53 => 35,  50 => 34,  46 => 30,  43 => 27,  41 => 26,  39 => 25,  37 => 24,  35 => 23,  33 => 22,  31 => 21,  29 => 20,  27 => 19,  25 => 18,  19 => 30,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:add_authorized_user_NOTUSED.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/add_authorized_user_NOTUSED.html.twig");
    }
}

<?php

/* OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig */
class __TwigTemplate_216bfc27e131fe222186187d0ab923cee1f6d74ac5e726b09a6c148080bf9e87 extends Twig_Template
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
        // line 33
        return $this->loadTemplate((isset($context["extendStr"]) ? $context["extendStr"] : null), "OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig", 33);
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
(isset($context["sitename"]) ? $context["sitename"] : null) == "vacreq")) {
            // line 26
            $context["extendStr"] = "OlegVacReqBundle::Default/base.html.twig";
        } elseif ((        // line 27
(isset($context["sitename"]) ? $context["sitename"] : null) == "calllog")) {
            // line 28
            $context["extendStr"] = "OlegCallLogBundle::Default/base.html.twig";
        } elseif ((        // line 29
(isset($context["sitename"]) ? $context["sitename"] : null) == "translationalresearch")) {
            // line 30
            $context["extendStr"] = "OlegTranslationalResearchBundle::Default/base.html.twig";
        }
        // line 33
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 35
    public function block_title($context, array $blocks = array())
    {
        // line 36
        echo "    Access Requests for the ";
        echo twig_escape_filter($this->env, (isset($context["sitenamefull"]) ? $context["sitenamefull"] : null), "html", null, true);
        echo " site
";
    }

    // line 39
    public function block_content($context, array $blocks = array())
    {
        // line 40
        echo "
    ";
        // line 41
        $this->loadTemplate("OlegUserdirectoryBundle::AccessRequest/access_request_list_content.html.twig", "OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig", 41)->display($context);
        // line 42
        echo "
";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 42,  70 => 41,  67 => 40,  64 => 39,  57 => 36,  54 => 35,  50 => 33,  47 => 30,  45 => 29,  43 => 28,  41 => 27,  39 => 26,  37 => 25,  35 => 24,  33 => 23,  31 => 22,  29 => 21,  27 => 20,  25 => 19,  19 => 33,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/AccessRequest/access_request_list.html.twig");
    }
}

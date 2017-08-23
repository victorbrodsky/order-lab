<?php

/* @Twig/Exception/error.html.twig */
class __TwigTemplate_5c64d9c6e74b22fa0f35b65849aaf5946d3f2b33ea5d24b51a42bc4de1260a7c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 18
        $this->parent = $this->loadTemplate("OlegOrderformBundle::Default/base.html.twig", "@Twig/Exception/error.html.twig", 18);
        $this->blocks = array(
            'maincss' => array($this, 'block_maincss'),
            'header' => array($this, 'block_header'),
            'mainjs' => array($this, 'block_mainjs'),
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "OlegOrderformBundle::Default/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 20
    public function block_maincss($context, array $blocks = array())
    {
    }

    // line 21
    public function block_header($context, array $blocks = array())
    {
    }

    // line 22
    public function block_mainjs($context, array $blocks = array())
    {
    }

    // line 24
    public function block_title($context, array $blocks = array())
    {
        // line 25
        echo "    Scan Order - ";
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : null), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : null), "html", null, true);
        echo "
";
    }

    // line 28
    public function block_content($context, array $blocks = array())
    {
        // line 29
        echo "
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"span12\">
                <div class=\"hero-unit center\">

                    ";
        // line 36
        echo "
                    <h1>The server returned a \"<font face=\"Tahoma\" color=\"red\">";
        // line 37
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : null), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : null), "html", null, true);
        echo "</font>\".</h1>

                    <br />

                    <p>
                    <h3>
                        <small>
                            Please verify that the address in your browser's URL bar is correct or press the <a class=\"btn btn-info\" href=\"javascript:;\" onclick=\"history.go(-1);\">BACK</a> button
                            or the <a class=\"btn btn-info\" href=\"";
        // line 45
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("main_common_home");
        echo "\">HOME</a> button.
                        </small>
                    </h3>
                    </p>

                    <br />

                    <p>
                    <h3>
                        <small>
                            If you believe there may be an issue, please contact the system administrator at <a href=\"mailto:";
        // line 55
        echo twig_escape_filter($this->env, (isset($context["default_system_email"]) ? $context["default_system_email"] : null), "html", null, true);
        echo "?Subject=";
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : null), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : null), "html", null, true);
        echo "\" target=\"_top\">";
        echo twig_escape_filter($this->env, (isset($context["default_system_email"]) ? $context["default_system_email"] : null), "html", null, true);
        echo "</a>.
                        </small>
                    </h3>
                    </p>


                </div>
            </div>
        </div>
    </div>

";
    }

    public function getTemplateName()
    {
        return "@Twig/Exception/error.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  99 => 55,  86 => 45,  73 => 37,  70 => 36,  62 => 29,  59 => 28,  50 => 25,  47 => 24,  42 => 22,  37 => 21,  32 => 20,  11 => 18,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "@Twig/Exception/error.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\app\\Resources\\TwigBundle\\views\\Exception\\error.html.twig");
    }
}

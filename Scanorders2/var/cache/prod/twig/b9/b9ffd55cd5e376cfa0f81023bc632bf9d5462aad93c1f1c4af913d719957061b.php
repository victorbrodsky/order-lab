<?php

/* OlegUserdirectoryBundle::Security/idle_timeout.html.twig */
class __TwigTemplate_cd0cdbdb938131284b85a4c81dcc586fa59fca14c5537492085d649108bce46c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'idletimeout' => array($this, 'block_idletimeout'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 16
        echo "

";
        // line 18
        $this->displayBlock('idletimeout', $context, $blocks);
    }

    public function block_idletimeout($context, array $blocks = array())
    {
        // line 19
        echo "
<!-- Modal -->
<div class=\"modal fade\" id=\"idle-timeout\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                ";
        // line 26
        echo "                <h4 class=\"modal-title\" id=\"myModalLabel\">Your session is about to expire!</h4>
            </div>
            <div class=\"modal-body\">

                You will be logged out in <span id=\"dialog-countdown\" style=\"font-weight:bold\"></span> seconds.

            </div>
            <div class=\"modal-footer\">
                <button id=\"idle-timeout-keepworking\" type=\"button\" class=\"btn btn-default idle-timeout_modal_close\" data-dismiss=\"modal\" onclick=\"keepWorking()\">Keep me logged in</button>
                <button id=\"idle-timeout-logoff\" type=\"button\" class=\"btn btn-default idle-timeout_modal_close\" data-dismiss=\"modal\" onclick=\"logoff()\">Log out</button>
            </div>
        </div>
    </div>
</div>


";
    }

    public function getTemplateName()
    {
        return "OlegUserdirectoryBundle::Security/idle_timeout.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  38 => 26,  30 => 19,  24 => 18,  20 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "OlegUserdirectoryBundle::Security/idle_timeout.html.twig", "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\\Oleg\\UserdirectoryBundle/Resources/views/Security/idle_timeout.html.twig");
    }
}

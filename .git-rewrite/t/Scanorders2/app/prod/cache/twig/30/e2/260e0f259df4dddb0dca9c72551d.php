<?php

/* OlegOrderformBundle::Default/base.html.twig */
class __TwigTemplate_30e2260e0f259df4dddb0dca9c72551d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'header' => array($this, 'block_header'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>

<head>
    
    ";
        // line 6
        if ((!$this->env->getExtension('security')->isGranted("ROLE_USER"))) {
            echo "                        
        <meta http-equiv=\"refresh\" content=\"0; URL=";
            // line 7
            echo $this->env->getExtension('routing')->getPath("login");
            echo "\">
    ";
        }
        // line 9
        echo "        
    ";
        // line 10
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "bdea819_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819_0") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819_part_1_bootstrap-responsive.min_1.css");
            // line 15
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
            // asset "bdea819_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819_1") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819_part_1_bootstrap.min_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
            // asset "bdea819_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819_2") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819_part_1_bootstrap3custom.min_3.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
            // asset "bdea819_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819_3") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819_form_2.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
            // asset "bdea819_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819_4") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819_part_3_datepicker_1.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
        } else {
            // asset "bdea819"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_bdea819") : $this->env->getExtension('assets')->getAssetUrl("css/bdea819.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
        
    ";
        }
        unset($context["asset_url"]);
        // line 17
        echo "        
        
    ";
        // line 20
        echo "    ";
        echo "    
    ";
        // line 21
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "e784d5d_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_e784d5d_0") : $this->env->getExtension('assets')->getAssetUrl("css/e784d5d_part_1_bootstrap-combobox_1.css");
            // line 24
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        } else {
            // asset "e784d5d"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_e784d5d") : $this->env->getExtension('assets')->getAssetUrl("css/e784d5d.css");
            echo "        <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\" />
    ";
        }
        unset($context["asset_url"]);
        // line 25
        echo "         
       
   ";
        // line 27
        $this->displayBlock('head', $context, $blocks);
        // line 31
        echo "        
</head> 
             
<body>

    <div class=\"container page-content\"> 

    <div class=\"header\">
        
        ";
        // line 40
        $this->displayBlock('header', $context, $blocks);
        // line 44
        echo "                    
    </div>   

    <div class=\"order-content\">
        
        ";
        // line 49
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "session"), "flashbag"), "get", array(0 => "notice"), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["flashMessage"]) {
            // line 50
            echo "            <div class=\"flash-notice\">
                ";
            // line 51
            echo twig_escape_filter($this->env, (isset($context["flashMessage"]) ? $context["flashMessage"] : null), "html", null, true);
            echo "
            </div>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['flashMessage'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 54
        echo "        
        ";
        // line 55
        $this->displayBlock('content', $context, $blocks);
        // line 56
        echo "        
    </div>

    <div class=\"footer\">
        ";
        // line 60
        $this->displayBlock('footer', $context, $blocks);
        // line 63
        echo "    </div>                   
 
    ";
        // line 65
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "93597e6_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6_0") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6_part_1_jquery_1.js");
            // line 71
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "93597e6_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6_1") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6_part_2_bootstrap.min_1.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "93597e6_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6_2") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6_part_3_addForm_1.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "93597e6_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6_3") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6_part_3_form_2.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
            // asset "93597e6_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6_4") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6_part_4_bootstrap-datepicker_1.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "93597e6"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_93597e6") : $this->env->getExtension('assets')->getAssetUrl("js/93597e6.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 72
        echo "     

    ";
        // line 74
        echo "        
    ";
        // line 75
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "debf739_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_debf739_0") : $this->env->getExtension('assets')->getAssetUrl("js/debf739_part_1_bootstrap-combobox_1.js");
            // line 78
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "debf739"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_debf739") : $this->env->getExtension('assets')->getAssetUrl("js/debf739.js");
            echo "        <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : null), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 79
        echo "         
    ";
        // line 80
        echo "             

    </div>

</body>
         
</html>
";
    }

    // line 27
    public function block_head($context, array $blocks = array())
    {
        echo "                                                                       
        <title>";
        // line 28
        $this->displayBlock('title', $context, $blocks);
        echo " - Scan Order</title>   
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    ";
    }

    public function block_title($context, array $blocks = array())
    {
    }

    // line 40
    public function block_header($context, array $blocks = array())
    {
        // line 42
        echo "            ";
        $this->env->loadTemplate("OlegOrderformBundle:Default:navbar.html.twig")->display($context);
        // line 43
        echo "        ";
    }

    // line 55
    public function block_content($context, array $blocks = array())
    {
    }

    // line 60
    public function block_footer($context, array $blocks = array())
    {
        // line 61
        echo "            &copy; Copyright 2013 by <a href=\"https://webmail.med.cornell.edu/\">Pathology - Weill Cornell Medical College</a>.
        ";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle::Default/base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  291 => 61,  288 => 60,  279 => 43,  276 => 42,  273 => 40,  262 => 28,  257 => 27,  246 => 80,  243 => 79,  225 => 75,  222 => 74,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 54,  150 => 51,  134 => 40,  81 => 25,  63 => 25,  96 => 38,  77 => 27,  58 => 19,  52 => 18,  59 => 6,  53 => 18,  23 => 1,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 116,  368 => 112,  365 => 111,  362 => 110,  360 => 109,  355 => 106,  341 => 105,  337 => 103,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 89,  283 => 55,  278 => 86,  268 => 85,  264 => 84,  258 => 81,  252 => 80,  247 => 78,  241 => 77,  235 => 74,  229 => 78,  224 => 71,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 49,  140 => 55,  132 => 51,  128 => 49,  119 => 42,  111 => 37,  107 => 36,  71 => 30,  177 => 65,  165 => 64,  160 => 61,  139 => 50,  135 => 47,  126 => 45,  114 => 42,  84 => 37,  70 => 25,  67 => 23,  61 => 19,  47 => 15,  38 => 10,  94 => 22,  89 => 36,  85 => 25,  79 => 35,  75 => 22,  68 => 14,  56 => 9,  50 => 10,  29 => 5,  87 => 20,  72 => 16,  55 => 15,  21 => 2,  26 => 6,  98 => 31,  93 => 28,  88 => 6,  78 => 24,  46 => 13,  27 => 4,  40 => 9,  44 => 15,  35 => 7,  31 => 6,  43 => 10,  41 => 7,  28 => 5,  201 => 92,  196 => 90,  183 => 70,  171 => 61,  166 => 71,  163 => 70,  158 => 67,  156 => 58,  151 => 57,  142 => 59,  138 => 57,  136 => 44,  123 => 31,  121 => 27,  117 => 25,  115 => 43,  105 => 40,  101 => 32,  91 => 17,  69 => 11,  66 => 20,  62 => 21,  49 => 17,  24 => 1,  32 => 4,  25 => 3,  22 => 2,  19 => 1,  209 => 82,  203 => 78,  199 => 67,  193 => 73,  189 => 71,  187 => 84,  182 => 66,  176 => 65,  173 => 74,  168 => 66,  164 => 56,  162 => 55,  154 => 54,  149 => 51,  147 => 50,  144 => 53,  141 => 51,  133 => 55,  130 => 41,  125 => 44,  122 => 43,  116 => 36,  112 => 42,  109 => 41,  106 => 33,  103 => 24,  99 => 21,  95 => 20,  92 => 33,  86 => 28,  82 => 31,  80 => 19,  73 => 19,  64 => 22,  60 => 13,  57 => 17,  54 => 16,  51 => 14,  48 => 16,  45 => 16,  42 => 7,  39 => 10,  36 => 5,  33 => 7,  30 => 7,);
    }
}

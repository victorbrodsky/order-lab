<?php

/* OlegOrderformBundle:Default:action.html.twig */
class __TwigTemplate_166c5bedd3c18d0fd5dc361df2dc3ada extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'action' => array($this, 'block_action'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "

";
        // line 3
        $this->displayBlock('action', $context, $blocks);
    }

    public function block_action($context, array $blocks = array())
    {
        // line 4
        echo "    
    <div class=\"btn-group\">
        <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">
          Action <span class=\"caret\"></span>
        </button>
        <ul class=\"dropdown-menu\">
            
            <li><a href=\"";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_show", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"))), "html", null, true);
        echo "\">Show</a></li>

            <li class=\"divider\"></li>
            
            ";
        // line 15
        if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") != "cancel")) {
            // line 16
            echo "                <li><a onclick=\"return confirm('Are you sure?');\" class=\"btn-danger\" 
                       href=\"";
            // line 17
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_status", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "status" => "cancel")), "html", null, true);
            echo "\">Cancel</a>
                </li>                  
            ";
        }
        // line 19
        echo "    
            
            ";
        // line 21
        if ($this->env->getExtension('security')->isGranted("ROLE_ADMIN")) {
            echo " 
                <li>
                    <a class=\"btn-warning\" 
                       href=\"";
            // line 24
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_edit", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"))), "html", null, true);
            echo "\">Edit</a> 
                </li>             
                
                ";
            // line 27
            if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") != "active")) {
                // line 28
                echo "                    <li><a onclick=\"return confirm('Are you sure?');\" 
                           href=\"";
                // line 29
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_status", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "status" => "active")), "html", null, true);
                echo "\">Active</a>
                    </li>                  
                ";
            }
            // line 32
            echo "                
                ";
            // line 33
            if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") != "completed")) {
                // line 34
                echo "                    <li><a onclick=\"return confirm('Are you sure?');\" 
                           href=\"";
                // line 35
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_status", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "status" => "completed")), "html", null, true);
                echo "\">Completed</a>
                    </li>                  
                ";
            }
            // line 38
            echo "                
                ";
            // line 39
            if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") != "uncompleted")) {
                // line 40
                echo "                    <li><a onclick=\"return confirm('Are you sure?');\" 
                           href=\"";
                // line 41
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_status", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "status" => "uncompleted")), "html", null, true);
                echo "\">Un-Completed</a>
                    </li>                  
                ";
            }
            // line 43
            echo " 
                    
                ";
            // line 51
            echo "          
           ";
        }
        // line 53
        echo "          
        </ul>
      </div>


";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:Default:action.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  20 => 1,  90 => 32,  76 => 21,  291 => 61,  288 => 60,  279 => 43,  276 => 42,  273 => 40,  262 => 28,  257 => 27,  243 => 79,  225 => 75,  222 => 74,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 54,  150 => 51,  134 => 40,  81 => 25,  63 => 25,  77 => 33,  58 => 19,  59 => 6,  53 => 18,  23 => 1,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 116,  368 => 112,  365 => 111,  362 => 110,  360 => 109,  355 => 106,  341 => 105,  337 => 103,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 89,  283 => 55,  278 => 86,  268 => 85,  264 => 84,  258 => 81,  252 => 80,  247 => 78,  241 => 77,  235 => 74,  229 => 78,  224 => 71,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 49,  140 => 55,  132 => 51,  128 => 49,  119 => 42,  107 => 36,  71 => 18,  177 => 65,  165 => 64,  160 => 61,  135 => 47,  126 => 45,  114 => 42,  84 => 32,  70 => 25,  67 => 24,  61 => 21,  38 => 6,  94 => 22,  89 => 34,  85 => 25,  75 => 28,  68 => 14,  56 => 21,  87 => 33,  21 => 2,  26 => 6,  93 => 28,  88 => 6,  78 => 29,  46 => 15,  27 => 4,  44 => 15,  31 => 4,  28 => 3,  201 => 92,  196 => 90,  183 => 70,  171 => 61,  166 => 71,  163 => 70,  158 => 67,  156 => 58,  151 => 57,  142 => 59,  138 => 57,  136 => 44,  121 => 27,  117 => 25,  105 => 40,  91 => 17,  62 => 16,  49 => 9,  24 => 3,  25 => 3,  19 => 1,  79 => 35,  72 => 31,  69 => 11,  47 => 15,  40 => 9,  37 => 10,  22 => 2,  246 => 80,  157 => 56,  145 => 46,  139 => 50,  131 => 42,  123 => 31,  120 => 53,  115 => 43,  111 => 37,  108 => 37,  101 => 39,  98 => 38,  96 => 38,  83 => 25,  74 => 14,  66 => 20,  55 => 15,  52 => 18,  50 => 10,  43 => 10,  41 => 7,  35 => 7,  32 => 5,  29 => 4,  209 => 82,  203 => 78,  199 => 67,  193 => 73,  189 => 71,  187 => 84,  182 => 66,  176 => 65,  173 => 74,  168 => 66,  164 => 56,  162 => 55,  154 => 54,  149 => 51,  147 => 50,  144 => 53,  141 => 51,  133 => 55,  130 => 41,  125 => 44,  122 => 43,  116 => 51,  112 => 43,  109 => 41,  106 => 41,  103 => 40,  99 => 21,  95 => 20,  92 => 35,  86 => 28,  82 => 31,  80 => 19,  73 => 27,  64 => 26,  60 => 6,  57 => 19,  54 => 16,  51 => 17,  48 => 16,  45 => 16,  42 => 8,  39 => 11,  36 => 5,  33 => 7,  30 => 4,);
    }
}

<?php

/* OlegOrderformBundle:ScanOrder:index.html.twig */
class __TwigTemplate_dd68e64ef2a4fd6cfdc3a0feb55d407e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("OlegOrderformBundle::Default/base.html.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
            'body' => array($this, 'block_body'),
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

    // line 5
    public function block_content($context, array $blocks = array())
    {
        echo " 
";
        // line 6
        $this->displayBlock('body', $context, $blocks);
    }

    public function block_body($context, array $blocks = array())
    {
        // line 15
        echo " 

    <form action=\"";
        // line 17
        echo $this->env->getExtension('routing')->getPath("index");
        echo "\" method=\"get\" ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'enctype');
;
        echo ">                  
";
        // line 19
        echo "        
        <div class=\"row-fluid\">
            <div class=\"span4\">
";
        // line 25
        echo "            ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "filter"), 'widget');
        echo "
            <button class=\"btn \" type=\"submit\">Filter</button>     
            </div>
";
        // line 30
        echo "            <div class=\"span4\">
";
        // line 31
        echo "           
";
        // line 34
        echo "            ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "search"), 'widget');
        echo "                
            <button class=\"btn\" type=\"submit\">Search</button>
            </div>
        </div>
              
";
        // line 40
        echo "        
    </form>

    <table class=\"table table-hover table-condensed\">
        <thead>
            <tr>
                <th>Order#</th>
                <th>#Slides</th>              
                <th>Order Date</th>
                <th>Status</th>              
                <th>Priority</th>               
                <th>Return Slide</th>             
                <th>Provider_TODEL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        ";
        // line 57
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["entities"]) ? $context["entities"] : $this->getContext($context, "entities")));
        foreach ($context['_seq'] as $context["_key"] => $context["entity"]) {
            // line 58
            echo "            
            ";
            // line 59
            if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "active")) {
                // line 60
                echo "                <tr class=\"activetr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "cancel")) {
                // line 62
                echo "                <tr class=\"canceltr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "completed")) {
                // line 64
                echo "                <tr class=\"completedtr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "uncompleted")) {
                // line 66
                echo "                <tr class=\"uncompletedtr\" >
            ";
            } else {
                // line 68
                echo "                <tr>
            ";
            }
            // line 70
            echo "
                <td><a href=\"";
            // line 71
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_show", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "html", null, true);
            echo "</a></td>
                <td>
                    ";
            // line 73
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "slide"), "count"), "html", null, true);
            echo ":
";
            // line 80
            echo "                    ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "slide"));
            foreach ($context['_seq'] as $context["_key"] => $context["slide"]) {
                // line 81
                echo "                        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("slide_show", array("id" => $this->getAttribute((isset($context["slide"]) ? $context["slide"] : $this->getContext($context, "slide")), "id"))), "html", null, true);
                echo "\">
                            acc:";
                // line 82
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["slide"]) ? $context["slide"] : $this->getContext($context, "slide")), "accession"), "html", null, true);
                echo "&nbsp;
                            ";
                // line 83
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["slide"]) ? $context["slide"] : $this->getContext($context, "slide")), "block"), "part"), "html", null, true);
                echo "
                            ";
                // line 84
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["slide"]) ? $context["slide"] : $this->getContext($context, "slide")), "block"), "html", null, true);
                echo " 
                        </a>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['slide'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 87
            echo "                </td>
                <td>";
            // line 88
            if ($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "orderdate")) {
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "orderdate"), "Y-m-d H"), "html", null, true);
                echo "h";
            }
            echo "</td>
                <td>";
            // line 89
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status"), "html", null, true);
            echo "</td>
                <td>";
            // line 90
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "priority"), "html", null, true);
            echo "</td>            
                <td>";
            // line 91
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "returnSlide"), "html", null, true);
            echo "</td>
                <td>";
            // line 92
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "provider"), "html", null, true);
            echo "</td>
                <td>  
                    
                    ";
            // line 95
            echo twig_include($this->env, $context, "OlegOrderformBundle::Default/action.html.twig");
            echo "
";
            // line 105
            echo "                   
                </td>
            </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['entity'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 109
        echo "        </tbody>
    </table>
      
";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:ScanOrder:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  251 => 116,  232 => 106,  185 => 91,  216 => 95,  206 => 94,  127 => 57,  256 => 118,  236 => 110,  226 => 102,  195 => 95,  192 => 88,  155 => 66,  728 => 23,  722 => 20,  718 => 18,  707 => 17,  698 => 320,  680 => 306,  666 => 298,  643 => 284,  628 => 275,  622 => 271,  619 => 269,  607 => 262,  598 => 256,  584 => 248,  578 => 245,  559 => 228,  557 => 227,  555 => 226,  548 => 221,  536 => 218,  533 => 217,  531 => 216,  528 => 214,  516 => 211,  514 => 210,  500 => 204,  490 => 198,  484 => 197,  482 => 196,  476 => 192,  468 => 190,  466 => 189,  460 => 185,  458 => 184,  452 => 183,  443 => 178,  439 => 177,  431 => 175,  422 => 172,  418 => 171,  410 => 169,  401 => 165,  397 => 164,  389 => 161,  357 => 148,  353 => 147,  343 => 143,  339 => 142,  319 => 137,  310 => 133,  302 => 130,  284 => 124,  280 => 123,  254 => 125,  249 => 115,  244 => 114,  231 => 104,  223 => 101,  219 => 100,  205 => 89,  175 => 74,  167 => 87,  148 => 57,  137 => 72,  848 => 18,  834 => 12,  823 => 11,  817 => 323,  814 => 321,  803 => 316,  790 => 305,  784 => 302,  770 => 294,  759 => 286,  753 => 283,  744 => 277,  738 => 274,  732 => 270,  729 => 268,  723 => 264,  702 => 322,  694 => 247,  688 => 244,  672 => 230,  665 => 229,  660 => 295,  656 => 224,  649 => 287,  646 => 222,  641 => 218,  635 => 217,  632 => 216,  627 => 212,  621 => 211,  618 => 210,  612 => 205,  606 => 204,  604 => 203,  602 => 202,  597 => 199,  591 => 198,  588 => 197,  583 => 194,  579 => 193,  575 => 192,  570 => 191,  566 => 189,  562 => 231,  558 => 187,  554 => 186,  550 => 185,  547 => 184,  529 => 182,  527 => 181,  515 => 177,  509 => 175,  506 => 205,  501 => 173,  498 => 203,  488 => 167,  485 => 166,  467 => 163,  463 => 162,  455 => 159,  449 => 157,  446 => 156,  441 => 155,  438 => 154,  429 => 150,  425 => 149,  421 => 148,  417 => 147,  406 => 144,  392 => 142,  386 => 140,  378 => 156,  372 => 135,  369 => 134,  364 => 133,  361 => 150,  356 => 129,  352 => 128,  348 => 146,  345 => 126,  333 => 124,  331 => 140,  327 => 139,  323 => 138,  317 => 119,  306 => 132,  301 => 113,  297 => 112,  267 => 118,  259 => 127,  242 => 113,  237 => 92,  221 => 88,  213 => 86,  200 => 89,  190 => 83,  118 => 66,  153 => 56,  102 => 31,  100 => 44,  113 => 36,  110 => 62,  97 => 57,  1357 => 388,  1348 => 387,  1346 => 386,  1343 => 385,  1327 => 381,  1320 => 380,  1318 => 379,  1315 => 378,  1292 => 374,  1267 => 373,  1265 => 372,  1262 => 371,  1250 => 366,  1245 => 365,  1243 => 364,  1240 => 363,  1231 => 357,  1225 => 355,  1222 => 354,  1217 => 353,  1215 => 352,  1212 => 351,  1205 => 346,  1196 => 344,  1192 => 343,  1189 => 342,  1186 => 341,  1184 => 340,  1181 => 339,  1173 => 335,  1171 => 334,  1168 => 333,  1162 => 329,  1156 => 327,  1153 => 326,  1151 => 325,  1148 => 324,  1139 => 319,  1137 => 318,  1114 => 317,  1111 => 316,  1108 => 315,  1105 => 314,  1102 => 313,  1099 => 312,  1096 => 311,  1094 => 310,  1091 => 309,  1084 => 305,  1080 => 304,  1075 => 303,  1073 => 302,  1070 => 301,  1063 => 296,  1060 => 295,  1052 => 290,  1049 => 289,  1047 => 288,  1044 => 287,  1036 => 282,  1032 => 281,  1028 => 280,  1025 => 279,  1023 => 278,  1020 => 277,  1012 => 273,  1010 => 269,  1008 => 268,  1005 => 267,  1000 => 263,  978 => 258,  975 => 257,  972 => 256,  969 => 255,  966 => 254,  963 => 253,  960 => 252,  957 => 251,  954 => 250,  951 => 249,  948 => 248,  946 => 247,  943 => 246,  935 => 240,  932 => 239,  930 => 238,  927 => 237,  919 => 233,  916 => 232,  914 => 231,  911 => 230,  899 => 226,  896 => 225,  893 => 224,  890 => 223,  888 => 222,  885 => 221,  877 => 217,  874 => 216,  872 => 215,  869 => 214,  861 => 210,  858 => 209,  856 => 208,  853 => 20,  845 => 203,  842 => 15,  840 => 201,  837 => 13,  829 => 196,  826 => 195,  824 => 194,  821 => 193,  813 => 189,  810 => 188,  808 => 317,  805 => 186,  797 => 182,  794 => 181,  792 => 180,  789 => 179,  781 => 175,  779 => 174,  776 => 297,  768 => 169,  765 => 168,  763 => 167,  760 => 166,  752 => 162,  749 => 161,  747 => 160,  745 => 159,  742 => 158,  735 => 153,  725 => 152,  720 => 151,  717 => 261,  711 => 148,  708 => 255,  706 => 146,  703 => 145,  695 => 139,  693 => 317,  692 => 137,  691 => 136,  690 => 135,  685 => 134,  679 => 132,  676 => 131,  674 => 303,  671 => 129,  662 => 228,  658 => 122,  654 => 121,  650 => 120,  645 => 119,  639 => 117,  636 => 116,  634 => 278,  631 => 114,  615 => 110,  613 => 265,  610 => 108,  594 => 104,  592 => 253,  589 => 102,  572 => 98,  560 => 96,  553 => 93,  551 => 223,  546 => 91,  543 => 219,  525 => 89,  523 => 212,  520 => 87,  511 => 82,  508 => 206,  505 => 80,  499 => 78,  497 => 77,  492 => 199,  489 => 75,  486 => 74,  471 => 72,  459 => 69,  456 => 68,  450 => 182,  442 => 62,  433 => 151,  428 => 59,  426 => 173,  414 => 170,  408 => 50,  405 => 49,  403 => 48,  400 => 47,  390 => 141,  388 => 42,  385 => 41,  377 => 37,  371 => 35,  366 => 33,  363 => 32,  350 => 26,  344 => 24,  342 => 23,  335 => 141,  332 => 20,  316 => 16,  313 => 15,  311 => 14,  308 => 13,  299 => 8,  293 => 128,  290 => 5,  281 => 385,  271 => 119,  266 => 363,  263 => 128,  260 => 360,  255 => 350,  253 => 116,  250 => 97,  248 => 122,  245 => 96,  240 => 108,  238 => 110,  233 => 91,  230 => 106,  227 => 103,  217 => 101,  215 => 277,  212 => 99,  210 => 91,  207 => 89,  204 => 264,  202 => 87,  197 => 86,  194 => 245,  191 => 83,  186 => 236,  184 => 85,  181 => 90,  179 => 78,  174 => 73,  161 => 199,  146 => 62,  104 => 59,  34 => 6,  152 => 64,  129 => 71,  124 => 44,  65 => 18,  20 => 2,  90 => 37,  76 => 30,  291 => 61,  288 => 125,  279 => 43,  276 => 378,  273 => 105,  262 => 28,  257 => 27,  243 => 324,  225 => 89,  222 => 107,  218 => 72,  180 => 76,  172 => 73,  170 => 88,  159 => 62,  150 => 82,  134 => 51,  81 => 34,  63 => 30,  77 => 33,  58 => 19,  59 => 16,  53 => 18,  23 => 3,  480 => 162,  474 => 191,  469 => 164,  461 => 70,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 61,  435 => 176,  430 => 144,  427 => 143,  423 => 57,  413 => 146,  409 => 145,  407 => 131,  402 => 130,  398 => 129,  393 => 162,  387 => 122,  384 => 160,  381 => 120,  379 => 119,  374 => 155,  368 => 34,  365 => 151,  362 => 110,  360 => 109,  355 => 27,  341 => 105,  337 => 22,  322 => 101,  314 => 118,  312 => 98,  309 => 117,  305 => 95,  298 => 129,  294 => 90,  285 => 3,  283 => 107,  278 => 384,  268 => 130,  264 => 84,  258 => 118,  252 => 80,  247 => 115,  241 => 77,  235 => 308,  229 => 90,  224 => 102,  220 => 103,  214 => 95,  208 => 109,  169 => 207,  143 => 54,  140 => 80,  132 => 69,  128 => 67,  119 => 52,  107 => 33,  71 => 26,  177 => 89,  165 => 70,  160 => 61,  135 => 39,  126 => 70,  114 => 64,  84 => 32,  70 => 29,  67 => 26,  61 => 25,  38 => 7,  94 => 38,  89 => 27,  85 => 38,  75 => 27,  68 => 35,  56 => 25,  87 => 33,  21 => 4,  26 => 5,  93 => 43,  88 => 33,  78 => 40,  46 => 14,  27 => 4,  44 => 17,  31 => 4,  28 => 3,  201 => 94,  196 => 87,  183 => 78,  171 => 69,  166 => 206,  163 => 68,  158 => 84,  156 => 61,  151 => 56,  142 => 52,  138 => 60,  136 => 73,  121 => 56,  117 => 25,  105 => 36,  91 => 41,  62 => 21,  49 => 17,  24 => 5,  25 => 29,  19 => 2,  79 => 32,  72 => 36,  69 => 34,  47 => 15,  40 => 15,  37 => 10,  22 => 4,  246 => 114,  157 => 66,  145 => 81,  139 => 73,  131 => 48,  123 => 56,  120 => 38,  115 => 36,  111 => 34,  108 => 49,  101 => 58,  98 => 45,  96 => 30,  83 => 25,  74 => 30,  66 => 31,  55 => 17,  52 => 11,  50 => 12,  43 => 10,  41 => 25,  35 => 6,  32 => 6,  29 => 5,  209 => 98,  203 => 78,  199 => 105,  193 => 92,  189 => 92,  187 => 81,  182 => 76,  176 => 220,  173 => 74,  168 => 70,  164 => 64,  162 => 68,  154 => 83,  149 => 55,  147 => 55,  144 => 74,  141 => 52,  133 => 49,  130 => 50,  125 => 48,  122 => 68,  116 => 54,  112 => 53,  109 => 46,  106 => 60,  103 => 36,  99 => 46,  95 => 35,  92 => 29,  86 => 25,  82 => 28,  80 => 31,  73 => 20,  64 => 18,  60 => 22,  57 => 16,  54 => 16,  51 => 19,  48 => 10,  45 => 16,  42 => 7,  39 => 10,  36 => 8,  33 => 7,  30 => 3,);
    }
}

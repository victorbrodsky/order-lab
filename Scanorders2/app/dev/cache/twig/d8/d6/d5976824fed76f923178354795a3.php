<?php

/* OlegOrderformBundle:MultyScanOrder:index.html.twig */
class __TwigTemplate_d8d6d5976824fed76f923178354795a3 extends Twig_Template
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
        // line 9
        echo "<table class=\"table table-hover table-condensed\">
        <thead>
            <tr>
                <th>Order#</th>
                <th>Patients</th>
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
        // line 23
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["entities"]) ? $context["entities"] : $this->getContext($context, "entities")));
        foreach ($context['_seq'] as $context["_key"] => $context["entity"]) {
            // line 24
            echo "            
            ";
            // line 25
            if (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "active")) {
                // line 26
                echo "                <tr class=\"activetr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "cancel")) {
                // line 28
                echo "                <tr class=\"canceltr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "completed")) {
                // line 30
                echo "                <tr class=\"completedtr\" >
            ";
            } elseif (($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status") == "uncompleted")) {
                // line 32
                echo "                <tr class=\"uncompletedtr\" >
            ";
            } else {
                // line 34
                echo "                <tr>
            ";
            }
            // line 36
            echo "
                <td><a href=\"";
            // line 37
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("scanorder_show", array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"))), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id"), "html", null, true);
            echo "</a></td>
                <td>
                    ";
            // line 46
            echo "                    ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "patient"));
            foreach ($context['_seq'] as $context["_key"] => $context["patient"]) {
                // line 47
                echo "                        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("patient_show", array("id" => $this->getAttribute((isset($context["patient"]) ? $context["patient"] : $this->getContext($context, "patient")), "id"))), "html", null, true);
                echo "\">
                            Patient: (ID=";
                // line 48
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["patient"]) ? $context["patient"] : $this->getContext($context, "patient")), "id"), "html", null, true);
                echo ", MRN=";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["patient"]) ? $context["patient"] : $this->getContext($context, "patient")), "mrn"), "html", null, true);
                echo ")<br>
                        </a>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['patient'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 51
            echo "                </td>
                <td>";
            // line 52
            if ($this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "orderdate")) {
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "orderdate"), "Y-m-d H"), "html", null, true);
                echo "h";
            }
            echo "</td>
                <td>";
            // line 53
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "status"), "html", null, true);
            echo "</td>
                <td>";
            // line 54
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "priority"), "html", null, true);
            echo "</td>            
                <td>";
            // line 55
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "returnSlide"), "html", null, true);
            echo "</td>
                <td>";
            // line 56
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "provider"), "html", null, true);
            echo "</td>
                <td>                    
                    ";
            // line 58
            echo twig_include($this->env, $context, "OlegOrderformBundle::Default/action.html.twig");
            echo "                  
                </td>
            </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['entity'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 62
        echo "        </tbody>
    </table>
      
";
    }

    public function getTemplateName()
    {
        return "OlegOrderformBundle:MultyScanOrder:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  153 => 62,  102 => 45,  100 => 47,  113 => 39,  110 => 38,  97 => 37,  1357 => 388,  1348 => 387,  1346 => 386,  1343 => 385,  1327 => 381,  1320 => 380,  1318 => 379,  1315 => 378,  1292 => 374,  1267 => 373,  1265 => 372,  1262 => 371,  1250 => 366,  1245 => 365,  1243 => 364,  1240 => 363,  1231 => 357,  1225 => 355,  1222 => 354,  1217 => 353,  1215 => 352,  1212 => 351,  1205 => 346,  1196 => 344,  1192 => 343,  1189 => 342,  1186 => 341,  1184 => 340,  1181 => 339,  1173 => 335,  1171 => 334,  1168 => 333,  1162 => 329,  1156 => 327,  1153 => 326,  1151 => 325,  1148 => 324,  1139 => 319,  1137 => 318,  1114 => 317,  1111 => 316,  1108 => 315,  1105 => 314,  1102 => 313,  1099 => 312,  1096 => 311,  1094 => 310,  1091 => 309,  1084 => 305,  1080 => 304,  1075 => 303,  1073 => 302,  1070 => 301,  1063 => 296,  1060 => 295,  1052 => 290,  1049 => 289,  1047 => 288,  1044 => 287,  1036 => 282,  1032 => 281,  1028 => 280,  1025 => 279,  1023 => 278,  1020 => 277,  1012 => 273,  1010 => 269,  1008 => 268,  1005 => 267,  1000 => 263,  978 => 258,  975 => 257,  972 => 256,  969 => 255,  966 => 254,  963 => 253,  960 => 252,  957 => 251,  954 => 250,  951 => 249,  948 => 248,  946 => 247,  943 => 246,  935 => 240,  932 => 239,  930 => 238,  927 => 237,  919 => 233,  916 => 232,  914 => 231,  911 => 230,  899 => 226,  896 => 225,  893 => 224,  890 => 223,  888 => 222,  885 => 221,  877 => 217,  874 => 216,  872 => 215,  869 => 214,  861 => 210,  858 => 209,  856 => 208,  853 => 207,  845 => 203,  842 => 202,  840 => 201,  837 => 200,  829 => 196,  826 => 195,  824 => 194,  821 => 193,  813 => 189,  810 => 188,  808 => 187,  805 => 186,  797 => 182,  794 => 181,  792 => 180,  789 => 179,  781 => 175,  779 => 174,  776 => 173,  768 => 169,  765 => 168,  763 => 167,  760 => 166,  752 => 162,  749 => 161,  747 => 160,  745 => 159,  742 => 158,  735 => 153,  725 => 152,  720 => 151,  717 => 150,  711 => 148,  708 => 147,  706 => 146,  703 => 145,  695 => 139,  693 => 138,  692 => 137,  691 => 136,  690 => 135,  685 => 134,  679 => 132,  676 => 131,  674 => 130,  671 => 129,  662 => 123,  658 => 122,  654 => 121,  650 => 120,  645 => 119,  639 => 117,  636 => 116,  634 => 115,  631 => 114,  615 => 110,  613 => 109,  610 => 108,  594 => 104,  592 => 103,  589 => 102,  572 => 98,  560 => 96,  553 => 93,  551 => 92,  546 => 91,  543 => 90,  525 => 89,  523 => 88,  520 => 87,  511 => 82,  508 => 81,  505 => 80,  499 => 78,  497 => 77,  492 => 76,  489 => 75,  486 => 74,  471 => 72,  459 => 69,  456 => 68,  450 => 64,  442 => 62,  433 => 60,  428 => 59,  426 => 58,  414 => 52,  408 => 50,  405 => 49,  403 => 48,  400 => 47,  390 => 43,  388 => 42,  385 => 41,  377 => 37,  371 => 35,  366 => 33,  363 => 32,  350 => 26,  344 => 24,  342 => 23,  335 => 21,  332 => 20,  316 => 16,  313 => 15,  311 => 14,  308 => 13,  299 => 8,  293 => 6,  290 => 5,  281 => 385,  271 => 371,  266 => 363,  263 => 362,  260 => 360,  255 => 350,  253 => 339,  250 => 338,  248 => 333,  245 => 332,  240 => 323,  238 => 309,  233 => 301,  230 => 300,  227 => 298,  217 => 286,  215 => 277,  212 => 276,  210 => 267,  207 => 266,  204 => 264,  202 => 263,  197 => 246,  194 => 245,  191 => 243,  186 => 236,  184 => 230,  181 => 229,  179 => 221,  174 => 214,  161 => 199,  146 => 178,  104 => 87,  34 => 6,  152 => 49,  129 => 145,  124 => 129,  65 => 26,  20 => 2,  90 => 32,  76 => 31,  291 => 61,  288 => 4,  279 => 43,  276 => 378,  273 => 377,  262 => 28,  257 => 27,  243 => 324,  225 => 295,  222 => 294,  218 => 72,  180 => 71,  172 => 63,  170 => 60,  159 => 193,  150 => 48,  134 => 55,  81 => 34,  63 => 25,  77 => 32,  58 => 14,  59 => 30,  53 => 18,  23 => 3,  480 => 162,  474 => 161,  469 => 71,  461 => 70,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 61,  435 => 146,  430 => 144,  427 => 143,  423 => 57,  413 => 134,  409 => 132,  407 => 131,  402 => 130,  398 => 129,  393 => 126,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 36,  368 => 34,  365 => 111,  362 => 110,  360 => 109,  355 => 27,  341 => 105,  337 => 22,  322 => 101,  314 => 99,  312 => 98,  309 => 97,  305 => 95,  298 => 91,  294 => 90,  285 => 3,  283 => 55,  278 => 384,  268 => 370,  264 => 84,  258 => 351,  252 => 80,  247 => 78,  241 => 77,  235 => 308,  229 => 78,  224 => 71,  220 => 287,  214 => 69,  208 => 68,  169 => 207,  143 => 58,  140 => 55,  132 => 38,  128 => 49,  119 => 52,  107 => 48,  71 => 19,  177 => 65,  165 => 64,  160 => 61,  135 => 39,  126 => 53,  114 => 108,  84 => 26,  70 => 13,  67 => 12,  61 => 2,  38 => 12,  94 => 57,  89 => 34,  85 => 36,  75 => 14,  68 => 14,  56 => 23,  87 => 28,  21 => 4,  26 => 3,  93 => 35,  88 => 37,  78 => 19,  46 => 11,  27 => 4,  44 => 9,  31 => 17,  28 => 16,  201 => 92,  196 => 90,  183 => 70,  171 => 213,  166 => 206,  163 => 70,  158 => 50,  156 => 192,  151 => 185,  142 => 59,  138 => 56,  136 => 165,  121 => 128,  117 => 25,  105 => 48,  91 => 56,  62 => 16,  49 => 8,  24 => 2,  25 => 29,  19 => 1,  79 => 32,  72 => 31,  69 => 28,  47 => 15,  40 => 9,  37 => 10,  22 => 2,  246 => 80,  157 => 56,  145 => 46,  139 => 166,  131 => 157,  123 => 31,  120 => 41,  115 => 40,  111 => 107,  108 => 37,  101 => 32,  98 => 24,  96 => 67,  83 => 22,  74 => 21,  66 => 19,  55 => 15,  52 => 18,  50 => 12,  43 => 6,  41 => 38,  35 => 3,  32 => 5,  29 => 5,  209 => 82,  203 => 78,  199 => 262,  193 => 73,  189 => 237,  187 => 84,  182 => 66,  176 => 220,  173 => 74,  168 => 66,  164 => 200,  162 => 55,  154 => 186,  149 => 44,  147 => 50,  144 => 173,  141 => 41,  133 => 48,  130 => 54,  125 => 52,  122 => 42,  116 => 51,  112 => 43,  109 => 102,  106 => 101,  103 => 26,  99 => 68,  95 => 46,  92 => 28,  86 => 46,  82 => 31,  80 => 23,  73 => 30,  64 => 12,  60 => 24,  57 => 10,  54 => 13,  51 => 8,  48 => 7,  45 => 6,  42 => 10,  39 => 9,  36 => 6,  33 => 4,  30 => 3,);
    }
}

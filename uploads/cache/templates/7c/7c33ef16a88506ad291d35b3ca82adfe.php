<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* welcome.twig.html */
class __TwigTemplate_69fd38343f6cf77f4eabee9be18ae1f1 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 2
        $macros["homepage"] = $this->macros["homepage"] = $this;
        // line 3
        yield "
<div class=\"flex flex-wrap mb-4 -mx-2 items-stretch \">
    <div class=\"w-full mx-2 my-2\">
        <h2 class=\"text-purple-700 font-bold tracking-wide uppercase\">Bienvenido a EduConnect</h2>
        <p class=\"text-gray-700 text-justify leading-relaxed mt-2\">
            La plataforma integral de gestión y acompañamiento académico diseñada para transformar el aprendizaje. Conectamos estudiantes, docentes y tutores especializados en un entorno digital colaborativo enfocado en la excelencia y el desarrollo educativo.
        </p>
    </div>

        ";
        // line 12
        yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Solicitudes de Ingreso", "Espacio dirigido a estudiantes y familias interesadas en formar parte de nuestra comunidad. Complete el formulario digital para iniciar su proceso de admisión y diagnóstico académico.", "/?q=/modules/Estudiante/estudiante_login.php",         // line 16
($context["organisationName"] ?? null), "none"], 12, $context, $this->getSourceContext());
        // line 18
        yield "

    ";
        // line 20
        yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Portal de Tutores", "Área exclusiva para profesionales de la educación y tutores académicos de EduConnect. Inicie sesión para gestionar sus solicitudes de apoyo y controlar sus tutorías programadas.", "/?q=/modules/Tutor/tutor_login.php",         // line 24
($context["organisationName"] ?? null), "none"], 20, $context, $this->getSourceContext());
        // line 26
        yield "
    
    ";
        // line 28
        if (($context["publicRegistration"] ?? null)) {
            // line 29
            yield "        ";
            yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Registro de Usuarios", "Únase hoy mismo a nuestra comunidad de aprendizaje continuo de forma rápida y sencilla.", "/?q=/publicRegistration.php",             // line 33
($context["organisationName"] ?? null), "none"], 29, $context, $this->getSourceContext());
            // line 35
            yield "
    ";
        }
        // line 37
        yield "
    ";
        // line 38
        if (($context["makeDepartmentsPublic"] ?? null)) {
            // line 39
            yield "        ";
            yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Departamentos Académicos", "Le invitamos a explorar las distintas áreas de estudio e información departamental para conocer más sobre nuestra oferta e identidad educativa.", "/?q=/modules/Departments/departments.php",             // line 43
($context["organisationName"] ?? null), "none"], 39, $context, $this->getSourceContext());
            // line 45
            yield "
    ";
        }
        // line 47
        yield "
    ";
        // line 48
        if (($context["makeUnitsPublic"] ?? null)) {
            // line 49
            yield "        ";
            yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Aprenda con Nosotros", "Compartimos de manera abierta algunas de nuestras unidades académicas vigentes con el público en general. Explore nuestro material educativo interactivo de libre acceso.", "/?q=/modules/Planner/units_public.php&sidebar=false",             // line 53
($context["organisationName"] ?? null), "none"], 49, $context, $this->getSourceContext());
            // line 55
            yield "
    ";
        }
        // line 57
        yield "
    ";
        // line 58
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["indexHooks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["hook"]) {
            // line 59
            yield "        ";
            yield CoreExtension::callMacro($macros["homepage"], "macro_card", [CoreExtension::getAttribute($this->env, $this->source, $context["hook"], "title", [], "any", false, false, false, 59), CoreExtension::getAttribute($this->env, $this->source, $context["hook"], "text", [], "any", false, false, false, 59), CoreExtension::getAttribute($this->env, $this->source, $context["hook"], "url", [], "any", false, false, false, 59), ($context["organisationName"] ?? null)], 59, $context, $this->getSourceContext());
            yield "
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['hook'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 61
        yield "
    ";
        // line 62
        if (($context["privacyPolicy"] ?? null)) {
            // line 63
            yield "        ";
            yield CoreExtension::callMacro($macros["homepage"], "macro_card", ["Política de Privacidad", "Consulte los lineamientos oficiales sobre cómo los datos personales de nuestra comunidad son protegidos, almacenados y administrados de manera segura.", "/?q=privacyPolicy.php",             // line 67
($context["organisationName"] ?? null), "none"], 63, $context, $this->getSourceContext());
            // line 69
            yield "
    ";
        }
        // line 71
        yield "</div>

";
        return; yield '';
    }

    // line 73
    public function macro_card($__name__ = null, $__content__ = null, $__url__ = "", $__organisationName__ = null, $__orgNamePos__ = "first", ...$__varargs__)
    {
        $macros = $this->macros;
        $context = $this->env->mergeGlobals([
            "name" => $__name__,
            "content" => $__content__,
            "url" => $__url__,
            "organisationName" => $__organisationName__,
            "orgNamePos" => $__orgNamePos__,
            "varargs" => $__varargs__,
        ]);

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 74
            yield "
    <div class=\"w-full sm:w-1/2 px-2 pb-4\">
        <a href=\"";
            // line 76
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["absoluteURL"] ?? null) . ($context["url"] ?? null)), "html", null, true);
            yield "\" class=\"block border shadow-sm rounded bg-white h-full text-gray-800 hover:shadow-md hover:text-purple-700 hover:border-purple-600 transition-colors duration-200\">
            <div class=\"block m-0 pt-4 px-4 text-base uppercase font-bold font-sans tracking-tight\">
                ";
            // line 78
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
            yield "

                <svg class=\"w-5 h-5 float-right -mt-px fill-current\" aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"angle-double-right\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34zm192-34l-136-136c-9.4-9.4-24.6-9.4-33.9 0l-22.6 22.6c-9.4 9.4-9.4 24.6 0 33.9l96.4 96.4-96.4 96.4c-9.4 9.4-9.4 24.6 0 33.9l22.6 22.6c9.4 9.4 24.6 9.4 33.9 0l136-136c9.4-9.2 9.4-24.4 0-33.8z\"></path></svg>
            </div>
            <p class=\"mb-1 p-4 text-gray-700 leading-tight text-sm\">
                ";
            // line 83
            if ((($context["orgNamePos"] ?? null) == "first")) {
                // line 84
                yield "                    ";
                yield Twig\Extension\CoreExtension::sprintf(($context["content"] ?? null), ($context["organisationName"] ?? null), "", "");
                yield "
                ";
            } elseif ((            // line 85
($context["orgNamePos"] ?? null) == "second")) {
                // line 86
                yield "                    ";
                yield Twig\Extension\CoreExtension::sprintf(($context["content"] ?? null), "", "", ($context["organisationName"] ?? null));
                yield "
                ";
            } else {
                // line 88
                yield "                    ";
                yield ($context["content"] ?? null);
                yield "
                ";
            }
            // line 90
            yield "            </p>
        </a>
    </div>

";
            return; yield '';
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "welcome.twig.html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  191 => 90,  185 => 88,  179 => 86,  177 => 85,  172 => 84,  170 => 83,  162 => 78,  157 => 76,  153 => 74,  137 => 73,  130 => 71,  126 => 69,  124 => 67,  122 => 63,  120 => 62,  117 => 61,  108 => 59,  104 => 58,  101 => 57,  97 => 55,  95 => 53,  93 => 49,  91 => 48,  88 => 47,  84 => 45,  82 => 43,  80 => 39,  78 => 38,  75 => 37,  71 => 35,  69 => 33,  67 => 29,  65 => 28,  61 => 26,  59 => 24,  58 => 20,  54 => 18,  52 => 16,  51 => 12,  40 => 3,  38 => 2,);
    }

    public function getSourceContext()
    {
        return new Source("", "welcome.twig.html", "C:\\xampp\\htdocs\\educonnect\\resources\\templates\\welcome.twig.html");
    }
}

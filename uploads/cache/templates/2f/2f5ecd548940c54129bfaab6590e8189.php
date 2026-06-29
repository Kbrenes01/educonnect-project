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

/* menu.twig.html */
class __TwigTemplate_489eed971d3dbe15489e9fccafe47192 extends Template
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
        // line 12
        yield "
<hr class=\"opacity-25 border-";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
        yield "-300 border-b-0 mx-0 -my-px\">

<ul class=\"list-none flex flex-wrap items-center my-3 -mx-4\">
    <li class=\"mx-0 px-0\">
        <a hx-boost=\"true\" hx-target=\"#content-wrap\" hx-select=\"#content-wrap\" hx-swap=\"outerHTML show:no-scroll swap:0s\" class=\"block uppercase font-bold text-sm text-gray-100 hover:text-gray-800 no-underline ml-1 px-4 py-3\" href=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["absoluteURL"] ?? null), "html", null, true);
        yield "/index.php\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Home"), "html", null, true);
        yield "</a>
    </li>

    ";
        // line 20
        if (($context["isLoggedIn"] ?? null)) {
            // line 21
            yield "        ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["menuMain"] ?? null));
            foreach ($context['_seq'] as $context["categoryName"] => $context["items"]) {
                // line 22
                yield "            <li class=\"sm:relative group mx-0 px-0\" x-data=\"{menuOpen: false}\" @mouseleave=\"menuOpen = false\" @click.outside=\"menuOpen = false\">
                <a @mouseenter=\"menuOpen = true\" @click=\"menuOpen = true\" :class=\"{'text-gray-800': menuOpen, 'text-gray-100': !menuOpen}\" class=\"block uppercase font-bold text-sm text-gray-100 no-underline px-4 py-3 cursor-pointer\">";
                // line 23
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()($context["categoryName"]), "html", null, true);
                yield "</a>

                <ul x-cloak x-show=\"menuOpen\" x-transition:enter.duration.250ms x-transition:leave.duration.0s class=\"list-none bg-black bg-opacity-75 backdrop-blur-lg backdrop-contrast-125 backdrop-saturate-150 rounded-md absolute w-available ms-4 me-4 sm:mx-0 sm:w-52 my-0 p-1 sm:p-1.5 z-50 ";
                // line 25
                yield ((($context["rightToLeft"] ?? null)) ? ("right-0") : ("left-0"));
                yield "\">
                    ";
                // line 26
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable($context["items"]);
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 27
                    yield "                        <li class=\"hover:bg-";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
                    yield "-700 rounded\">
                            <a @click=\"menuOpen = false\" class=\"block text-sm text-white focus:text-";
                    // line 28
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
                    yield "-200 no-underline px-2 py-2 md:py-1 leading-normal ";
                    yield ((($context["rightToLeft"] ?? null)) ? ("text-right") : ("text-left"));
                    yield "\" href=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, false, 28), "html", null, true);
                    yield "\">
                                ";
                    // line 29
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "name", [], "any", false, false, false, 29), CoreExtension::getAttribute($this->env, $this->source, $context["item"], "textDomain", [], "any", false, false, false, 29)), "html", null, true);
                    yield "
                            </a>
                        </li>
                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 33
                yield "                </ul>

                <div x-cloak x-show=\"menuOpen\" class=\"absolute -left-8 h-48 m-0 z-40\" style=\"width:calc(100% + 8rem);\"></div>
            </li>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['categoryName'], $context['items'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 38
            yield "
        ";
            // line 40
            yield "        <li class=\"sm:relative group mx-0 px-0\" x-data=\"{menuOpen: false}\" @mouseleave=\"menuOpen = false\" @click.outside=\"menuOpen = false\">
            <a @mouseenter=\"menuOpen = true\" @click=\"menuOpen = true\" :class=\"{'text-gray-800': menuOpen, 'text-gray-100': !menuOpen}\" class=\"block uppercase font-bold text-sm text-gray-100 no-underline px-4 py-3 cursor-pointer\">
                EduConnect
            </a>

            <ul x-cloak x-show=\"menuOpen\" x-transition:enter.duration.250ms x-transition:leave.duration.0s class=\"list-none bg-black bg-opacity-75 backdrop-blur-lg backdrop-contrast-125 backdrop-saturate-150 rounded-md absolute w-available ms-4 me-4 sm:mx-0 sm:w-64 my-0 p-1 sm:p-1.5 z-50 ";
            // line 45
            yield ((($context["rightToLeft"] ?? null)) ? ("right-0") : ("left-0"));
            yield "\">
                <li class=\"hover:bg-";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-700 rounded\">
                    <a hx-boost=\"false\" @click=\"menuOpen = false\" class=\"block text-sm text-white focus:text-";
            // line 47
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-200 no-underline px-2 py-2 md:py-1 leading-normal ";
            yield ((($context["rightToLeft"] ?? null)) ? ("text-right") : ("text-left"));
            yield "\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["absoluteURL"] ?? null), "html", null, true);
            yield "/modules/AdminEduConnect/seguimiento_tutorias.php\">
                        Seguimiento de tutorías
                    </a>
                </li>

                <li class=\"hover:bg-";
            // line 52
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-700 rounded\">
                    <a hx-boost=\"false\" @click=\"menuOpen = false\" class=\"block text-sm text-white focus:text-";
            // line 53
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-200 no-underline px-2 py-2 md:py-1 leading-normal ";
            yield ((($context["rightToLeft"] ?? null)) ? ("text-right") : ("text-left"));
            yield "\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["absoluteURL"] ?? null), "html", null, true);
            yield "/modules/AdminEduConnect/consultar_usuarios.php\">
                        Consulta de usuarios
                    </a>
                </li>

                <li class=\"hover:bg-";
            // line 58
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-700 rounded\">
                    <a hx-boost=\"false\" @click=\"menuOpen = false\" class=\"block text-sm text-white focus:text-";
            // line 59
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["themeColour"] ?? null), "html", null, true);
            yield "-200 no-underline px-2 py-2 md:py-1 leading-normal ";
            yield ((($context["rightToLeft"] ?? null)) ? ("text-right") : ("text-left"));
            yield "\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["absoluteURL"] ?? null), "html", null, true);
            yield "/modules/AdminEduConnect/completar_estudiante.php\">
                        Completar estudiante
                    </a>
                </li>
            </ul>

            <div x-cloak x-show=\"menuOpen\" class=\"absolute -left-8 h-48 m-0 z-40\" style=\"width:calc(100% + 8rem);\"></div>
        </li>
    ";
        }
        // line 68
        yield "
    <li class=\"notificationTray flex-grow relative\">
        <div class=\"flex flex-row-reverse items-center\">

            <div id=\"finderTray\" class=\"mr-4 w-auto sm:w-full max-w-sm\">
                ";
        // line 73
        yield Twig\Extension\CoreExtension::include($this->env, $context, "finder.twig.html");
        yield "
            </div>

            ";
        // line 76
        yield Twig\Extension\CoreExtension::include($this->env, $context, "tray.twig.html");
        yield "
        </div>
    </li>
</ul>";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "menu.twig.html";
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
        return array (  191 => 76,  185 => 73,  178 => 68,  162 => 59,  158 => 58,  146 => 53,  142 => 52,  130 => 47,  126 => 46,  122 => 45,  115 => 40,  112 => 38,  102 => 33,  92 => 29,  84 => 28,  79 => 27,  75 => 26,  71 => 25,  66 => 23,  63 => 22,  58 => 21,  56 => 20,  48 => 17,  41 => 13,  38 => 12,);
    }

    public function getSourceContext()
    {
        return new Source("", "menu.twig.html", "C:\\xampp\\htdocs\\educonnect\\resources\\templates\\menu.twig.html");
    }
}

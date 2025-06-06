<?php

declare(strict_types=1);

namespace App\Services\Pdf;

use App\Services\CacheService;
use App\Services\Pdf\Canvas\CanvasImageRenderer;
use App\Services\Pdf\Canvas\CanvasTextRenderer;
use App\Services\Pdf\Pagination\Paginator;
use BadMethodCallException;
use Barryvdh\DomPDF\PDF as DomPdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use ReflectionMethod;

/*
 * Class PdfService.
 *
 * This class provides pdf mail sending functionality
 */

define(
    'BASE_PATH',
    [
        base_path(),
        sys_get_temp_dir(),
    ]
);

class PdfService
{
    private const DEFAULT_OPTIONS_ARRAY = [
        'isRemoteEnabled'      => true,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled'         => true,
        'dpi'                  => 98.8,
        'compress'             => 0,
        'defaultFont'          => 'sans-serif',
        'defaultPaperSize'     => 'A4',
        'chroot'               => BASE_PATH,
    ];

    /**
     * Pdf builder.
     *
     * @var DomPdf pdf
     */
    public DomPdf $domPdf;

    /**
     * Holds blade view defined variables as array.
     *
     * @var array
     */
    public array $data;

    /**
     * References blade view template file name.
     *
     * @var string
     */
    private string $template;

    /**
     * Holds pdf customizable options.
     *
     * @var array
     */
    public array $options;

    public bool $rendered = false;

    /**
     * @var null|Collection<CanvasTextRenderer>
     */
    public ?Collection $canvasTextRenderers;

    /**
     * @var null|Collection<CanvasImageRenderer>
     */
    public ?Collection $canvasImageRenderers;

    /**
     * @var null|Paginator
     */
    public ?Paginator $paginator = null;

    /**
     * Construct service with default values.
     *
     * @param string             $template
     * @param null|array         $data
     * @param null|Options|array $options
     * @param ?CustomFontOptions $customFontOptions
     */
    public function __construct(string $template, ?array $data = [], null|Options|array $options = [])
    {
        $this->initOptions($options);
        $this->setData($data);
        $this->setTemplate($template);

        $this->canvasTextRenderers = new Collection();
        $this->canvasImageRenderers = new Collection();

        $domPdf = App::make('dompdf.wrapper');
        $domPdf->loadView($this->getTemplate(), $this->getData());
        $domPdf->setOptions($this->options);

        $this->setDomPdf($domPdf);
    }

    /**
     * Dynamically handle calls into the dompdf instance.
     *
     * @param  string      $method
     * @param  null|array  $parameters
     * @return $this|mixed
     */
    public function __call(string $method, ?array $parameters = [])
    {
        $domPdfInstance = $this->getDomPdf();

        if (method_exists($domPdfInstance, $method)) {
            return $domPdfInstance->{$method}(...$parameters);
        }

        $domPdfMethods = get_class_methods($domPdfInstance);

        foreach ($domPdfMethods as $domPdfMethod) {
            $reflectionMethod = new ReflectionMethod($domPdfInstance, $domPdfMethod);

            if ($reflectionMethod->getNumberOfParameters() === 0) {
                $returnedObject = $domPdfInstance->{$domPdfMethod}();

                if (is_object($returnedObject)) {
                    if (method_exists($returnedObject, $method)) {
                        return $returnedObject->{$method}(...$parameters);
                    }
                }
            }
        }

        throw new BadMethodCallException("Method [{$method}] does not exist on PdfService, DomPdf, or related objects.");
    }

    /**
     * Returns data variable.
     *
     * @return array
     */
    private function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets $data variable.
     *
     * @param        $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns template variable.
     *
     * @return string
     */
    private function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Sets template variable.
     *
     * @param        $template
     * @return $this
     */
    public function setTemplate($template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Sets options variable.
     *
     * @param  null|array $options $options
     * @return $this
     */
    public function initOptions(null|array $options): self
    {
        $this->options = $options ?
            [
                ...self::DEFAULT_OPTIONS_ARRAY,
                ...$options,
            ]
         : self::DEFAULT_OPTIONS_ARRAY;

        return $this;
    }

    /**
     * Generates the PDF output.
     *
     * @param  string      $filename
     * @param  null|string $dir
     * @return string
     */
    public function generate(): string
    {
        $this->initAdditionalRenderers();

        $this->rendered = true;

        return $this
            ->getDomPdf()
            ->output();
    }

    /**
     * Shows the PDF in the browser.
     *
     * @return Response
     */
    public function stream(): Response
    {
        $this->rendered = true;

        return $this
            ->getDomPdf()
            ->stream('test.pdf');
    }

    /**
     * Get the value of domPdf.
     *
     * @return DomPdf
     */
    public function getDomPdf(): DomPdf
    {
        return $this->domPdf;
    }

    /**
     * Set the value of domPdf.
     *
     * @param  DomPdf $domPdf
     * @return self
     */
    public function setDomPdf(DomPdf $domPdf): self
    {
        $this->domPdf = $domPdf;

        return $this;
    }

    /**
     * Init all additional renderers.
     *
     * @return void
     */
    private function initAdditionalRenderers(): void
    {
        $this
            ->initAdditionalTextRenderers()
            ->initAdditionalImageRenderers();
    }

    /**
     * Init text renderers.
     *
     * @return self
     */
    private function initAdditionalTextRenderers(): self
    {
        $this->addPaginatorTextRenderer();
        $textRenderers = $this->getCanvasTextRenderers();

        if ($textRenderers->isEmpty()) {
            return $this;
        }

        $this->render();
        $domPdf = $this->getDomPdf();
        $canvas = $domPdf->getCanvas();

        foreach ($textRenderers as $textRenderer) {
            $texRendererArray = $textRenderer->toArray();
            if ($textRenderer->isOnEveryPage) {
                $canvas->page_text(...$texRendererArray);
            } else {
                $canvas->text(...$texRendererArray);
            }
        }

        $this->setDomPdf($domPdf);

        return $this;
    }

    /**
     * Add paginator text renderer.
     *
     * @return self
     */
    private function addPaginatorTextRenderer(): self
    {
        $paginator = $this->getPaginator();
        if (! $paginator) {
            return $this;
        }

        $textRenderers = $this->getCanvasTextRenderers();

        $textRenderers->push($this->getPaginator());

        return $this;
    }

    /**
     * Init the additional images.
     *
     * @return self
     */
    private function initAdditionalImageRenderers(): self
    {
        $imageRenderers = $this->getCanvasImageRenderers();

        if ($imageRenderers->isEmpty()) {
            return $this;
        }

        $this->render();
        $domPdf = $this->getDomPdf();
        $canvas = $domPdf->getCanvas();

        foreach ($imageRenderers as $imageRenderer) {
            $imageRendererArray = $imageRenderer->toArray();
            if ($imageRenderer->isOnEveryPage) {
                $canvas->page_script(function (
                    int $PAGE_NUM,
                    int $PAGE_COUNT,
                    Canvas $canvas,
                    FontMetrics $fontMetrics
                ) use ($imageRendererArray) {
                    $canvas->image(...$imageRendererArray);
                });
            } else {
                $canvas->image(...$imageRendererArray);
            }
        }

        $this->setDomPdf($domPdf);

        return $this;
    }

    /**
     * Renders the pdf if not rendered.
     *
     * @return void
     */
    private function render()
    {
        $domPdf = $this->getDomPdf();

        if (! $this->rendered) {
            $domPdf->render();
        }

        return $this;
    }

    /**
     * Get the value of canvasTextRenderers.
     *
     * @return ?Collection<CanvasTextRenderer>
     */
    public function getCanvasTextRenderers(): ?Collection
    {
        return $this->canvasTextRenderers;
    }

    /**
     * Set the value of canvasTextRenderers.
     *
     * @param  ?Collection<CanvasTextRenderer> $canvasTextRenderers
     * @return self
     */
    public function setCanvasTextRenderers(?Collection $canvasTextRenderers = null): self
    {
        $this->canvasTextRenderers = $canvasTextRenderers;

        return $this;
    }

    /**
     * Get the value of canvasImageRenderers.
     *
     * @return ?Collection<CanvasImageRenderer>
     */
    public function getCanvasImageRenderers(): ?Collection
    {
        return $this->canvasImageRenderers;
    }

    /**
     * Set the value of canvasImageRenderers.
     *
     * @param  Collection<CanvasImageRenderer> $canvasImageRenderers
     * @return self
     */
    public function setCanvasImageRenderers(Collection $canvasImageRenderers): self
    {
        $this->canvasImageRenderers = $canvasImageRenderers;

        return $this;
    }

    /**
     * Get the value of paginator.
     *
     * @return ?Paginator
     */
    public function getPaginator(): ?Paginator
    {
        return $this->paginator;
    }

    /**
     * Set the value of paginator.
     *
     * @param  ?Paginator $paginator
     * @return self
     */
    public function setPaginator(?Paginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }
}

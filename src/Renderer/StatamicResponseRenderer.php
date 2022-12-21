<?php

declare(strict_types=1);

/**
 * Copyright (c) 2022 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/laravel-arcanist/arcanist
 */

namespace Arcanist\Renderer;

use Arcanist\AbstractWizard;
use Arcanist\Contracts\ResponseRenderer;
use Arcanist\Exception\StepTemplateNotFoundException;
use Arcanist\WizardStep;
use function redirect;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\View\Factory;
use InvalidArgumentException;
use Statamic\View\View;
use Symfony\Component\HttpFoundation\Response;

class StatamicResponseRenderer implements ResponseRenderer
{
    public function __construct(private Factory $factory, private string $viewBasePath)
    {
    }

    public function renderStep(
        WizardStep $step,
        AbstractWizard $wizard,
        array $data = [],
    ): Responsable|Response|Renderable|View {
        $viewName = $this->viewBasePath . '.' . $wizard::$slug . '.' . $step->slug;

        try {
            return (new View)
                ->template($viewName)
                ->with([
                    'wizard' => $wizard->summary(),
                    'step' => $data,
                ]);
        } catch (InvalidArgumentException) {
            throw StepTemplateNotFoundException::forStep($step);
        }
    }

    public function redirect(WizardStep $step, AbstractWizard $wizard): Response|Renderable|Responsable
    {
        if (!$wizard->exists()) {
            return redirect()->route('wizard.' . $wizard::$slug . '.create');
        }

        return redirect()->route('wizard.' . $wizard::$slug . '.show', [
            $wizard->getId(), $step->slug,
        ]);
    }

    public function redirectWithError(
        WizardStep $step,
        AbstractWizard $wizard,
        ?string $error = null,
    ): Response|Renderable|Responsable {
        return redirect()
            ->route('wizard.' . $wizard::$slug . '.show', [
                $wizard->getId(),
                $step->slug,
            ])
            ->withErrors(['wizard' => $error]);
    }
}

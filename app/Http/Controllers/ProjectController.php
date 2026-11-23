<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MarkdownRenderer;
use App\Services\ProjectCatalog;
use App\Services\ServiceCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final readonly class ProjectController
{
    public function __construct(
        private ProjectCatalog $projects,
        private ServiceCatalog $services,
        private MarkdownRenderer $markdown,
    ) {}

    public function index(Request $request): View
    {
        $directions = $this->services->directions();
        $slug = $request->string('rabota')->toString();
        // Неизвестный фильтр — не ошибка, а «все объекты».
        $active = $directions->firstWhere('slug', $slug);

        return view('projects.index', [
            'directions' => $directions,
            'active' => $active,
            'projects' => $this->projects->list($active),
        ]);
    }

    public function show(string $slug): View
    {
        $project = $this->projects->findPublished($slug);

        abort_if($project === null, 404);

        return view('projects.show', [
            'project' => $project,
            'bodyHtml' => $this->markdown->toHtml($project->body),
            'directions' => $this->services->directions(),
        ]);
    }
}

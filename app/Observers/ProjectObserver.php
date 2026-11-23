<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Project;
use App\Services\ProjectPhotos;

/**
 * Не оставлять на диске фото, которые убрали из объекта или вместе с ним.
 */
final readonly class ProjectObserver
{
    public function __construct(
        private ProjectPhotos $photos,
    ) {}

    public function updated(Project $project): void
    {
        if (! $project->wasChanged('photos')) {
            return;
        }

        $before = $this->paths($project->getOriginal('photos'));
        $this->photos->delete(array_values(array_diff($before, $project->photos)));
    }

    public function deleted(Project $project): void
    {
        $this->photos->delete($project->photos);
    }

    /**
     * @return list<string>
     */
    private function paths(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\IndexNow;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Сообщить Яндексу о новых страницах. Запускает deploy/update.sh после
 * каждого обновления сайта: `php artisan seo:indexnow`.
 */
final class SubmitIndexNow extends Command
{
    protected $signature = 'seo:indexnow {--dry-run : Только показать, что ушло бы}';

    protected $description = 'Отправить Яндексу новые страницы сайта через IndexNow';

    public function __construct(private readonly IndexNow $indexNow)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->indexNow->key() === null) {
            $this->warn('IndexNow выключен: в .env нет ключа SITE_INDEXNOW_KEY.');

            return self::SUCCESS;
        }

        $pending = $this->indexNow->pending();

        if ($pending === []) {
            $this->info('Новых страниц нет — Яндексу отправлять нечего.');

            return self::SUCCESS;
        }

        if ((bool) $this->option('dry-run')) {
            foreach ($pending as $url) {
                $this->line($url);
            }

            return self::SUCCESS;
        }

        try {
            $sent = $this->indexNow->submitPending();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Яндекс получил новые страницы: %d.', $sent));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AdventureWorld;
use App\Services\Content\ContentPackBuilder;
use Illuminate\Console\Command;

class BuildContentPack extends Command
{
    protected $signature = 'content:build-pack
                            {world? : Adventure world id or slug}
                            {--all : Publish every world}
                            {--level= : Only worlds for this level code (PG, PP1, PP2)}';

    protected $description = 'Publish an adventure world as a versioned content pack for the app';

    public function handle(ContentPackBuilder $builder): int
    {
        $worlds = $this->targets();

        if ($worlds->isEmpty()) {
            $this->error('No matching adventure worlds found.');

            return self::FAILURE;
        }

        $rows = [];
        $missing = [];

        foreach ($worlds as $world) {
            $pack = $builder->publish($world);

            foreach ($pack->missing_media ?? [] as $entry) {
                $missing[] = $pack->pack_id . '  ' . ($entry['kind'] ?? '?') . '  ' . ($entry['url'] ?? '');
            }

            $rows[] = [
                $pack->pack_id,
                'v' . $pack->version,
                $pack->level_code ?: 'all',
                $pack->mission_count,
                $pack->question_count,
                $pack->media_count,
                $this->humanBytes($pack->bytes_core),
                $this->humanBytes($pack->bytes_video),
                count($pack->missing_media ?? []),
            ];
        }

        $this->table(
            ['pack', 'version', 'level', 'missions', 'questions', 'media', 'core', 'video', 'missing'],
            $rows
        );

        if ($missing !== []) {
            $this->warn(count($missing) . ' media reference(s) could not be resolved. Run with -v to list them.');

            if ($this->output->isVerbose()) {
                foreach ($missing as $line) {
                    $this->line('  ' . $line);
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int,AdventureWorld>
     */
    protected function targets()
    {
        $query = AdventureWorld::with('subject.level')->orderBy('sort_order');

        if ($this->option('all') || $this->option('level')) {
            if ($level = $this->option('level')) {
                $query->whereHas('subject.level', fn ($q) => $q->where('code', strtoupper($level)));
            }

            return $query->get();
        }

        $identifier = $this->argument('world');

        if (! $identifier) {
            $this->error('Pass a world id or slug, or use --all.');

            return collect();
        }

        return AdventureWorld::with('subject.level')
            ->where('id', is_numeric($identifier) ? (int) $identifier : 0)
            ->orWhere('slug', $identifier)
            ->get();
    }

    protected function humanBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }

        return $bytes . ' B';
    }
}

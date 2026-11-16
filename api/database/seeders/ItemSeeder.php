<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $directory = base_path('../data/curriculum');

        if (! File::isDirectory($directory)) {
            throw new RuntimeException("Curriculum directory not found at [{$directory}].");
        }

        $files = array_values(array_filter(
            File::allFiles($directory),
            fn ($file) => strtolower($file->getExtension()) === 'yaml',
        ));

        if ($files === []) {
            throw new RuntimeException("No YAML curriculum files found in [{$directory}].");
        }

        foreach ($files as $file) {
            $item = Yaml::parseFile($file->getPathname());

            if (
                ! is_array($item)
                || ! is_string($item['id'] ?? null)
                || ! is_string($item['domain'] ?? null)
                || ! is_string($item['level'] ?? null)
                || (isset($item['type']) && ! in_array($item['type'], ['multiple-choice', 'essay'], true))
                || (($item['type'] ?? null) === 'essay' && ! is_string($item['question'] ?? null))
            ) {
                throw new RuntimeException("Invalid item YAML structure in [{$file->getPathname()}].");
            }

            DB::table('items')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'domain' => $item['domain'],
                    'level' => $item['level'],
                    'payload' => json_encode($item, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}

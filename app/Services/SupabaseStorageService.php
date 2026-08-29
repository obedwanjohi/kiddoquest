<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected string $projectRef;
    protected string $serviceKey;
    protected string $bucket;
    protected string $baseUrl;

    public function __construct()
    {
        $this->projectRef = config('services.supabase.project_ref', env('SUPABASE_PROJECT_REF', 'hxxxmizzuddcxmufrsbr'));
        $this->serviceKey = config('services.supabase.service_key', env('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imh4eHhtaXp6dWRkY3htdWZyc2JyIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4NzkxOTg2NywiZXhwIjoyMTAzNDk1ODY3fQ.bN4sJ5kxbc5OGb4J729FXKbQKxrK6j9SBBgt7kGMJWg'));
        $this->bucket = config('services.supabase.bucket', env('SUPABASE_STORAGE_BUCKET', 'media'));
        $this->baseUrl = "https://{$this->projectRef}.supabase.co/storage/v1";
    }

    /**
     * Upload raw file contents to Supabase Storage bucket.
     */
    public function uploadFile(string $remotePath, string $contents, string $mimeType = 'application/octet-stream'): ?string
    {
        $url = "{$this->baseUrl}/object/{$this->bucket}/" . ltrim($remotePath, '/');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'apikey'        => $this->serviceKey,
                'Content-Type'  => $mimeType,
                'x-upsert'      => 'true',
            ])->withBody($contents, $mimeType)->post($url);

            if ($response->successful()) {
                return $this->getPublicUrl($remotePath);
            }

            Log::error('Supabase Storage Upload Failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'path'   => $remotePath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Supabase Storage Upload Exception', [
                'message' => $e->getMessage(),
                'path'    => $remotePath,
            ]);
        }

        return null;
    }

    /**
     * Get the public URL for a file in Supabase Storage.
     */
    public function getPublicUrl(string $remotePath): string
    {
        if (str_starts_with($remotePath, 'http://') || str_starts_with($remotePath, 'https://')) {
            return $remotePath;
        }

        return "{$this->baseUrl}/object/public/{$this->bucket}/" . ltrim($remotePath, '/');
    }
}

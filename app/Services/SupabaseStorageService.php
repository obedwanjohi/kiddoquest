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
        // All values come from config/services.php (-> .env). The service-role key was
        // previously hard-coded here; it must be rotated in the Supabase dashboard.
        $this->projectRef = (string) config('services.supabase.project_ref', '');
        $this->serviceKey = (string) config('services.supabase.service_key', '');
        $this->bucket = (string) config('services.supabase.bucket', 'media');
        $this->baseUrl = "https://{$this->projectRef}.supabase.co/storage/v1";
    }

    /**
     * Upload raw file contents to Supabase Storage bucket.
     */
    public function uploadFile(string $remotePath, string $contents, string $mimeType = 'application/octet-stream'): ?string
    {
        if ($this->serviceKey === '' || $this->projectRef === '') {
            Log::error('Supabase Storage upload skipped: SUPABASE_PROJECT_REF / SUPABASE_SERVICE_KEY are not configured.', ['path' => $remotePath]);

            return null;
        }

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

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected string $projectUrl;
    protected string $serviceRoleKey;

    public function __construct()
    {
        $this->projectUrl = env('SUPABASE_URL'); // e.g. https://your-project.supabase.co
        $this->serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    protected function headers(): array
    {
        return [
            'apikey' => $this->serviceRoleKey,
            'Authorization' => 'Bearer ' . $this->serviceRoleKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * List files in a bucket
     */
    public function listFiles(string $bucket, string $prefix = ''): array
    {
        // Fixed URL - removed '/bucket' from path
        $url = "{$this->projectUrl}/storage/v1/object/list/{$bucket}";

        // Always include prefix, even if empty
        $body = [
            'prefix' => $prefix
        ];

        // Use POST with body instead of GET with query params
        $response = Http::withHeaders($this->headers())
            ->post($url, $body);

        if ($response->failed()) {
            throw new \Exception("Supabase list files failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Upload a file to a bucket
     */
    public function uploadFile(string $bucket, string $path, string $filePath, bool $upsert = true): array
    {
        // Fixed URL - removed 's' from 'buckets'
        $url = "{$this->projectUrl}/storage/v1/object/{$bucket}/{$path}";

        // Read file contents
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $contents = file_get_contents($filePath);
        
        // Get file mime type
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $headers = [
            'apikey' => $this->serviceRoleKey,
            'Authorization' => 'Bearer ' . $this->serviceRoleKey,
            'Content-Type' => $mimeType,
        ];

        // Add upsert header if needed
        if ($upsert) {
            $headers['x-upsert'] = 'true';
        }

        $response = Http::withHeaders($headers)
            ->withBody($contents)
            ->post($url);

        if ($response->failed()) {
            throw new \Exception("Supabase upload failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Delete a file from a bucket
     */
    public function deleteFile(string $bucket, string $path): array
    {
        // Fixed URL - removed 's' from 'buckets'
        $url = "{$this->projectUrl}/storage/v1/object/{$bucket}/{$path}";

        $response = Http::withHeaders($this->headers())->delete($url);

        if ($response->failed()) {
            throw new \Exception("Supabase delete failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Get public URL for a file
     */
    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->projectUrl}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /**
     * Get signed URL for private files
     */
    public function getSignedUrl(string $bucket, string $path, int $expiresIn = 3600): array
    {
        $url = "{$this->projectUrl}/storage/v1/object/sign/{$bucket}/{$path}";

        $response = Http::withHeaders($this->headers())
            ->post($url, [
                'expiresIn' => $expiresIn
            ]);

        if ($response->failed()) {
            throw new \Exception("Supabase signed URL failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Create a bucket
     */
    public function createBucket(string $name, bool $public = false): array
    {
        $url = "{$this->projectUrl}/storage/v1/bucket";

        $response = Http::withHeaders($this->headers())
            ->post($url, [
                'name' => $name,
                'public' => $public
            ]);

        if ($response->failed()) {
            throw new \Exception("Supabase create bucket failed: " . $response->body());
        }

        return $response->json();
    }
}
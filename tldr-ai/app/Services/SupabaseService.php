<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_KEY');
        $this->bucket = env('SUPABASE_STORAGE_BUCKET');
    }

    /**
     * Upload a file to Supabase Storage
     *
     * @param string $filePath Local file path
     * @param string $fileName Name to save as in Supabase
     * @return array Response JSON
     */
    public function uploadFile($filePath, $fileName)
    {
        $fileContents = file_get_contents($filePath);

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key
        ])->attach(
            'file', $fileContents, $fileName
        )->post("{$this->url}/storage/v1/object/{$this->bucket}/$fileName");

        return $response->json();
    }

    /**
     * Get a public URL for a file in Supabase Storage
     *
     * @param string $fileName
     * @return string
     */
    public function getFileUrl($fileName)
    {
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/$fileName";
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    protected $supabase;
    protected $hfToken;
    protected string $bucket = 'documents'; // default bucket

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
        $this->hfToken = env('HUGGINGFACE_API_KEY');
    }

    // Show dashboard + list files
    public function index()
    {
        // Pass the bucket name
        $files = $this->supabase->listFiles($this->bucket);

        // Generate summary for each file
        foreach ($files as &$file) {
            $file['summary'] = $this->summarizeFile($file['name']);
        }

        return view('dashboard', compact('files'));
    }

    // Handle file upload
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:10240',
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Upload file: bucket, path, contents
        $this->supabase->uploadFile(
            $this->bucket,
            $fileName,
            file_get_contents($file->getPathname())
        );

        // Get public URL
        $url = $this->supabase->getPublicUrl($this->bucket, $fileName);

        // Optional: summarize via Hugging Face
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->hfToken,
        ])->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
            'inputs' => file_get_contents($file->getPathname())
        ]);

        $summary = $response->json()[0]['summary_text'] ?? 'No summary available.';

        $uploadedFile = [
            'name' => $fileName,
            'url' => $url,
            'summary' => $summary
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'file' => $uploadedFile,
                'message' => 'File uploaded and summarized successfully.'
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'File uploaded! Public URL: ' . $url);
    }

    protected function summarizeFile($fileName)
    {
        $url = $this->supabase->getPublicUrl($this->bucket, $fileName);
        $content = file_get_contents($url);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->hfToken,
        ])->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
            'inputs' => $content
        ]);

        $result = $response->json();
        return $result[0]['summary_text'] ?? 'Could not generate summary';
    }
}

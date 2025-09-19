<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        try {
            // Get files from Supabase
            $files = $this->supabase->listFiles($this->bucket);
            
            // Process files WITHOUT generating summaries (to avoid timeout)
            $processedFiles = [];
            foreach ($files as $file) {
                $processedFiles[] = [
                    'name' => $file['name'],
                    'url' => $this->supabase->getPublicUrl($this->bucket, $file['name']),
                    'summary' => 'Click to generate summary', // Placeholder, can be generated on-demand
                    'size' => $file['metadata']['size'] ?? 'Unknown',
                    'updated_at' => $file['updated_at'] ?? 'Unknown'
                ];
            }

            return view('dashboard', ['files' => $processedFiles]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard index error: ' . $e->getMessage());
            return view('dashboard', ['files' => []]);
        }
    }

    // Handle file upload
    public function upload(Request $request)
    {
        // Force JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                // Validate the uploaded file
                $request->validate([
                    'document' => 'required|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png',
                ]);

                $file = $request->file('document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Get the actual file path (not contents)
                $filePath = $file->getPathname();

                // Upload file to Supabase using file path
                $uploadResult = $this->supabase->uploadFile(
                    $this->bucket,
                    $fileName,
                    $filePath  // Pass the file path, not contents
                );

                // Get public URL
                $url = $this->supabase->getPublicUrl($this->bucket, $fileName);

                // Generate summary with fallback options
                $summary = $this->generateSummaryWithFallback($filePath, $file->getClientMimeType());

                $uploadedFile = [
                    'name' => $fileName,
                    'url' => $url,
                    'summary' => $summary
                ];

                return response()->json([
                    'success' => true,
                    'file' => $uploadedFile,
                    'message' => 'File uploaded successfully!'
                ]);
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
                
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage()
                ], 500);
            }
        }

        // Handle non-AJAX requests
        try {
            $request->validate([
                'document' => 'required|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png',
            ]);

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->getPathname();

            $this->supabase->uploadFile($this->bucket, $fileName, $filePath);
            $url = $this->supabase->getPublicUrl($this->bucket, $fileName);

            return redirect()->route('dashboard')->with('success', 'File uploaded successfully!');
            
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate summary with fallback options
     */
    protected function generateSummaryWithFallback(string $filePath, string $mimeType): string
    {
        // First, try quick AI summary if API is configured
        if (!empty($this->hfToken)) {
            try {
                return $this->generateSummaryQuick($filePath, $mimeType);
            } catch (\Exception $e) {
                Log::info('AI summary failed, falling back to text preview: ' . $e->getMessage());
            }
        }
        
        // Fallback to simple text preview
        return $this->generateSimplePreview($filePath, $mimeType);
    }

    /**
     * Generate simple text preview as fallback
     */
    protected function generateSimplePreview(string $filePath, string $mimeType): string
    {
        if (!$this->canSummarize($mimeType)) {
            return 'Preview not available for this file type (' . $mimeType . ')';
        }

        $content = file_get_contents($filePath);
        
        if (strpos($mimeType, 'text/') === 0) {
            // Clean text and get first few sentences
            $content = trim($content);
            $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            if (count($sentences) > 0) {
                // Take first 2-3 sentences, max 200 chars
                $preview = implode('. ', array_slice($sentences, 0, 3));
                $preview = substr($preview, 0, 200);
                
                if (strlen($preview) < strlen($content)) {
                    $preview .= '...';
                }
                
                return 'Text preview: ' . $preview;
            }
        }
        
        // Basic info fallback
        $fileSize = filesize($filePath);
        $readableSize = $this->formatBytes($fileSize);
        
        return "File uploaded successfully. Size: {$readableSize}. AI summary temporarily unavailable.";
    }

    /**
     * Generate summary with shorter timeout for immediate response
     */
    protected function generateSummaryQuick(string $filePath, string $mimeType): string
    {
        // Check if we can summarize this file type
        if (!$this->canSummarize($mimeType)) {
            throw new \Exception('Cannot summarize this file type');
        }

        $content = file_get_contents($filePath);
        
        // Clean and limit content for text files
        if (strpos($mimeType, 'text/') === 0) {
            $content = substr(trim($content), 0, 300); // Shorter for quick processing
        } else {
            throw new \Exception('Non-text file, skip AI summary');
        }
        
        // Skip if content is too short
        if (strlen(trim($content)) < 20) {
            throw new \Exception('Content too short');
        }

        // Much shorter timeout for quick response
        $response = Http::timeout(8)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->hfToken,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
                'inputs' => $content
            ]);

        if ($response->successful()) {
            $result = $response->json();
            
            if (isset($result[0]['summary_text'])) {
                return 'AI Summary: ' . $result[0]['summary_text'];
            } elseif (isset($result['error'])) {
                // Model might be loading
                if (strpos($result['error'], 'loading') !== false) {
                    throw new \Exception('Model loading');
                }
                throw new \Exception('API error: ' . $result['error']);
            }
        }
        
        throw new \Exception('API request failed');
    }

    /**
     * Generate summary for specific file (AJAX endpoint)
     */
    public function generateSummary(Request $request)
    {
        try {
            $fileName = $request->input('file_name');
            
            if (!$fileName) {
                return response()->json([
                    'success' => false,
                    'message' => 'File name is required'
                ], 400);
            }

            // Generate summary for the file
            $summary = $this->summarizeFile($fileName);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
            
        } catch (\Exception $e) {
            Log::error('Summary generation error for ' . ($fileName ?? 'unknown') . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format file size in human readable format
     */
    protected function formatBytes($size, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Summarize existing file (for dashboard display)
     */
    protected function summarizeFile($fileName): string
    {
        try {
            $url = $this->supabase->getPublicUrl($this->bucket, $fileName);
            
            // Try to get content from public URL with short timeout
            $response = Http::timeout(5)->get($url);
            
            if (!$response->successful()) {
                return 'Could not access file for summary';
            }
            
            $content = $response->body();
            
            // If we have HuggingFace token, try AI summary with very short timeout
            if (!empty($this->hfToken)) {
                try {
                    $content = substr(trim($content), 0, 300); // Limit content
                    
                    if (strlen($content) > 20) {
                        $summaryResponse = Http::timeout(5) // Very short timeout
                            ->withHeaders([
                                'Authorization' => 'Bearer ' . $this->hfToken,
                                'Content-Type' => 'application/json',
                            ])
                            ->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
                                'inputs' => $content
                            ]);

                        if ($summaryResponse->successful()) {
                            $result = $summaryResponse->json();
                            if (isset($result[0]['summary_text'])) {
                                return 'AI Summary: ' . $result[0]['summary_text'];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fall through to text preview
                }
            }
            
            // Fallback to text preview
            $content = trim($content);
            if (strlen($content) > 50) {
                $preview = substr($content, 0, 150);
                if (strlen($preview) < strlen($content)) {
                    $preview .= '...';
                }
                return 'Text preview: ' . $preview;
            }
            
            return 'File content is too short to preview.';
            
        } catch (\Exception $e) {
            Log::error('File summary error: ' . $e->getMessage());
            return 'Summary generation failed. Click to retry.';
        }
    }

    /**
     * Check if file type can be summarized
     */
    private function canSummarize(string $mimeType): bool
    {
        $textMimes = [
            'text/plain',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        return in_array($mimeType, $textMimes);
    }
}
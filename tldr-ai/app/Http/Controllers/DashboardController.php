<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $supabase;
    protected $hfToken;
    protected string $bucket = 'documents'; // default bucket

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
        $this->hfToken = env('HUGGINGFACE_API_KEY');
        
        // Debug logging
        Log::info('DashboardController initialized');
        Log::info('HuggingFace token loaded: ' . (!empty($this->hfToken) ? 'YES' : 'NO'));
        if (!empty($this->hfToken)) {
            Log::info('HuggingFace token length: ' . strlen($this->hfToken));
            Log::info('HuggingFace token starts with: ' . substr($this->hfToken, 0, 10));
        }
    }

    // Show dashboard + list files
    public function index()
    {
        try {
            // Get user's documents from database - using direct query to avoid IDE issues
            $documents = Document::where('user_id', Auth::id())->latest()->get();
            
            // Convert to array format for the view
            $files = $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->stored_filename,
                    'original_name' => $doc->filename,
                    'url' => $doc->public_url,
                    'summary' => $doc->display_summary,
                    'size' => $doc->formatted_size,
                    'updated_at' => $doc->updated_at->format('M d, Y H:i'),
                    'summary_generated' => $doc->summary_generated,
                ];
            })->toArray();

            return view('dashboard', ['files' => $files]);
            
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
                $originalName = $file->getClientOriginalName();
                $storedName = time() . '_' . $originalName;
                $filePath = $file->getPathname();
                $mimeType = $file->getClientMimeType();
                $fileSize = $file->getSize();

                // Upload file to Supabase with better error handling
                try {
                    Log::info('Attempting to upload file: ' . $storedName . ' to bucket: ' . $this->bucket);
                    Log::info('File path: ' . $filePath . ' exists: ' . (file_exists($filePath) ? 'yes' : 'no'));
                    
                    $uploadResult = $this->supabase->uploadFile(
                        $this->bucket,
                        $storedName,
                        $filePath
                    );
                    
                    Log::info('Upload result: ' . json_encode($uploadResult));
                } catch (\Exception $e) {
                    Log::error('Supabase upload failed: ' . $e->getMessage());
                    throw new \Exception('Failed to upload file to storage: ' . $e->getMessage());
                }

                // Get public URL
                $publicUrl = $this->supabase->getPublicUrl($this->bucket, $storedName);
                
                // Log for debugging
                Log::info('File uploaded to Supabase: ' . $storedName);
                Log::info('Public URL: ' . $publicUrl);
                
                // Verify the upload by checking if the file exists
                try {
                    $testResponse = Http::timeout(5)->get($publicUrl);
                    Log::info('File verification - URL accessible: ' . ($testResponse->successful() ? 'yes' : 'no'));
                    Log::info('File verification - Response status: ' . $testResponse->status());
                } catch (\Exception $e) {
                    Log::warning('Could not verify file upload: ' . $e->getMessage());
                }

                // Create database record
                $document = Document::create([
                    'user_id' => Auth::id(),
                    'filename' => $originalName,
                    'stored_filename' => $storedName,
                    'file_path' => $storedName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'public_url' => $publicUrl,
                    'summary_generated' => false,
                ]);

                // Try to generate summary immediately (with short timeout)
                $summary = $this->generateAndSaveSummary($document);

                return response()->json([
                    'success' => true,
                    'file' => [
                        'id' => $document->id,
                        'name' => $storedName,
                        'original_name' => $originalName,
                        'url' => $publicUrl,
                        'summary' => $summary,
                        'size' => $document->formatted_size,
                        'summary_generated' => $document->summary_generated,
                    ],
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
            $originalName = $file->getClientOriginalName();
            $storedName = time() . '_' . $originalName;
            $filePath = $file->getPathname();

            $this->supabase->uploadFile($this->bucket, $storedName, $filePath);
            $publicUrl = $this->supabase->getPublicUrl($this->bucket, $storedName);

            // Create database record
            Document::create([
                'user_id' => Auth::id(),
                'filename' => $originalName,
                'stored_filename' => $storedName,
                'file_path' => $storedName,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'public_url' => $publicUrl,
                'summary_generated' => false,
            ]);

            return redirect()->route('dashboard')->with('success', 'File uploaded successfully!');
            
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate summary for specific document (AJAX endpoint)
     */
    public function generateSummary(Request $request)
    {
        try {
            $documentId = $request->input('document_id');
            
            if (!$documentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID is required'
                ], 400);
            }

            // Find the document (ensure user owns it)
            $document = Document::where('id', $documentId)
                              ->where('user_id', Auth::id())
                              ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // If summary already exists, return it
            if ($document->summary_generated && $document->summary) {
                return response()->json([
                    'success' => true,
                    'summary' => $document->summary
                ]);
            }

            // Generate and save summary
            $summary = $this->generateAndSaveSummary($document);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
            
        } catch (\Exception $e) {
            Log::error('Summary generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate summary and save to database
     */
    protected function generateAndSaveSummary(Document $document): string
    {
        try {
            // Generate summary using HuggingFace first, then fallback
            $summary = $this->generateSummaryWithFallback($document->public_url, $document->mime_type);
            
            // Save to database
            $document->update([
                'summary' => $summary,
                'summary_generated' => true,
            ]);
            
            Log::info('Summary saved for document ' . $document->id . ': ' . substr($summary, 0, 100) . '...');
            
            return $summary;
            
        } catch (\Exception $e) {
            Log::error('Summary generation failed for document ' . $document->id . ': ' . $e->getMessage());
            
            // Save fallback summary
            $fallbackSummary = 'Summary generation failed. File: ' . $document->filename;
            $document->update([
                'summary' => $fallbackSummary,
                'summary_generated' => true,
            ]);
            
            return $fallbackSummary;
        }
    }

    /**
     * Generate summary with fallback options (FIXED VERSION)
     */
    protected function generateSummaryWithFallback(string $fileUrl, string $mimeType): string
    {
        Log::info('=== SUMMARY GENERATION START ===');
        Log::info('File URL: ' . $fileUrl);
        Log::info('MIME type: ' . $mimeType);
        Log::info('HuggingFace token empty check: ' . (empty($this->hfToken) ? 'EMPTY' : 'HAS_VALUE'));
        Log::info('HuggingFace token length: ' . strlen($this->hfToken ?? ''));
        
        // First, try AI summary if API is configured
        if (!empty($this->hfToken)) {
            Log::info('HuggingFace token detected, attempting AI summary...');
            try {
                $aiSummary = $this->generateSummaryFromUrl($fileUrl, $mimeType);
                Log::info('AI summary successful: ' . substr($aiSummary, 0, 100) . '...');
                return $aiSummary;
            } catch (\Exception $e) {
                Log::warning('AI summary failed: ' . $e->getMessage());
                Log::warning('Falling back to text preview');
            }
        } else {
            Log::warning('HuggingFace token is empty - skipping AI summary');
            Log::warning('Token value (first 20 chars): "' . substr($this->hfToken ?? '', 0, 20) . '"');
        }
        
        // Fallback to simple text preview
        Log::info('Using fallback text preview');
        $preview = $this->generateSimplePreviewFromUrl($fileUrl, $mimeType);
        Log::info('Fallback preview generated: ' . substr($preview, 0, 100) . '...');
        Log::info('=== SUMMARY GENERATION END ===');
        return $preview;
    }

    /**
     * Generate AI summary from file URL (IMPROVED WITH PDF SUPPORT)
     */
    protected function generateSummaryFromUrl(string $fileUrl, string $mimeType): string
    {
        if (!$this->canSummarize($mimeType)) {
            throw new \Exception('Cannot summarize this file type: ' . $mimeType);
        }

        Log::info('Fetching file content from: ' . $fileUrl);
        
        // Get file content
        $response = Http::timeout(10)->get($fileUrl);
        if (!$response->successful()) {
            throw new \Exception('Could not fetch file content, HTTP status: ' . $response->status());
        }

        $rawContent = $response->body();
        Log::info('File content fetched, length: ' . strlen($rawContent) . ' bytes');
        
        // Extract text based on file type
        $content = $this->extractTextContent($rawContent, $mimeType);
        
        // Skip if content is too short
        if (strlen(trim($content)) < 50) {
            throw new \Exception('Content too short for meaningful summary: ' . strlen($content) . ' characters');
        }

        // Try multiple models/approaches
        $models = [
            'facebook/bart-large-cnn',
            'sshleifer/distilbart-cnn-12-6',
            'google/pegasus-xsum'
        ];

        foreach ($models as $index => $model) {
            try {
                Log::info('Trying model #' . ($index + 1) . ': ' . $model);
                
                // Test if content can be JSON encoded before sending
                $testJson = json_encode(['inputs' => $content]);
                if ($testJson === false) {
                    throw new \Exception('Content cannot be JSON encoded: ' . json_last_error_msg());
                }
                
                $timeout = ($index === 0) ? 15 : 25;
                $response = Http::timeout($timeout)
                    ->retry(2, 1000)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->hfToken,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://api-inference.huggingface.co/models/' . $model, [
                        'inputs' => $content,
                        'options' => [
                            'wait_for_model' => true,
                            'use_cache' => false
                        ]
                    ]);

                Log::info('Model ' . $model . ' response status: ' . $response->status());
                
                if ($response->successful()) {
                    $result = $response->json();
                    Log::info('Model ' . $model . ' response: ' . json_encode($result));
                    
                    if (isset($result[0]['summary_text'])) {
                        $summary = trim($result[0]['summary_text']);
                        Log::info('SUCCESS with model ' . $model);
                        return 'AI Summary: ' . $summary;
                    } elseif (isset($result['error'])) {
                        $error = $result['error'];
                        Log::warning('Model ' . $model . ' returned error: ' . $error);
                        
                        if (strpos(strtolower($error), 'loading') !== false && $index < count($models) - 1) {
                            Log::info('Model loading, trying next model...');
                            continue;
                        }
                        throw new \Exception('HuggingFace API error: ' . $error);
                    }
                } else {
                    Log::warning('Model ' . $model . ' HTTP error: ' . $response->status() . ' - ' . $response->body());
                    if ($index < count($models) - 1) {
                        continue;
                    }
                }
                
            } catch (\Exception $e) {
                Log::warning('Model ' . $model . ' failed: ' . $e->getMessage());
                if ($index < count($models) - 1) {
                    continue;
                }
                throw $e;
            }
        }

        throw new \Exception('All HuggingFace models failed or timed out');
    }

    /**
     * Extract text content from different file types
     */
    protected function extractTextContent(string $rawContent, string $mimeType): string
    {
        Log::info('Extracting text from MIME type: ' . $mimeType);
        
        switch ($mimeType) {
            case 'application/pdf':
                return $this->extractPdfText($rawContent);
            
            case 'text/plain':
                return $this->cleanTextContent($rawContent);
            
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                // For now, treat as binary and try to extract readable parts
                return $this->extractReadableText($rawContent);
            
            default:
                if (strpos($mimeType, 'text/') === 0) {
                    return $this->cleanTextContent($rawContent);
                }
                throw new \Exception('Unsupported file type for text extraction: ' . $mimeType);
        }
    }

    /**
     * Extract text from PDF (basic approach)
     */
    protected function extractPdfText(string $pdfContent): string
    {
        Log::info('Attempting PDF text extraction');
        
        // Basic PDF text extraction using regex patterns
        // This is a simple approach - for production, consider using smalot/pdfparser package
        
        $text = '';
        
        // Look for text objects in PDF
        if (preg_match_all('/\((.*?)\)/', $pdfContent, $matches)) {
            foreach ($matches[1] as $match) {
                // Clean up the extracted text
                $cleanText = $this->cleanPdfText($match);
                if (strlen($cleanText) > 2) {
                    $text .= $cleanText . ' ';
                }
            }
        }
        
        // Alternative: look for text between BT and ET markers
        if (strlen($text) < 50 && preg_match_all('/BT\s+(.*?)\s+ET/s', $pdfContent, $btMatches)) {
            foreach ($btMatches[1] as $btMatch) {
                if (preg_match_all('/\((.*?)\)/', $btMatch, $textMatches)) {
                    foreach ($textMatches[1] as $textMatch) {
                        $cleanText = $this->cleanPdfText($textMatch);
                        if (strlen($cleanText) > 2) {
                            $text .= $cleanText . ' ';
                        }
                    }
                }
            }
        }
        
        // Fallback: extract any readable ASCII text
        if (strlen($text) < 50) {
            $text = $this->extractReadableText($pdfContent);
        }
        
        $text = $this->cleanTextContent($text);
        
        Log::info('PDF text extraction result: ' . strlen($text) . ' characters');
        Log::info('PDF text preview: ' . substr($text, 0, 200) . '...');
        
        if (strlen($text) < 50) {
            throw new \Exception('Could not extract sufficient text from PDF');
        }
        
        return substr($text, 0, 1000);
    }

    /**
     * Clean PDF text extraction results
     */
    protected function cleanPdfText(string $text): string
    {
        // Remove PDF escape sequences
        $text = str_replace(['\\r', '\\n', '\\t'], [' ', ' ', ' '], $text);
        $text = preg_replace('/\\\\[0-9]{1,3}/', '', $text); // Remove octal sequences
        $text = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $text);
        
        return trim($text);
    }

    /**
     * Extract readable text from binary content
     */
    protected function extractReadableText(string $content): string
    {
        // Extract sequences of printable characters
        $text = '';
        if (preg_match_all('/[a-zA-Z0-9\s\.,\!\?\-\:\;]{10,}/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $clean = trim($match);
                if (strlen($clean) > 10) {
                    $text .= $clean . ' ';
                }
            }
        }
        
        return $text;
    }

    /**
     * Clean text content for API consumption (ENHANCED)
     */
    protected function cleanTextContent(string $content): string
    {
        $content = trim($content);
        
        // More aggressive UTF-8 cleaning for PDF content
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        $content = mb_scrub($content, 'UTF-8');
        
        // Remove control characters and non-printable characters
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
        
        // Use iconv with more aggressive cleaning
        $content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        
        // For PDF content, be more aggressive - keep only basic ASCII and common punctuation
        $content = preg_replace('/[^\x20-\x7E\s]/', ' ', $content);
        
        // Clean up multiple spaces
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Final test - if still can't JSON encode, use base64 fallback approach
        $testJson = json_encode($content);
        if ($testJson === false) {
            Log::warning('Content still not JSON encodable, using ASCII-only fallback');
            // Keep only letters, numbers, basic punctuation and spaces
            $content = preg_replace('/[^a-zA-Z0-9\s\.\,\!\?\-\:\;\(\)]/', ' ', $content);
            $content = preg_replace('/\s+/', ' ', $content);
        }
        
        // Limit content length
        $content = substr(trim($content), 0, 1000);
        Log::info('Text content prepared for AI, length: ' . strlen($content) . ' characters');
        Log::info('Content preview: ' . substr($content, 0, 100) . '...');
        
        return $content;
    }

    /**
     * Generate simple text preview as fallback
     */
    protected function generateSimplePreviewFromUrl(string $fileUrl, string $mimeType): string
    {
        if (!$this->canSummarize($mimeType)) {
            return 'Preview not available for this file type (' . $mimeType . ')';
        }

        try {
            $response = Http::timeout(5)->get($fileUrl);
            if (!$response->successful()) {
                return 'Could not fetch file for preview';
            }

            $content = trim($response->body());

            if (strpos($mimeType, 'text/') === 0 && strlen($content) > 20) {
                // Clean text and get first few sentences
                $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
                
                if (count($sentences) > 0) {
                    $preview = implode('. ', array_slice($sentences, 0, 2));
                    $preview = substr($preview, 0, 200);
                    
                    if (strlen($preview) < strlen($content)) {
                        $preview .= '...';
                    }
                    
                    return 'Text preview: ' . $preview;
                }
            }
            
            return 'File uploaded successfully. Preview not available.';
            
        } catch (\Exception $e) {
            return 'File uploaded successfully. Preview generation failed.';
        }
    }

    /**
     * Delete a document
     */
    public function delete(Request $request)
    {
        try {
            $documentId = $request->input('document_id');
            
            if (!$documentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID is required'
                ], 400);
            }

            // Find the document (ensure user owns it)
            $document = Document::where('id', $documentId)
                              ->where('user_id', Auth::id())
                              ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Delete from Supabase
            try {
                $this->supabase->deleteFile($this->bucket, $document->file_path);
                Log::info('File deleted from Supabase: ' . $document->file_path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete from Supabase (continuing anyway): ' . $e->getMessage());
                // Continue with database deletion even if Supabase deletion fails
            }

            // Delete from database
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
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
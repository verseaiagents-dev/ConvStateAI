<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeChunk;
use App\Models\FieldMapping;
use App\Services\KnowledgeBase\ContentChunker;
use App\Services\KnowledgeBase\AIService;
use App\Services\KnowledgeBase\FAQOptimizationService;
use App\Services\KnowledgeBase\FieldMappingService;
use Maatwebsite\Excel\Facades\Excel;

class KnowledgeBaseController extends Controller
{
    protected $contentChunker;
    protected $aiService;
    protected $faqOptimizer;
    protected $fieldMappingService;

    public function __construct(ContentChunker $contentChunker, AIService $aiService, FAQOptimizationService $faqOptimizer, FieldMappingService $fieldMappingService)
    {
        $this->contentChunker = $contentChunker;
        $this->aiService = $aiService;
        $this->faqOptimizer = $faqOptimizer;
        $this->fieldMappingService = $fieldMappingService;
    }

    /**
     * Show knowledge base page
     */
    public function index()
    {
        $user = Auth::user();
        $knowledgeBases = KnowledgeBase::with('chunks')->orderBy('created_at', 'desc')->get();
        
        return view('dashboard.knowledge-base', compact('user', 'knowledgeBases'));
    }

    /**
     * Handle file upload
     */
    public function uploadFile(Request $request)
    {
        try {
            // Validation
            $validator = \Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt,xml,json,xlsx,xls|max:10240', // 10MB max
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ], [
                'file.required' => 'Dosya seçilmedi',
                'file.file' => 'Geçersiz dosya',
                'file.mimes' => 'Desteklenmeyen dosya formatı. Desteklenen: CSV, TXT, XML, JSON, Excel',
                'file.max' => 'Dosya boyutu çok büyük. Maksimum 10MB olmalı',
                'name.required' => 'Knowledge base adı gerekli',
                'name.string' => 'Knowledge base adı metin olmalı',
                'name.max' => 'Knowledge base adı çok uzun (maksimum 255 karakter)',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation hatası',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // File size check
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new \Exception('Dosya boyutu 10MB\'dan büyük olamaz');
            }

            // File extension check
            $allowedExtensions = ['csv', 'txt', 'xml', 'json', 'xlsx', 'xls'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception("Desteklenmeyen dosya formatı: {$extension}. Desteklenen: " . implode(', ', $allowedExtensions));
            }
            
            // Store file
            $path = $file->storeAs('knowledge-base', $fileName, 'public');
            
            // Create knowledge base record
            $knowledgeBase = KnowledgeBase::create([
                'site_id' => 1, // Default site
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'source_type' => 'file',
                'source_path' => $path,
                'file_type' => $extension,
                'file_size' => $this->contentChunker->countTokens(file_get_contents($file->getPathname())),
                'processing_status' => 'processing',
                'is_processing' => true,
            ]);

            // Process file and create chunks
            $chunks = $this->processFileAndCreateChunks($file, $extension, $knowledgeBase);
            
            // Update knowledge base with chunk count
            $knowledgeBase->update([
                'chunk_count' => count($chunks),
                'total_records' => $this->getTotalRecords($file, $extension),
                'processed_records' => count($chunks),
                'processing_status' => 'completed',
                'is_processing' => false,
                'last_processed_at' => Carbon::now(),
            ]);

            DB::commit();
        
            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi ve işlendi',
                'knowledge_base_id' => $knowledgeBase->id,
                'chunk_count' => count($chunks),
                'file_name' => $fileName,
                'file_size' => $this->contentChunker->countTokens(file_get_contents($file->getPathname())),
                'extension' => $extension
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($knowledgeBase)) {
                $knowledgeBase->update([
                    'processing_status' => 'failed',
                    'is_processing' => false,
                    'error_message' => $e->getMessage()
                ]);
            }

            \Log::error('Knowledge base upload error: ' . $e->getMessage(), [
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'N/A',
                'name' => $request->input('name'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dosya işlenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle URL content fetch
     */
    public function fetchFromUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:500',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $url = $request->input('url');
            $response = \Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL\'den içerik alınamadı. HTTP Status: ' . $response->status()
                ], 400);
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', '');
            
            // UTF-8 encoding kontrolü ve düzeltme
            if (!mb_check_encoding($content, 'UTF-8')) {
                // Farklı encoding'leri dene
                $encodings = ['ISO-8859-1', 'ISO-8859-9', 'Windows-1254', 'Windows-1252', 'ASCII'];
                
                foreach ($encodings as $encoding) {
                    if (mb_check_encoding($content, $encoding)) {
                        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                        \Log::info("URL content encoding converted from {$encoding} to UTF-8: " . $url);
                        break;
                    }
                }
                
                // Hala UTF-8 değilse, force convert
                if (!mb_check_encoding($content, 'UTF-8')) {
                    $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                    \Log::warning("URL content encoding force converted to UTF-8: " . $url);
                }
            }
            
            // Determine file type from content type or URL
            $extension = $this->determineExtensionFromUrl($url, $contentType);
            
            // Create knowledge base record
            $knowledgeBase = KnowledgeBase::create([
                'site_id' => 1, // Default site
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'source_type' => 'url',
                'source_path' => $url,
                'file_type' => $extension,
                'file_size' => $this->contentChunker->countTokens($content),
                'processing_status' => 'processing',
                'is_processing' => true,
            ]);
            
            // Create temporary file for processing
            $tempFile = tempnam(sys_get_temp_dir(), 'kb_url_');
            file_put_contents($tempFile, $content);
            
            // Create a file object for processing
            $file = new \Illuminate\Http\UploadedFile(
                $tempFile,
                basename($url),
                $this->getMimeType($extension),
                null,
                true
            );
            
            // Process content and create chunks
            $chunks = $this->processFileAndCreateChunks($file, $extension, $knowledgeBase);
            
            // Update knowledge base with chunk count
            $knowledgeBase->update([
                'chunk_count' => count($chunks),
                'total_records' => $this->getTotalRecords($file, $extension),
                'processed_records' => count($chunks),
                'processing_status' => 'completed',
                'is_processing' => false,
                'last_processed_at' => Carbon::now(),
            ]);
            
            // Clean up temp file
            unlink($tempFile);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'URL\'den içerik başarıyla alındı ve işlendi',
                'knowledge_base_id' => $knowledgeBase->id,
                'chunk_count' => count($chunks),
                'file_name' => basename($url),
                'file_size' => $this->contentChunker->countTokens($content),
                'extension' => $extension,
                'url' => $url
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($knowledgeBase)) {
                $knowledgeBase->update([
                    'processing_status' => 'failed',
                    'is_processing' => false,
                    'error_message' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'URL işlenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process file and create chunks - Gelişmiş algoritma
     */
    private function processFileAndCreateChunks($file, $extension, KnowledgeBase $knowledgeBase): array
    {
        $chunks = [];
        
        // Akıllı chunking konfigürasyonu
        $chunkConfig = [
            'max_chunk_size' => 800, // Daha küçük chunk'lar
            'overlap_size' => 150,   // Daha az overlap
            'min_chunk_size' => 200,
            'preserve_words' => true, // Kelime bütünlüğünü koru
            'smart_sizing' => true,   // Akıllı boyutlandırma
            'quality_check' => true   // Kalite kontrolü
        ];
        
        switch ($extension) {
            case 'csv':
                $chunks = $this->processCSV($file, $knowledgeBase, $chunkConfig);
                break;
            case 'txt':
                $chunks = $this->processTXT($file, $knowledgeBase, $chunkConfig);
                break;
            case 'xml':
                $chunks = $this->processXML($file, $knowledgeBase, $chunkConfig);
                break;
            case 'json':
                $chunks = $this->processJSON($file, $knowledgeBase, $chunkConfig);
                break;
            case 'xlsx':
            case 'xls':
                $chunks = $this->processExcel($file, $knowledgeBase, $chunkConfig);
                break;
            default:
                throw new \Exception('Desteklenmeyen dosya formatı: ' . $extension);
        }

        // Chunk kalitesini değerlendir
        if ($chunkConfig['quality_check']) {
            $quality = $this->contentChunker->evaluateChunkQuality($chunks);
            \Log::info('Chunk quality report:', $quality);
            
            // Düşük kaliteli chunk'ları yeniden işle
            if ($quality['overlap_quality'] < 80) {
                \Log::warning('Low chunk quality detected, reprocessing with better config');
                $chunkConfig['overlap_size'] = 200;
                $chunkConfig['max_chunk_size'] = 600;
                
                // Yeniden işle
                switch ($extension) {
                    case 'csv':
                        $chunks = $this->processCSV($file, $knowledgeBase, $chunkConfig);
                        break;
                    case 'txt':
                        $chunks = $this->processTXT($file, $knowledgeBase, $chunkConfig);
                        break;
                    default:
                        break;
                }
            }
        }

        // Create chunks in database
        $chunkModels = [];
        foreach ($chunks as $chunkData) {
            // Content type'ı daha iyi belirle
            $contentType = $this->determineContentType($chunkData['content'], $extension);
            
            $chunkModels[] = KnowledgeChunk::create([
                'knowledge_base_id' => $knowledgeBase->id,
                'chunk_index' => $chunkData['chunk_index'],
                'content' => $chunkData['content'],
                'content_hash' => $chunkData['content_hash'],
                'content_type' => $contentType,
                'chunk_size' => $chunkData['chunk_size'],
                'word_count' => $chunkData['word_count'],
                'metadata' => array_merge($chunkData['metadata'] ?? [], [
                    'original_content_type' => $chunkData['content_type'] ?? 'unknown',
                    'detected_content_type' => $contentType
                ]),
            ]);
        }

        // FAQ optimizasyonu yap (eğer content_type faq ise veya genel content ise)
        // Bu işlem başarısız olsa bile upload işlemi devam etsin
        try {
            // Sadece küçük dosyalar için FAQ optimizasyonu yap
            $totalContentSize = collect($chunks)->sum('chunk_size');
            if ($totalContentSize < 50000) { // 50KB'dan küçük dosyalar
                $this->optimizeFAQContent($knowledgeBase, $chunks);
            } else {
                Log::info('File too large for FAQ optimization, skipping', [
                    'knowledge_base_id' => $knowledgeBase->id,
                    'total_size' => $totalContentSize
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('FAQ optimization failed during upload, continuing without optimization', [
                'knowledge_base_id' => $knowledgeBase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // FAQ optimizasyonu başarısız olsa bile upload işlemi devam etsin
            // Kullanıcıya bilgi ver
            Log::info('Upload completed successfully without FAQ optimization', [
                'knowledge_base_id' => $knowledgeBase->id,
                'chunks_created' => count($chunkModels)
            ]);
        }

        return $chunkModels;
    }

    /**
     * Process CSV file
     */
    private function processCSV($file, KnowledgeBase $knowledgeBase, array $config = []): array
    {
        $content = $this->readFileSafely($file->getPathname());
        return $this->contentChunker->chunkCsvContent($content, array_merge([
            'max_rows_per_chunk' => 30, // Daha az satır per chunk
            'preserve_rows' => true
        ], $config));
    }

    /**
     * Process TXT file
     */
    private function processTXT($file, KnowledgeBase $knowledgeBase, array $config = []): array
    {
        $content = $this->readFileSafely($file->getPathname());
        return $this->contentChunker->chunkContent($content, array_merge([
            'max_chunk_size' => 800,
            'overlap_size' => 150,
            'preserve_words' => true
        ], $config));
    }

    /**
     * Process XML file
     */
    private function processXML($file, KnowledgeBase $knowledgeBase, array $config = []): array
    {
        $content = $this->readFileSafely($file->getPathname());
        $xml = simplexml_load_string($content);
        
        if ($xml === false) {
            throw new \Exception('XML dosyası okunamadı');
        }
        
        // Convert XML to text for chunking
        $textContent = $this->xmlToText($xml);
        return $this->contentChunker->chunkContent($textContent, array_merge([
            'max_chunk_size' => 800,
            'overlap_size' => 150,
            'preserve_words' => true
        ], $config));
    }

    /**
     * Process JSON file
     */
    private function processJSON($file, KnowledgeBase $knowledgeBase, array $config = []): array
    {
        $content = $this->readFileSafely($file->getPathname());
        return $this->contentChunker->chunkJsonContent($content, array_merge([
            'max_items_per_chunk' => 1, // Her ürün için ayrı chunk
            'preserve_structure' => true
        ], $config));
    }

    /**
     * Process Excel file
     */
    private function processExcel($file, KnowledgeBase $knowledgeBase, array $config = []): array
    {
        $content = Excel::toArray([], $file);
        $textContent = $this->excelToText($content);
        
        return $this->contentChunker->chunkContent($textContent, array_merge([
            'max_chunk_size' => 800,
            'overlap_size' => 150,
            'preserve_words' => true
        ], $config));
    }

    /**
     * Convert XML to text
     */
    private function xmlToText($xml, $depth = 0): string
    {
        $text = '';
        $indent = str_repeat('  ', $depth);
        
        foreach ($xml->children() as $child) {
            $text .= $indent . $child->getName() . ': ' . (string)$child . "\n";
            
            if (count($child->children()) > 0) {
                $text .= $this->xmlToText($child, $depth + 1);
            }
        }
        
        return $text;
    }

    /**
     * Convert Excel to text
     */
    private function excelToText(array $sheets): string
    {
        $text = '';
        
        foreach ($sheets as $sheetIndex => $sheet) {
            $text .= "Sheet " . ($sheetIndex + 1) . ":\n";
            
            foreach ($sheet as $rowIndex => $row) {
                $text .= "Row " . ($rowIndex + 1) . ": " . implode(' | ', $row) . "\n";
            }
            
            $text .= "\n";
        }
        
        return $text;
    }

    /**
     * Get total records count
     */
    private function getTotalRecords($file, $extension): int
    {
        switch ($extension) {
            case 'csv':
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);
                return count(array_filter($lines, 'trim')) - 1; // Exclude header
            case 'json':
                $content = file_get_contents($file->getPathname());
                $data = json_decode($content, true);
                return is_array($data) ? count($data) : 1;
            case 'xlsx':
            case 'xls':
                $content = Excel::toArray([], $file);
                return array_sum(array_map('count', $content));
            default:
                return 1;
        }
    }

    /**
     * Search knowledge base
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
        ]);

        try {
            $query = $request->input('query');
            
            // Intent detection
            $intent = $this->aiService->detectIntent($query);
            
            // Search for relevant chunks
            $chunks = KnowledgeChunk::where('is_indexed', true)
                ->where(function($q) use ($query) {
                    $q->where('content', 'like', '%' . $query . '%')
                      ->orWhereRaw("JSON_EXTRACT(metadata, '$.keywords') LIKE ?", ['%' . $query . '%']);
                })
                ->with('knowledgeBase')
                ->limit(5)
                ->get();
            
            // Generate response
            $response = $this->aiService->generateResponse($query, $chunks->toArray());
            
            // Log query
            DB::table('query_logs')->insert([
                'site_id' => 1,
                'session_id' => $request->session()->getId() ?? 'api_' . uniqid(),
                'user_id' => Auth::id() ?? 1,
                'query_text' => $query,
                'detected_intent' => $intent['intent'],
                'confidence_score' => $intent['confidence'],
                'response_text' => $response,
                'chunks_used' => json_encode($chunks->pluck('id')->toArray()),
                'response_time_ms' => 0, // TODO: Calculate actual response time
                'created_at' => Carbon::now(),
            ]);
            
            return response()->json([
                'success' => true,
                'intent' => $intent,
                'response' => $response,
                'chunks' => $chunks,
                'suggestions' => $this->generateSuggestions($intent)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama yapılırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate suggestions based on intent
     */
    private function generateSuggestions(array $intent): array
    {
        $suggestions = [];
        
        switch ($intent['intent']) {
            case 'product_search':
                $suggestions = [
                    'Ürün kategorilerini görmek ister misiniz?',
                    'Fiyat aralığı belirtebilir misiniz?',
                    'Hangi markayı tercih edersiniz?'
                ];
                break;
            case 'faq_search':
                $suggestions = [
                    'Sık sorulan sorular sayfasını ziyaret etmek ister misiniz?',
                    'Başka bir konuda yardım almak ister misiniz?'
                ];
                break;
            default:
                $suggestions = [
                    'Ürün aramak ister misiniz?',
                    'Kategorileri keşfetmek ister misiniz?',
                    'Yardım almak ister misiniz?'
                ];
        }
        
        return $suggestions;
    }

    /**
     * Get knowledge base details
     */
    public function show($id)
    {
        $knowledgeBase = KnowledgeBase::with('chunks')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'knowledge_base' => $knowledgeBase
        ]);
    }

    /**
     * Get knowledge base detail with chunks and stats
     */
    public function getDetail($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $chunks = KnowledgeChunk::where('knowledge_base_id', $id)
                ->orderBy('chunk_index', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Calculate statistics
            $stats = [
                'total_chunks' => $chunks->count(),
                'avg_chunk_size' => $chunks->count() > 0 ? round($chunks->avg('content_length') ?? 0) : 0,
                'total_tokens' => $chunks->sum('token_count') ?? 0,
            ];
            
            return response()->json([
                'success' => true,
                'knowledge_base' => $knowledgeBase,
                'chunks' => $chunks,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Knowledge base bulunamadı: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get knowledge base chunks
     */
    public function getChunks($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $chunks = KnowledgeChunk::where('knowledge_base_id', $id)
                ->orderBy('chunk_index', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'knowledge_base' => $knowledgeBase,
                'chunks' => $chunks,
                'total_chunks' => $chunks->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chunk\'lar alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete knowledge base
     */
    public function destroy($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            // Delete associated chunks
            $knowledgeBase->chunks()->delete();
            
            // Delete file from storage
            if ($knowledgeBase->source_type === 'file' && $knowledgeBase->source_path) {
                Storage::disk('public')->delete($knowledgeBase->source_path);
            }
            
            // Delete knowledge base
            $knowledgeBase->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Knowledge base başarıyla silindi'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Knowledge base silinirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine file extension from URL and content type
     */
    private function determineExtensionFromUrl(string $url, string $contentType): string
    {
        // Try to get extension from URL first
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $pathExtension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array(strtolower($pathExtension), ['csv', 'txt', 'xml', 'json', 'xlsx', 'xls'])) {
                return strtolower($pathExtension);
            }
        }
        
        // Try to determine from content type
        if (str_contains($contentType, 'json')) return 'json';
        if (str_contains($contentType, 'xml')) return 'xml';
        if (str_contains($contentType, 'csv') || str_contains($contentType, 'text/csv')) return 'csv';
        if (str_contains($contentType, 'text/plain')) return 'txt';
        if (str_contains($contentType, 'spreadsheet') || str_contains($contentType, 'excel')) return 'xlsx';
        
        // Default to txt if can't determine
        return 'txt';
    }

    /**
     * Get MIME type for extension
     */
    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            'json' => 'application/json',
            'txt' => 'text/plain',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            default => 'text/plain'
        };
    }

    /**
     * Safely read file content with UTF-8 encoding
     */
    private function readFileSafely(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \Exception("Dosya okunamadı: " . $path);
        }
        
        // UTF-8 encoding kontrolü ve düzeltme
        if (!mb_check_encoding($content, 'UTF-8')) {
            // Farklı encoding'leri dene
            $encodings = ['ISO-8859-1', 'ISO-8859-9', 'Windows-1254', 'Windows-1252', 'ASCII'];
            
            foreach ($encodings as $encoding) {
                if (mb_check_encoding($content, $encoding)) {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                    \Log::info("File encoding converted from {$encoding} to UTF-8: " . $path);
                    break;
                }
            }
            
            // Hala UTF-8 değilse, force convert
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                \Log::warning("File encoding force converted to UTF-8: " . $path);
            }
        }
        
        return $content;
    }

    /**
     * Optimize FAQ content based on chunks
     */
    private function optimizeFAQContent(KnowledgeBase $knowledgeBase, array $chunks)
    {
        try {
            $faqChunks = collect($chunks)->filter(function ($chunk) {
                return $chunk['content_type'] === 'faq';
            })->values();

            if ($faqChunks->isEmpty()) {
                return;
            }

            // Combine all FAQ content into a single string
            $combinedContent = $faqChunks->pluck('content')->implode("\n\n");
            
            // Optimize FAQ content
            $optimizationResult = $this->faqOptimizer->optimizeFAQContent($combinedContent, [
                'max_questions' => min(10, $faqChunks->count()),
                'question_style' => 'natural',
                'answer_style' => 'detailed',
                'language' => 'tr'
            ]);

            // Update chunks with optimized content
            foreach ($faqChunks as $index => $chunk) {
                if (isset($optimizationResult['faqs'][$index])) {
                    $faq = $optimizationResult['faqs'][$index];
                    
                    // Find the actual chunk model to update
                    $chunkModel = KnowledgeChunk::find($chunk['id'] ?? $chunk['chunk_index']);
                    if ($chunkModel) {
                        $chunkModel->update([
                            'content' => "Soru: {$faq['question']}\n\nCevap: {$faq['answer']}",
                            'content_hash' => hash('sha256', $faq['question'] . $faq['answer']),
                            'metadata' => array_merge($chunkModel->metadata ?? [], [
                                'optimized_at' => Carbon::now(),
                                'faq_data' => $faq,
                                'optimization_score' => $optimizationResult['optimization_score']
                            ])
                        ]);
                    }
                }
            }

            // Update knowledge base metadata
            $knowledgeBase->update([
                'metadata' => array_merge($knowledgeBase->metadata ?? [], [
                    'faq_optimized_at' => Carbon::now(),
                    'faq_optimization_score' => $optimizationResult['optimization_score'],
                    'faq_metadata' => $optimizationResult['metadata']
                ])
            ]);

            Log::info('FAQ optimization completed successfully', [
                'knowledge_base_id' => $knowledgeBase->id,
                'chunks_optimized' => $faqChunks->count(),
                'optimization_score' => $optimizationResult['optimization_score']
            ]);

        } catch (\Exception $e) {
            Log::error('FAQ optimization failed', [
                'knowledge_base_id' => $knowledgeBase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw exception, just log the error to avoid breaking the upload process
        }
    }

    /**
     * Manually optimize FAQ content for a knowledge base
     */
    public function optimizeFAQ(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            $request->validate([
                'config' => 'array',
                'config.max_questions' => 'integer|min:1|max:20',
                'config.question_style' => 'string|in:natural,formal,casual,technical',
                'config.answer_style' => 'string|in:detailed,concise,step_by_step,technical',
                'config.language' => 'string|in:tr,en,mixed'
            ]);

            $config = $request->input('config', []);
            
            // Get FAQ chunks
            $faqChunks = KnowledgeChunk::where('knowledge_base_id', $id)
                ->where('content_type', 'faq')
                ->get();

            if ($faqChunks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu knowledge base\'de FAQ content bulunamadı'
                ], 404);
            }

            // Combine all FAQ content
            $combinedContent = $faqChunks->pluck('content')->implode("\n\n");
            
            // Optimize FAQ content
            $optimizationResult = $this->faqOptimizer->optimizeFAQContent($combinedContent, $config);
            
            // Update chunks with optimized content
            foreach ($faqChunks as $index => $chunk) {
                if (isset($optimizationResult['faqs'][$index])) {
                    $faq = $optimizationResult['faqs'][$index];
                    
                    $chunk->update([
                        'content' => "Soru: {$faq['question']}\n\nCevap: {$faq['answer']}",
                        'content_hash' => hash('sha256', $faq['question'] . $faq['answer']),
                        'metadata' => array_merge($chunk->metadata ?? [], [
                            'optimized_at' => Carbon::now(),
                            'faq_data' => $faq,
                            'optimization_score' => $optimizationResult['optimization_score']
                        ])
                    ]);
                }
            }

            // Update knowledge base metadata
            $knowledgeBase->update([
                'metadata' => array_merge($knowledgeBase->metadata ?? [], [
                    'faq_optimized_at' => Carbon::now(),
                    'faq_optimization_score' => $optimizationResult['optimization_score'],
                    'faq_metadata' => $optimizationResult['metadata']
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ content başarıyla optimize edildi',
                'optimization_result' => $optimizationResult,
                'chunks_updated' => $faqChunks->count()
            ]);

        } catch (\Exception $e) {
            Log::error('FAQ optimization error: ' . $e->getMessage(), [
                'knowledge_base_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'FAQ optimizasyonu başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine content type of a chunk
     */
    private function determineContentType(string $content, string $extension): string
    {
        // Basic checks for common FAQ patterns
        $content = strtolower($content);
        if (str_contains($content, 'soru:') || str_contains($content, 'cevap:') || str_contains($content, 'sorular:') || str_contains($content, 'cevaplar:')) {
            return 'faq';
        }

        // More sophisticated checks for specific file types
        if ($extension === 'txt') {
            if (str_contains($content, 'soru:') || str_contains($content, 'cevap:') || str_contains($content, 'sorular:') || str_contains($content, 'cevaplar:')) {
                return 'faq';
            }
            if (str_contains($content, 'faq:') || str_contains($content, 'soru:') || str_contains($content, 'cevap:')) {
                return 'faq';
            }
        }

        if ($extension === 'csv') {
            // Look for specific columns or patterns in CSV
            if (str_contains($content, 'soru,cevap') || str_contains($content, 'soru,cevaplar') || str_contains($content, 'soru,cevaplar')) {
                return 'faq';
            }
        }

        if ($extension === 'json') {
            // Look for specific JSON keys or patterns
            if (str_contains($content, '"soru":') || str_contains($content, '"cevap":') || str_contains($content, '"sorular":') || str_contains($content, '"cevaplar":')) {
                return 'faq';
            }
            
            // Product catalog detection
            if (str_contains($content, '"title":') && str_contains($content, '"price":') && str_contains($content, '"category":')) {
                return 'product';
            }
            
            // Product listing detection
            if (str_contains($content, '"id":') && str_contains($content, '"name":') && str_contains($content, '"brand":')) {
                return 'product';
            }
        }

        if ($extension === 'xml') {
            // Look for specific XML tags or attributes
            if (str_contains($content, '<soru>') || str_contains($content, '<cevap>') || str_contains($content, '<sorular>') || str_contains($content, '<cevaplar>')) {
                return 'faq';
            }
        }

        // Default to 'general' if no specific pattern is found
        return 'general';
    }

    /**
     * Detect fields from uploaded file
     */
    public function detectFields(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            if (!$knowledgeBase->source_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Knowledge base dosyası bulunamadı'
                ], 404);
            }

            $detectedFields = $this->fieldMappingService->detectFields(
                $knowledgeBase->source_path,
                $knowledgeBase->file_type
            );

            $suggestedMappings = $this->fieldMappingService->suggestMappings($detectedFields);

            return response()->json([
                'success' => true,
                'detected_fields' => $detectedFields,
                'suggested_mappings' => $suggestedMappings,
                'standard_fields' => FieldMapping::getStandardFields(),
                'field_types' => FieldMapping::getFieldTypes()
            ]);

        } catch (\Exception $e) {
            Log::error('Field detection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Field detection hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save field mappings
     */
    public function saveFieldMappings(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            $request->validate([
                'mappings' => 'required|array|min:1',
                'mappings.*.source_field' => 'required|string',
                'mappings.*.target_field' => 'required|string',
                'mappings.*.field_type' => 'required|string',
                'mappings.*.is_required' => 'boolean',
                'mappings.*.default_value' => 'nullable|string',
                'mappings.*.transformation' => 'nullable|array',
                'mappings.*.validation_rules' => 'nullable|array'
            ]);

            $mappings = $request->input('mappings');
            
            // Validate mappings
            $errors = $this->fieldMappingService->validateMappings($mappings);
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mapping validation hatası',
                    'errors' => $errors
                ], 422);
            }

            // Save mappings
            $success = $this->fieldMappingService->createMappings($knowledgeBase->id, $mappings);
            
            if (!$success) {
                throw new \Exception('Field mappings kaydedilemedi');
            }

            // Update knowledge base status
            $knowledgeBase->update([
                'processing_status' => 'completed',
                'metadata' => array_merge($knowledgeBase->metadata ?? [], [
                    'field_mappings_created_at' => Carbon::now(),
                    'field_mappings_count' => count($mappings)
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field mappings başarıyla kaydedildi',
                'mappings_count' => count($mappings)
            ]);

        } catch (\Exception $e) {
            Log::error('Save field mappings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Field mappings kaydedilemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field mappings for knowledge base
     */
    public function getFieldMappings($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $mappings = $knowledgeBase->fieldMappings()->orderBy('mapping_order')->get();

            return response()->json([
                'success' => true,
                'mappings' => $mappings
            ]);

        } catch (\Exception $e) {
            Log::error('Get field mappings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Field mappings alınamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview transformed data
     */
    public function previewTransformedData(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            $request->validate([
                'mappings' => 'required|array|min:1'
            ]);

            $mappings = $request->input('mappings');
            
            // Get sample data from file
            $sampleData = $this->getSampleDataFromFile($knowledgeBase->source_path, $knowledgeBase->file_type, 5);
            
            // Transform data using mappings
            $transformedData = $this->fieldMappingService->transformData($sampleData, $mappings);

            return response()->json([
                'success' => true,
                'original_data' => $sampleData,
                'transformed_data' => $transformedData
            ]);

        } catch (\Exception $e) {
            Log::error('Preview transformed data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data preview hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample data from file for preview
     */
    private function getSampleDataFromFile(string $filePath, string $fileType, int $rows = 5): array
    {
        return $this->fieldMappingService->getSampleData($filePath, $fileType, $rows);
    }

    /**
     * Validate data against field mappings
     */
    public function validateData(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $mappings = $request->input('mappings', []);
            $data = $request->input('data', []);

            if (empty($mappings) || empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mappings and data are required'
                ], 400);
            }

            $validationResults = [];
            $totalErrors = 0;

            foreach ($data as $rowIndex => $row) {
                $rowErrors = [];
                
                foreach ($mappings as $mapping) {
                    $sourceField = $mapping['source_field'];
                    $value = $row[$sourceField] ?? null;
                    $validationRules = $mapping['validation_rules'] ?? [];

                    if (!empty($validationRules)) {
                        $errors = $this->fieldMappingService->validateData($value, $validationRules);
                        if (!empty($errors)) {
                            $rowErrors[$sourceField] = $errors;
                            $totalErrors++;
                        }
                    }
                }

                if (!empty($rowErrors)) {
                    $validationResults[$rowIndex] = $rowErrors;
                }
            }

            return response()->json([
                'success' => true,
                'validation_results' => $validationResults,
                'total_errors' => $totalErrors,
                'total_rows' => count($data),
                'valid_rows' => count($data) - count($validationResults)
            ]);

        } catch (\Exception $e) {
            Log::error('Data validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process data in batches
     */
    public function processBatchData(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $mappings = $request->input('mappings', []);
            $chunkSize = $request->input('chunk_size', 100);

            if (empty($mappings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mappings are required'
                ], 400);
            }

            // Get sample data for processing
            $sampleData = $this->getSampleDataFromFile($knowledgeBase->source_path, $knowledgeBase->file_type, 1000);
            
            // Process data in batches
            $results = $this->fieldMappingService->processBatchData($sampleData, $mappings, $chunkSize);
            
            // Get processing statistics
            $stats = $this->fieldMappingService->getProcessingStats($sampleData, $mappings);

            return response()->json([
                'success' => true,
                'results' => $results,
                'statistics' => $stats,
                'message' => 'Batch processing completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Batch processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Batch processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field mapping statistics
     */
    public function getMappingStats($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $mappings = $knowledgeBase->fieldMappings()->get();

            $stats = [
                'total_mappings' => $mappings->count(),
                'active_mappings' => $mappings->where('is_active', true)->count(),
                'required_mappings' => $mappings->where('is_required', true)->count(),
                'field_types' => $mappings->groupBy('field_type')->map->count(),
                'transformation_rules' => $mappings->whereNotNull('transformation')->count(),
                'validation_rules' => $mappings->whereNotNull('validation_rules')->count()
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Mapping stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get mapping statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export transformed data
     */
    public function exportTransformedData(Request $request, $id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            $mappings = $request->input('mappings', []);
            $format = $request->input('format', 'csv');

            if (empty($mappings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mappings are required'
                ], 400);
            }

            // Get sample data
            $sampleData = $this->getSampleDataFromFile($knowledgeBase->source_path, $knowledgeBase->file_type, 1000);
            
            // Transform data
            $transformedData = $this->fieldMappingService->transformData($sampleData, $mappings);

            // Export based on format
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($transformedData, $knowledgeBase->name);
                case 'json':
                    return response()->json([
                        'success' => true,
                        'data' => $transformedData
                    ]);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported export format'
                    ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data to CSV
     */
    private function exportToCsv(array $data, string $filename)
    {
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'No data to export'
            ], 400);
        }

        $headers = array_keys($data[0]);
        $csvContent = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';
                $csvRow[] = '"' . str_replace('"', '""', $value) . '"';
            }
            $csvContent .= implode(',', $csvRow) . "\n";
        }

        $filename = str_replace(' ', '_', $filename) . '_transformed.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

<?php

namespace App\Services\KnowledgeBase;

class ContentChunker
{
    /**
     * Token sayısını hesaplar - Merkezi boyut hesaplama metodu
     * İleride gerçek tokenizer bağlanabilir
     */
    public function countTokens(string $text): int
    {
        try {
            // UTF-8 karakter kodlaması kontrolü
            if (!mb_check_encoding($text, 'UTF-8')) {
                // UTF-8 değilse, güvenli şekilde dönüştür
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }
            
            // Boş string kontrolü
            if (empty($text)) {
                return 0;
            }
            
            // İlk versiyonda token ≈ mb_strlen($text) / 4
            // İleride gerçek tokenizer bağlanabilir
            $tokenCount = (int) ceil(mb_strlen($text, 'UTF-8') / 4);
            
            // Minimum 1 token döndür
            return max(1, $tokenCount);
            
        } catch (\Exception $e) {
            // Hata durumunda fallback olarak strlen kullan
            \Log::warning('countTokens error, falling back to strlen: ' . $e->getMessage());
            return (int) ceil(strlen($text) / 4);
        }
    }

    /**
     * Content'i chunk'lara böler - Gelişmiş algoritma
     */
    public function chunkContent(string $content, array $config = []): array
    {
        $chunks = [];
        $maxChunkSize = $config['max_chunk_size'] ?? 1000;
        $overlapSize = $config['overlap_size'] ?? 200;
        $minChunkSize = $config['min_chunk_size'] ?? 200;
        $preserveWords = $config['preserve_words'] ?? true;
        
        // Content'i cümlelere böl
        $sentences = $this->splitIntoSentences($content);
        
        $currentChunk = '';
        $chunkIndex = 0;
        
        foreach ($sentences as $sentence) {
            $sentenceLength = $this->countTokens($sentence);
            
            // Eğer mevcut chunk + yeni cümle maksimum boyutu aşıyorsa
            if ($this->countTokens($currentChunk . $sentence) > $maxChunkSize) {
                if (!empty($currentChunk) && $this->countTokens($currentChunk) >= $minChunkSize) {
                    // Chunk'ı kelime sınırlarında böl
                    $finalChunk = $this->trimToWordBoundary($currentChunk, $preserveWords);
                    $chunks[] = $this->createChunk($finalChunk, $chunkIndex++);
                    
                    // Overlap'i de kelime sınırlarında al
                    $currentChunk = $this->getSmartOverlap($currentChunk, $overlapSize, $preserveWords);
                }
            }
            
            $currentChunk .= $sentence . ' ';
        }
        
        // Son chunk'ı ekle
        if (!empty($currentChunk) && $this->countTokens(trim($currentChunk)) >= $minChunkSize) {
            $finalChunk = $this->trimToWordBoundary(trim($currentChunk), $preserveWords);
            $chunks[] = $this->createChunk($finalChunk, $chunkIndex);
        }
        
        return $chunks;
    }

    /**
     * Content'i cümlelere böler - Gelişmiş algoritma
     */
    private function splitIntoSentences(string $content): array
    {
        // Türkçe ve İngilizce cümle sonları + özel durumlar
        $sentenceEndings = [
            '. ', '! ', '? ', 
            '.\n', '!\n', '?\n', 
            '.\r\n', '!\r\n', '?\r\n',
            '... ', '?! ', '!? ',
            '.\t', '!\t', '?\t'
        ];
        
        // Kısaltmalar ve özel durumlar (bunları cümle sonu olarak kabul etme)
        $abbreviations = [
            'Dr.', 'Mr.', 'Mrs.', 'Ms.', 'Prof.', 'Inc.', 'Ltd.', 'Co.',
            'vs.', 'etc.', 'i.e.', 'e.g.', 'a.m.', 'p.m.', 'U.S.', 'U.K.',
            'Dr', 'Mr', 'Mrs', 'Ms', 'Prof', 'Inc', 'Ltd', 'Co',
            'vs', 'etc', 'i.e', 'e.g', 'a.m', 'p.m', 'U.S', 'U.K'
        ];
        
        $sentences = [];
        $currentSentence = '';
        
        // Content'i satırlara böl
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Satırı cümlelere böl
            $lineSentences = $this->splitLineIntoSentences($line, $sentenceEndings, $abbreviations);
            
            foreach ($lineSentences as $sentence) {
                if (!empty(trim($sentence))) {
                    $sentences[] = trim($sentence);
                }
            }
        }
        
        // Cümleleri filtrele ve temizle
        return $this->filterAndCleanSentences($sentences);
    }

    /**
     * Satırı cümlelere böler
     */
    private function splitLineIntoSentences(string $line, array $endings, array $abbreviations): array
    {
        $sentences = [];
        $currentSentence = '';
        
        // UTF-8 güvenliği için mb_* fonksiyonları kullan
        $lineLength = mb_strlen($line, 'UTF-8');
        
        // Satırı karakter karakter işle
        $i = 0;
        while ($i < $lineLength) {
            $char = mb_substr($line, $i, 1, 'UTF-8');
            $currentSentence .= $char;
            
            // Cümle sonu kontrolü
            if ($this->isSentenceEnd($line, $i, $endings, $abbreviations)) {
                $sentences[] = $currentSentence;
                $currentSentence = '';
                
                // Birden fazla karakter atla (örn: "..." için)
                if ($char === '.' && $i + 2 < $lineLength && 
                    mb_substr($line, $i + 1, 1, 'UTF-8') === '.' && 
                    mb_substr($line, $i + 2, 1, 'UTF-8') === '.') {
                    $i += 2;
                }
            }
            
            $i++;
        }
        
        // Kalan kısmı ekle
        if (!empty(trim($currentSentence))) {
            $sentences[] = $currentSentence;
        }
        
        return $sentences;
    }

    /**
     * Cümle sonu olup olmadığını kontrol eder
     */
    private function isSentenceEnd(string $line, int $position, array $endings, array $abbreviations): bool
    {
        $lineLength = mb_strlen($line, 'UTF-8');
        
        if ($position >= $lineLength - 1) {
            return false;
        }
        
        $char = mb_substr($line, $position, 1, 'UTF-8');
        $nextChar = mb_substr($line, $position + 1, 1, 'UTF-8');
        
        // Cümle sonu karakterleri
        if (in_array($char, ['.', '!', '?'])) {
            // Sonraki karakter boşluk, tab veya yeni satır mı?
            if (in_array($nextChar, [' ', "\t", "\n", "\r"])) {
                // Kısaltma kontrolü
                $wordBefore = $this->getWordBefore($line, $position);
                if (!in_array($wordBefore, $abbreviations)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Pozisyondan önceki kelimeyi alır
     */
    private function getWordBefore(string $line, int $position): string
    {
        $word = '';
        $i = $position - 1;
        
        // UTF-8 güvenliği için mb_* fonksiyonları kullan
        while ($i >= 0 && $i < mb_strlen($line, 'UTF-8')) {
            $char = mb_substr($line, $i, 1, 'UTF-8');
            
            // Harf kontrolü (Türkçe karakterler dahil)
            if (preg_match('/[\p{L}\p{N}]/u', $char)) {
                $word = $char . $word;
            } else {
                break;
            }
            
            $i--;
        }
        
        return $word;
    }

    /**
     * Cümleleri filtrele ve temizle
     */
    private function filterAndCleanSentences(array $sentences): array
    {
        $filtered = [];
        
        foreach ($sentences as $sentence) {
            $cleanSentence = trim($sentence);
            
            // Çok kısa cümleleri filtrele (token bazlı)
            if ($this->countTokens($cleanSentence) < 10) {
                continue;
            }
            
            // Sadece noktalama işareti olan cümleleri filtrele
            if (preg_match('/^[^\w\s]*$/u', $cleanSentence)) {
                continue;
            }
            
            // En az bir harf içeren cümleleri kabul et (Türkçe karakterler dahil)
            if (preg_match('/[\p{L}]/u', $cleanSentence)) {
                $filtered[] = $cleanSentence;
            }
        }
        
        return $filtered;
    }

    /**
     * Chunk oluşturur
     */
    private function createChunk(string $content, int $index): array
    {
        return [
            'chunk_index' => $index,
            'content' => trim($content),
            'content_hash' => hash('sha256', $content),
            'chunk_size' => $this->countTokens($content),
            'word_count' => str_word_count($content),
            'content_type' => $this->detectContentType($content),
            'metadata' => $this->extractMetadata($content)
        ];
    }

    /**
     * Overlap oluşturur
     */
    private function getOverlap(string $content, int $overlapSize): string
    {
        if ($this->countTokens($content) <= $overlapSize) {
            return $content;
        }
        
        // Token bazlı overlap hesaplama - güvenli UTF-8 işleme
        try {
            $targetTokens = $overlapSize;
            $currentTokens = 0;
            $position = 0;
            $contentLength = mb_strlen($content, 'UTF-8');
            
            // Güvenli pozisyon kontrolü
            while ($currentTokens < $targetTokens && $position < $contentLength) {
                $substring = mb_substr($content, $position, null, 'UTF-8');
                $currentTokens = $this->countTokens($substring);
                $position++;
                
                // Sonsuz döngü koruması
                if ($position > $contentLength) {
                    break;
                }
            }
            
            // Pozisyon güvenliği
            $position = min($position, $contentLength);
            return mb_substr($content, $position, null, 'UTF-8');
            
        } catch (\Exception $e) {
            // Hata durumunda basit substring kullan
            \Log::warning('getOverlap error, using simple substring: ' . $e->getMessage());
            $contentLength = strlen($content);
            $overlapBytes = min($overlapSize * 4, $contentLength); // Yaklaşık byte hesaplama
            return substr($content, -$overlapBytes);
        }
    }

    /**
     * Akıllı overlap oluşturur - kelime sınırlarında böler
     */
    private function getSmartOverlap(string $content, int $overlapSize, bool $preserveWords = true): string
    {
        if ($this->countTokens($content) <= $overlapSize) {
            return $content;
        }
        
        if (!$preserveWords) {
            return $this->getOverlap($content, $overlapSize);
        }
        
        // Overlap boyutundan biraz daha fazla al
        $extendedOverlap = $this->getOverlap($content, $overlapSize + 50);
        
        // İlk kelime sınırını bul (UTF-8 güvenli)
        $firstWordPos = mb_strpos($extendedOverlap, ' ', 0, 'UTF-8');
        if ($firstWordPos !== false) {
            return trim(mb_substr($extendedOverlap, $firstWordPos, null, 'UTF-8'));
        }
        
        return trim($extendedOverlap);
    }

    /**
     * Chunk'ı kelime sınırlarında böler
     */
    private function trimToWordBoundary(string $content, bool $preserveWords = true): string
    {
        if (!$preserveWords) {
            return $content;
        }
        
        // Son kelimeyi tamamla
        $lastSpacePos = mb_strrpos($content, ' ', 0, 'UTF-8');
        if ($lastSpacePos !== false) {
            // Son kelime yarım kalmışsa, onu kaldır
            $lastWord = mb_substr($content, $lastSpacePos + 1, null, 'UTF-8');
            if ($this->countTokens($lastWord) < 3) { // Çok kısa kelimeleri kaldır
                $content = mb_substr($content, 0, $lastSpacePos, 'UTF-8');
            }
        }
        
        return trim($content);
    }

    /**
     * Content tipini tespit eder
     */
    private function detectContentType(string $content): string
    {
        $content = strtolower($content);
        
        if (preg_match('/\b(ürün|product|item|goods)\b/', $content)) {
            return 'product';
        }
        
        if (preg_match('/\b(soru|cevap|faq|question|answer)\b/', $content)) {
            return 'faq';
        }
        
        if (preg_match('/\b(blog|makale|article|post)\b/', $content)) {
            return 'blog';
        }
        
        if (preg_match('/\b(yorum|review|rating|değerlendirme)\b/', $content)) {
            return 'review';
        }
        
        if (preg_match('/\b(kategori|category|sınıf)\b/', $content)) {
            return 'category';
        }
        
        return 'general';
    }

    /**
     * Metadata çıkarır
     */
    private function extractMetadata(string $content): array
    {
        $metadata = [];
        
        // Anahtar kelimeler
        $keywords = $this->extractKeywords($content);
        if (!empty($keywords)) {
            $metadata['keywords'] = $keywords;
        }
        
        // Sayısal değerler
        if (preg_match_all('/\d+(?:\.\d+)?/', $content, $matches)) {
            $metadata['numbers'] = $matches[0];
        }
        
        // Para birimi
        if (preg_match_all('/\d+(?:\.\d+)?\s*(?:TL|USD|EUR|₺|\$|€)/', $content, $matches)) {
            $metadata['currencies'] = $matches[0];
        }
        
        // Tarihler
        if (preg_match_all('/\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}/', $content, $matches)) {
            $metadata['dates'] = $matches[0];
        }
        
        return $metadata;
    }

    /**
     * Anahtar kelimeleri çıkarır
     */
    private function extractKeywords(string $content): array
    {
        // Stop words
        $stopWords = ['ve', 'veya', 'ile', 'için', 'bu', 'şu', 'o', 'bir', 'da', 'de', 'mi', 'mı', 'mu', 'mü'];
        
        // Content'i temizle
        $content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content);
        $words = preg_split('/\s+/', strtolower($content));
        
        // Stop words'leri filtrele ve sayıları say
        $wordCount = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $wordCount[$word] = ($wordCount[$word] ?? 0) + 1;
            }
        }
        
        // En çok geçen kelimeleri döndür
        arsort($wordCount);
        return array_slice(array_keys($wordCount), 0, 10);
    }

    /**
     * JSON content'i chunk'lara böler
     */
    public function chunkJsonContent(string $jsonContent, array $config = []): array
    {
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON content');
        }
        
        $chunks = [];
        $chunkIndex = 0;
        $maxItemsPerChunk = $config['max_items_per_chunk'] ?? 1; // Default 1 item per chunk
        $preserveStructure = $config['preserve_structure'] ?? true;
        
        if (is_array($data)) {
            $items = array_values($data);
            $chunks = array_chunk($items, $maxItemsPerChunk);
            $totalChunks = count($chunks);
            
            return array_map(function($chunk, $index) use ($maxItemsPerChunk, $items, $totalChunks) {
                // Her chunk için detaylı metadata
                $metadata = [
                    'item_count' => count($chunk),
                    'data_type' => 'array',
                    'chunk_type' => 'json_items',
                    'start_index' => ($index * $maxItemsPerChunk),
                    'end_index' => ($index * $maxItemsPerChunk) + count($chunk) - 1,
                    'total_items' => count($items),
                    'chunk_number' => $index + 1,
                    'total_chunks' => $totalChunks
                ];
                
                // İlk item'dan detaylı bilgi al
                if (!empty($chunk)) {
                    $firstItem = $chunk[0];
                    if (is_array($firstItem)) {
                        $metadata['sample_keys'] = array_keys($firstItem);
                        
                        // Ürün bilgilerini metadata'ya ekle
                        if (isset($firstItem['id'])) {
                            $metadata['product_id'] = $firstItem['id'];
                        }
                        if (isset($firstItem['title'])) {
                            $metadata['product_title'] = $firstItem['title'];
                        }
                        if (isset($firstItem['category'])) {
                            $metadata['product_category'] = $firstItem['category'];
                        }
                        if (isset($firstItem['price'])) {
                            $metadata['product_price'] = $firstItem['price'];
                        }
                        if (isset($firstItem['rating'])) {
                            $metadata['product_rating'] = $firstItem['rating'];
                        }
                        
                        // İlk 3 değeri örnek olarak al
                        $metadata['sample_values'] = array_slice(array_values($firstItem), 0, 3);
                    }
                }
                
                // Chunk content'ini oluştur
                $chunkContent = json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                
                // Content type'ı belirle
                $contentType = 'json';
                if (isset($chunk[0]['category'])) {
                    $contentType = 'product';
                } elseif (isset($chunk[0]['title'])) {
                    $contentType = 'product';
                }
                
                return [
                    'chunk_index' => $index,
                    'content' => $chunkContent,
                    'content_hash' => hash('sha256', $chunkContent),
                    'chunk_size' => $this->countTokens($chunkContent),
                    'word_count' => $this->countWordsInJson($chunk),
                    'content_type' => $contentType,
                    'metadata' => $metadata
                ];
            }, $chunks, array_keys($chunks));
        }
        
        return [];
    }

    /**
     * JSON içeriğindeki kelime sayısını hesaplar
     */
    private function countWordsInJson($data): int
    {
        if (is_string($data)) {
            return str_word_count($data);
        }
        
        if (is_array($data)) {
            $wordCount = 0;
            foreach ($data as $item) {
                $wordCount += $this->countWordsInJson($item);
            }
            return $wordCount;
        }
        
        if (is_numeric($data)) {
            return 1; // Sayıları 1 kelime olarak say
        }
        
        return 0;
    }

    /**
     * CSV content'i chunk'lara böler - Gelişmiş algoritma
     */
    public function chunkCsvContent(string $csvContent, array $config = []): array
    {
        $chunks = [];
        $chunkIndex = 0;
        $maxRowsPerChunk = $config['max_rows_per_chunk'] ?? 50;
        $preserveRows = $config['preserve_rows'] ?? true;
        
        $lines = explode("\n", $csvContent);
        $header = array_shift($lines); // Header'ı al
        
        // Boş satırları temizle
        $lines = array_filter($lines, function($line) {
            return !empty(trim($line));
        });
        
        // Satırları chunk'lara böl
        $rowChunks = array_chunk($lines, $maxRowsPerChunk);
        
        foreach ($rowChunks as $index => $rowChunk) {
            // Chunk'ı oluştur
            $chunkContent = $header . "\n" . implode("\n", $rowChunk);
            
            // Metadata'yı zenginleştir
            $metadata = [
                'row_count' => count($rowChunk),
                'has_header' => true,
                'chunk_type' => 'csv_rows',
                'start_row' => ($index * $maxRowsPerChunk) + 1,
                'end_row' => ($index * $maxRowsPerChunk) + count($rowChunk)
            ];
            
            // CSV yapısını analiz et
            if (!empty($rowChunk)) {
                $firstRow = $rowChunk[0];
                $columns = str_getcsv($firstRow);
                $metadata['column_count'] = count($columns);
                $metadata['sample_data'] = array_slice($columns, 0, 3); // İlk 3 sütun
            }
            
            $chunks[] = [
                'chunk_index' => $chunkIndex++,
                'content' => $chunkContent,
                'content_hash' => hash('sha256', $chunkContent),
                'chunk_size' => $this->countTokens($chunkContent),
                'word_count' => $this->countWordsInCsv($chunkContent),
                'content_type' => 'csv',
                'metadata' => $metadata
            ];
        }
        
        return $chunks;
    }

    /**
     * CSV içeriğindeki kelime sayısını hesaplar
     */
    private function countWordsInCsv(string $csvContent): int
    {
        // CSV'den sadece metin içeriğini çıkar
        $textContent = preg_replace('/,/', ' ', $csvContent); // Virgülleri boşluk yap
        $textContent = preg_replace('/"/', '', $textContent); // Tırnak işaretlerini kaldır
        
        return str_word_count($textContent);
    }

    /**
     * Akıllı chunk boyutlandırma yapar
     */
    public function smartChunkContent(string $content, array $config = []): array
    {
        $config['preserve_words'] = true;
        $config['smart_sizing'] = true;
        
        // Content tipini otomatik tespit et
        $contentType = $this->detectContentType($content);
        
        switch ($contentType) {
            case 'csv':
                return $this->chunkCsvContent($content, $config);
            case 'json':
                return $this->chunkJsonContent($content, $config);
            case 'xml':
                return $this->chunkXmlContent($content, $config);
            default:
                return $this->chunkContent($content, $config);
        }
    }

    /**
     * XML content'i chunk'lara böler
     */
    public function chunkXmlContent(string $xmlContent, array $config = []): array
    {
        $chunks = [];
        $chunkIndex = 0;
        $maxElementsPerChunk = $config['max_elements_per_chunk'] ?? 20;
        
        // XML'i parse et
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            throw new \InvalidArgumentException('Invalid XML content');
        }
        
        // XML'i düz metne çevir
        $textContent = $this->xmlToText($xml);
        
        // Metin olarak chunk'la
        return $this->chunkContent($textContent, $config);
    }

    /**
     * XML'i düz metne çevirir
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
     * Chunk kalitesini değerlendirir
     */
    public function evaluateChunkQuality(array $chunks): array
    {
        $quality = [
            'total_chunks' => count($chunks),
            'avg_chunk_size' => 0,
            'avg_word_count' => 0,
            'incomplete_words' => 0,
            'incomplete_sentences' => 0,
            'overlap_quality' => 0
        ];
        
        if (empty($chunks)) {
            return $quality;
        }
        
        $totalSize = 0;
        $totalWords = 0;
        
        foreach ($chunks as $chunk) {
            $totalSize += $chunk['chunk_size'];
            $totalWords += $chunk['word_count'];
            
            // Yarım kelime kontrolü
            if (preg_match('/\b\w{1,2}$/', $chunk['content'])) {
                $quality['incomplete_words']++;
            }
            
            // Yarım cümle kontrolü
            if (!preg_match('/[.!?]\s*$/', $chunk['content'])) {
                $quality['incomplete_sentences']++;
            }
        }
        
        $quality['avg_chunk_size'] = round($totalSize / count($chunks));
        $quality['avg_word_count'] = round($totalWords / count($chunks));
        $quality['overlap_quality'] = round((1 - ($quality['incomplete_words'] + $quality['incomplete_sentences']) / count($chunks)) * 100);
        
        return $quality;
    }

    /**
     * Semantic search için chunk'ları filtreler
     */
    public function filterChunksByRelevance(array $chunks, array $searchTerms, float $minRelevance = 0.75): array
    {
        $relevantChunks = [];
        
        foreach ($chunks as $chunk) {
            $relevanceScore = $this->calculateChunkRelevance($chunk, $searchTerms);
            
            if ($relevanceScore >= $minRelevance) {
                $chunk['relevance_score'] = $relevanceScore;
                $chunk['matched_terms'] = $this->findMatchedTermsInChunk($chunk, $searchTerms);
                $relevantChunks[] = $chunk;
            }
        }
        
        // Relevance score'a göre sırala
        usort($relevantChunks, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });
        
        return $relevantChunks;
    }

    /**
     * Chunk için relevance score hesaplar
     */
    private function calculateChunkRelevance(array $chunk, array $searchTerms): float
    {
        $score = 0.0;
        $content = mb_strtolower($chunk['content']);
        
        // Ana terimler için yüksek puan
        foreach ($searchTerms['primary'] ?? [] as $term) {
            if (mb_strpos($content, mb_strtolower($term)) !== false) {
                $score += 0.4;
            }
        }
        
        // Benzer kelimeler için orta puan
        foreach ($searchTerms['similar'] ?? [] as $term) {
            if (mb_strpos($content, mb_strtolower($term)) !== false) {
                $score += 0.25;
            }
        }
        
        // İlgili kategoriler için bonus puan
        if (isset($chunk['content_type'])) {
            foreach ($searchTerms['categories'] ?? [] as $category) {
                if (mb_strpos(mb_strtolower($chunk['content_type']), mb_strtolower($category)) !== false) {
                    $score += 0.2;
                }
            }
        }
        
        // Metadata'dan bonus puan
        if (isset($chunk['metadata'])) {
            $metadata = is_string($chunk['metadata']) ? $chunk['metadata'] : json_encode($chunk['metadata']);
            foreach ($searchTerms['primary'] ?? [] as $term) {
                if (mb_strpos(mb_strtolower($metadata), mb_strtolower($term)) !== false) {
                    $score += 0.15;
                }
            }
        }
        
        return min($score, 1.0);
    }

    /**
     * Chunk'ta eşleşen terimleri bulur
     */
    private function findMatchedTermsInChunk(array $chunk, array $searchTerms): array
    {
        $matchedTerms = [];
        $content = mb_strtolower($chunk['content']);
        
        // Ana terimler
        foreach ($searchTerms['primary'] ?? [] as $term) {
            if (mb_strpos($content, mb_strtolower($term)) !== false) {
                $matchedTerms[] = [
                    'term' => $term,
                    'type' => 'primary',
                    'score' => 0.4
                ];
            }
        }
        
        // Benzer kelimeler
        foreach ($searchTerms['similar'] ?? [] as $term) {
            if (mb_strpos($content, mb_strtolower($term)) !== false) {
                $matchedTerms[] = [
                    'term' => $term,
                    'type' => 'similar',
                    'score' => 0.25
                ];
            }
        }
        
        return $matchedTerms;
    }

    /**
     * Fuzzy matching ile chunk arama
     */
    public function fuzzyChunkSearch(array $chunks, string $query, float $minSimilarity = 0.6): array
    {
        $query = mb_strtolower($query);
        $queryWords = explode(' ', $query);
        
        $fuzzyResults = [];
        
        foreach ($chunks as $chunk) {
            $similarityScore = $this->calculateFuzzySimilarity($chunk, $queryWords);
            
            if ($similarityScore >= $minSimilarity) {
                $chunk['fuzzy_score'] = $similarityScore;
                $chunk['similarity_details'] = $this->getSimilarityDetails($chunk, $queryWords);
                $fuzzyResults[] = $chunk;
            }
        }
        
        // Fuzzy score'a göre sırala
        usort($fuzzyResults, function($a, $b) {
            return $b['fuzzy_score'] <=> $a['fuzzy_score'];
        });
        
        return $fuzzyResults;
    }

    /**
     * Fuzzy similarity hesaplar
     */
    private function calculateFuzzySimilarity(array $chunk, array $queryWords): float
    {
        $content = mb_strtolower($chunk['content']);
        $totalScore = 0.0;
        $wordCount = count($queryWords);
        
        foreach ($queryWords as $word) {
            if (mb_strlen($word) < 3) continue; // Çok kısa kelimeleri atla
            
            $wordScore = 0.0;
            
            // Tam eşleşme
            if (mb_strpos($content, $word) !== false) {
                $wordScore = 1.0;
            } else {
                // Kısmi eşleşme
                $wordScore = $this->calculatePartialMatch($content, $word);
            }
            
            $totalScore += $wordScore;
        }
        
        return $wordCount > 0 ? $totalScore / $wordCount : 0.0;
    }

    /**
     * Kısmi eşleşme skoru hesaplar
     */
    private function calculatePartialMatch(string $content, string $word): float
    {
        $maxScore = 0.0;
        $wordLength = mb_strlen($word);
        
        // Word'ün alt string'lerini kontrol et
        for ($i = 0; $i < $wordLength - 2; $i++) {
            for ($j = $i + 3; $j <= $wordLength; $j++) {
                $substring = mb_substr($word, $i, $j - $i);
                if (mb_strpos($content, $substring) !== false) {
                    $substringLength = mb_strlen($substring);
                    $score = $substringLength / $wordLength;
                    $maxScore = max($maxScore, $score);
                }
            }
        }
        
        return $maxScore * 0.8; // Kısmi eşleşme için maksimum %80 puan
    }

    /**
     * Similarity detaylarını getirir
     */
    private function getSimilarityDetails(array $chunk, array $queryWords): array
    {
        $details = [];
        $content = mb_strtolower($chunk['content']);
        
        foreach ($queryWords as $word) {
            if (mb_strlen($word) < 3) continue;
            
            if (mb_strpos($content, $word) !== false) {
                $details[] = [
                    'word' => $word,
                    'match_type' => 'exact',
                    'score' => 1.0
                ];
            } else {
                $partialScore = $this->calculatePartialMatch($content, $word);
                if ($partialScore > 0) {
                    $details[] = [
                        'word' => $word,
                        'match_type' => 'partial',
                        'score' => $partialScore
                    ];
                }
            }
        }
        
        return $details;
    }

    /**
     * Chunk'ları kategorilere göre gruplar
     */
    public function groupChunksByCategory(array $chunks): array
    {
        $grouped = [];
        
        foreach ($chunks as $chunk) {
            $category = $chunk['content_type'] ?? 'general';
            
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            
            $grouped[$category][] = $chunk;
        }
        
        return $grouped;
    }

    /**
     * Chunk'ları metadata'ya göre filtreler
     */
    public function filterChunksByMetadata(array $chunks, array $filters): array
    {
        $filtered = [];
        
        foreach ($chunks as $chunk) {
            $matches = true;
            
            foreach ($filters as $key => $value) {
                if (isset($chunk['metadata'][$key])) {
                    $chunkValue = $chunk['metadata'][$key];
                    
                    if (is_array($value)) {
                        // Array değer kontrolü
                        if (!in_array($chunkValue, $value)) {
                            $matches = false;
                            break;
                        }
                    } else {
                        // String değer kontrolü
                        if (mb_strtolower($chunkValue) !== mb_strtolower($value)) {
                            $matches = false;
                            break;
                        }
                    }
                } else {
                    $matches = false;
                    break;
                }
            }
            
            if ($matches) {
                $filtered[] = $chunk;
            }
        }
        
        return $filtered;
    }
}

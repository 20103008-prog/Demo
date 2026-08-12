<?php

namespace App\Services;

use App\Models\PayrollFaq;
use App\Models\PayrollSetting;

class AiQueryService
{
    /**
     * Cosine-similarity based FAQ matching for payroll queries.
     *
     * @return array{draft:?string,category:?string,confidence:float,needs_manual_review:bool}
     */
    public function match(string $text): array
    {
        $threshold = PayrollSetting::getFloat('ai_confidence_threshold', 0.35);
        $queryTokens = $this->tokenize($text);
        $queryVector = $this->termFrequency($queryTokens);

        $best = null;
        $bestScore = 0.0;

        foreach (PayrollFaq::active()->get() as $faq) {
            $docTokens = $this->tokenize($faq->title.' '.$faq->keywords.' '.$faq->response.' '.$faq->category);
            $docVector = $this->termFrequency($docTokens);
            $score = $this->cosineSimilarity($queryVector, $docVector);

            // Boost when keyword tokens appear directly
            foreach ($faq->keywordList() as $kw) {
                if (str_contains(strtolower($text), $kw)) {
                    $score = min(1.0, $score + 0.15);
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        if (! $best || $bestScore < $threshold) {
            return [
                'draft' => null,
                'category' => $best?->category,
                'confidence' => round($bestScore, 4),
                'needs_manual_review' => true,
            ];
        }

        return [
            'draft' => $best->response,
            'category' => $best->category,
            'confidence' => round($bestScore, 4),
            'needs_manual_review' => $bestScore < PayrollSetting::getFloat('ai_high_confidence', 0.55),
        ];
    }

    protected function tokenize(string $text): array
    {
        $text = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $text) ?? '');
        $stop = ['the', 'a', 'an', 'is', 'are', 'of', 'to', 'and', 'or', 'for', 'in', 'on', 'my', 'me', 'i', 'please', 'what', 'how', 'when'];

        return array_values(array_filter(
            preg_split('/\s+/', $text) ?: [],
            fn ($t) => strlen($t) > 1 && ! in_array($t, $stop, true)
        ));
    }

    protected function termFrequency(array $tokens): array
    {
        $tf = [];
        $n = max(1, count($tokens));
        foreach ($tokens as $t) {
            $tf[$t] = ($tf[$t] ?? 0) + 1;
        }
        foreach ($tf as $k => $v) {
            $tf[$k] = $v / $n;
        }

        return $tf;
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));

        foreach ($keys as $k) {
            $va = $a[$k] ?? 0.0;
            $vb = $b[$k] ?? 0.0;
            $dot += $va * $vb;
            $magA += $va * $va;
            $magB += $vb * $vb;
        }

        if ($magA <= 0 || $magB <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }
}

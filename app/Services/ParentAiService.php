<?php

namespace App\Services;

use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParentAiService
{
    /**
     * Generate personalized AI pedagogical advice for parents.
     */
    public function generateAdvice(Child $child, array $performanceData, string $userQuestion): string
    {
        $question = trim($userQuestion);

        // Strict Pedagogy Relevance Check: Question MUST relate to learning, parenting, or child development
        $educationalKeywords = ['child', 'kid', 'learn', 'count', 'math', 'number', 'shape', 'letter', 'read', 'phonic', 'sound', 'screen', 'time', 'struggl', 'confus', 'school', 'game', 'practice', 'age', 'strengthen', 'focus', 'help', 'parent', 'star', 'coin', 'progress'];
        $isRelevant = false;
        foreach ($educationalKeywords as $word) {
            if (str_contains(strtolower($question), $word)) {
                $isRelevant = true;
                break;
            }
        }

        if (!$isRelevant && !empty($question)) {
            return "🌟 **AI Parent Coach Guardrail:** I am specialized specifically in early-childhood learning, CBC curriculum, and parenting tips for young kids! Feel free to ask me anything about " . e($child->name) . "'s counting, phonics, screen time, or learning progress!";
        }

        // Try calling Free LLM Provider (Groq API, OpenRouter, or Gemini)
        $groqKey = config('services.groq.key') ?? env('GROQ_API_KEY');
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if (!empty($groqKey)) {
            $aiResponse = $this->callGroqApi($groqKey, $child, $performanceData, $question);
            if ($aiResponse) return $aiResponse;
        }

        if (!empty($geminiKey)) {
            $aiResponse = $this->callGeminiApi($geminiKey, $child, $performanceData, $question);
            if ($aiResponse) return $aiResponse;
        }

        // Fallback: Real Data-Driven Smart Pedagogical Response
        return $this->generateDataDrivenFallback($child, $performanceData, $question);
    }

    /**
     * Call Groq API (Free Tier: 14,400 Requests/Day with Llama 3).
     */
    protected function callGroqApi(string $apiKey, Child $child, array $perf, string $question): ?string
    {
        try {
            $systemPrompt = "You are a warm, supportive Early Childhood Education & CBC Curriculum AI Specialist advising parents about their child's learning. " .
                "The child's name is {$child->name}. " .
                "Accuracy rate: {$perf['accuracy_rate']}%. Completed missions: {$perf['passed_missions']} out of {$perf['total_missions']}. " .
                "Total Stars: {$child->total_stars}, Star Coins: {$child->star_coins}. " .
                "Keep your answers concise, encouraging, actionable (2-4 bullet points or short paragraphs), and include simple 1-minute home activities.";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(8)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $question ?: "Give me a summary of {$child->name}'s learning progress and a fun 1-minute home activity."],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 350,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }
        } catch (\Throwable $e) {
            Log::warning('Groq AI API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Call Google Gemini API (Free Tier).
     */
    protected function callGeminiApi(string $apiKey, Child $child, array $perf, string $question): ?string
    {
        try {
            $prompt = "You are an early childhood education specialist advising a parent about their child {$child->name}. " .
                "{$child->name}'s score accuracy: {$perf['accuracy_rate']}%. Completed missions: {$perf['passed_missions']}. " .
                "Parent Question: {$question}. " .
                "Provide a brief, warm, practical answer with a 1-minute home learning activity.";

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(8)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini AI API call failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Real Data-Driven Smart Fallback (No API key required).
     */
    protected function generateDataDrivenFallback(Child $child, array $perf, string $question): string
    {
        $qLower = strtolower($question);
        $name = e($child->name);
        $accuracy = $perf['accuracy_rate'] ?? 80;
        $passed = $perf['passed_missions'] ?? 0;
        $total = $perf['total_missions'] ?? 1;

        if (str_contains($qLower, 'struggl') || str_contains($qLower, 'mistake') || str_contains($qLower, 'confus') || str_contains($qLower, 'wrong')) {
            return "💡 **Pedagogy Insight for {$name}:** Children aged 3–5 learn through multi-sensory repetition! Currently, {$name} has completed **{$passed} of {$total} missions** with an overall accuracy of **{$accuracy}%**.\n\n" .
                "**1-Minute Home Activity:** Pick 3 physical items at home (like spoons or apples). Have {$name} touch each item as they count aloud: *'One, Two, Three!'* Physical touching builds strong neural connections!";
        }

        if (str_contains($qLower, 'time') || str_contains($qLower, 'screen') || str_contains($qLower, 'how long') || str_contains($qLower, 'minutes')) {
            $limit = $child->daily_time_limit_minutes > 0 ? "{$child->daily_time_limit_minutes} minutes" : "unlimited (recommended 20–30 mins)";
            return "⏰ **Screen Time Guidance for {$name}:** Current daily limit is set to **{$limit}**.\n\n" .
                "Early childhood specialists recommend 15 to 30 minutes of interactive educational play daily. Micro-learning sessions yield higher memory retention than long marathons!";
        }

        if (str_contains($qLower, 'read') || str_contains($qLower, 'phonic') || str_contains($qLower, 'letter') || str_contains($qLower, 'sound')) {
            return "📖 **Phonics & Language Tip:** Practice letter sounds using physical objects around the house! For example, point at a Banana and emphasize the '/b/' sound (*'b-b-banana'*).";
        }

        return "🌟 **AI Parent Coach for {$name}:** {$name} is making great progress! " .
            "They have earned **{$child->total_stars} Stars** and **{$child->star_coins} Star Coins** across **{$passed} completed missions** with **{$accuracy}% accuracy**.\n\n" .
            "**Recommended Action:** Celebrate their achievements with positive praise, keep sessions under 30 minutes, and assign their next focus mission!";
    }
}

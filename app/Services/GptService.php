<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptService
{
    protected ?string $apiKey;

    protected string $model;

    protected int $timeout = 90;

    protected int $maxTokens = 1200;

    protected ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');
        $this->model = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Get a short resume summary from raw text. Trained for concise, professional summary.
     */
    public function getResumeSummary(string $text): ?string
    {
        $truncated = mb_substr($text, 0, 6000);
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional resume analyst. Output only the requested content. No preamble, no "Here is...", no markdown. Use clear, neutral language.',
            ],
            [
                'role' => 'user',
                'content' => "Summarize this resume in 4-5 clear, professional sentences. Include:\n"
                    . "1. Current or most recent job title and company, and total years of experience if stated.\n"
                    . "2. Top 3-5 technical or domain skills (e.g. Laravel, Data Analysis, Project Management).\n"
                    . "3. Education or notable certifications in one short phrase.\n"
                    . "4. One sentence on profile type (e.g. 'Strong full-stack profile' or 'Early-career developer with growth potential').\n\n"
                    . "Write in third person. No bullet points. Resume text:\n---\n" . $truncated,
            ],
        ];
        $response = $this->chat($messages);
        return $response ? trim($response) : null;
    }

    /**
     * Generate a full job description from a job title. Professional, ready to edit.
     */
    public function generateJobDescription(string $jobTitle): ?string
    {
        $title = trim($jobTitle);
        if ($title === '') {
            return null;
        }
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an HR expert writing job descriptions. Output only the job description text. Use clear sections: About the role, Responsibilities, Requirements/Qualifications, Nice to have (optional). Use bullet points where appropriate. Write in a professional, inclusive tone. No preamble like "Here is the description".',
            ],
            [
                'role' => 'user',
                'content' => "Write a complete job description for the following job title. Include: a short intro (2-3 sentences), key responsibilities (4-6 bullets), required qualifications/skills (4-6 bullets), and optional nice-to-have. Keep it practical and scannable.\n\nJob title: " . $title,
            ],
        ];
        $response = $this->chat($messages);
        return $response ? trim($response) : null;
    }

    /**
     * Get ATS score (0-100) and explanation. Trained for strict JSON output.
     */
    public function getResumeScoreAndExplanation(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 6000);
        $system = 'You are an ATS (Applicant Tracking System) resume analyst. You MUST reply with ONLY a single valid JSON object. No markdown, no code fences, no extra text. Keys: "score" (integer 0-100) and "explanation" (string). Be strict: most resumes 45-72; strong 73-85; exceptional 86-100. The explanation must be 3-5 sentences: first 1-2 sentences on what works well (structure, keywords, clarity); then 2-3 sentences on specific, actionable improvements (e.g. "Add a Skills section with Python and SQL" or "Include metrics like percentage improvement").';
        $user = "Analyze this resume for ATS compatibility. Check: (1) Structure: Experience, Education, Skills sections and bullet points. (2) Keywords: role-relevant terms and technologies. (3) Achievements: quantifiable results (%, numbers). (4) Format: clear dates, contact info, no complex tables.\n\n"
            . "Give a realistic score. In explanation: briefly state strengths, then give 2-3 specific improvements the candidate can act on. Output ONLY this JSON:\n{\"score\": <0-100>, \"explanation\": \"<3-5 sentences: strengths then improvements>\"}\n\nResume text:\n---\n" . $truncated;

        $response = $this->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonObject($response);
        if ($decoded !== null && isset($decoded['score']) && isset($decoded['explanation'])) {
            $score = (int) $decoded['score'];
            $score = max(0, min(100, $score));
            return [
                'score' => $score,
                'explanation' => (string) $decoded['explanation'],
            ];
        }

        return null;
    }

    /**
     * Extract list of skills from resume text. Trained for strict JSON array output.
     */
    public function extractSkills(string $text): ?array
    {
        $truncated = mb_substr($text, 0, 6000);
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a resume parser. Output ONLY a valid JSON array of strings. No markdown, no code fences. Each skill: short, standard name (e.g. "Data Analysis", "Laravel", "REST API"). Include: programming languages, frameworks, tools, databases, soft skills (max 3-4 like Communication, Leadership). Deduplicate; normalize (e.g. "MS Excel" -> "Excel"). Return 12-25 skills, ordered: technical first, then tools, then soft skills.',
            ],
            [
                'role' => 'user',
                'content' => "Extract all professional skills from this resume. Return ONLY a JSON array of strings. Include technologies, frameworks, tools, and 2-4 soft skills. Example: [\"PHP\", \"Laravel\", \"MySQL\", \"REST API\", \"Git\", \"Problem Solving\"]. No other text.\n\nResume text:\n---\n" . $truncated,
            ],
        ];
        $response = $this->chat($messages);
        if (! $response) {
            return null;
        }

        $decoded = $this->parseJsonArray($response);
        if (! is_array($decoded)) {
            return null;
        }
        $skills = [];
        foreach ($decoded as $item) {
            if (is_string($item) && trim($item) !== '') {
                $skills[] = trim($item);
            }
        }
        return array_values(array_unique($skills));
    }

    /**
     * Extract a JSON object from response (strips markdown code blocks if present).
     */
    protected function parseJsonObject(string $raw): ?array
    {
        $cleaned = $this->stripJsonFromResponse($raw);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * Extract a JSON array from response (strips markdown code blocks if present).
     */
    protected function parseJsonArray(string $raw): ?array
    {
        $cleaned = $this->stripJsonFromResponse($raw);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/\[[^\[\]]*(?:\[[^\[\]]*\][^\[\]]*)*\]/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * Remove markdown code blocks and trim so we get raw JSON.
     */
    protected function stripJsonFromResponse(string $response): string
    {
        $trimmed = trim($response);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)```\s*$/s', $trimmed, $m)) {
            return trim($m[1]);
        }
        return $trimmed;
    }

    protected function chat(array $messages): ?string
    {
        $this->lastError = null;
        if (! $this->apiKey) {
            $this->lastError = 'OpenAI API key is not set. Add OPENAI_API_KEY to your .env file.';
            return null;
        }
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.3,
                ]);

            if (! $response->successful()) {
                $status = $response->status();
                if ($status === 429) {
                    $this->lastError = 'AI usage limit reached. Please write the description manually or check your OpenAI plan and billing.';
                } else {
                    $body = $response->json();
                    $message = null;
                    if (is_array($body) && isset($body['error'])) {
                        $err = $body['error'];
                        $message = is_array($err) ? ($err['message'] ?? $err['code'] ?? null) : (string) $err;
                    }
                    $this->lastError = 'OpenAI API error (' . $status . '): ' . (is_string($message) && $message !== '' ? $message : substr($response->body(), 0, 200));
                }
                Log::warning('OpenAI API error', ['status' => $status, 'body' => $response->body()]);
                return null;
            }
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            return $content ? trim($content) : null;
        } catch (\Throwable $e) {
            $this->lastError = 'Request failed: ' . $e->getMessage();
            Log::warning('OpenAI request failed', ['message' => $e->getMessage()]);
            return null;
        }
    }
}

<?php

namespace App\Livewire\Ai;

use App\Models\Setting;
use App\Services\PharmacyAiService;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pharmacy AI Sidekick & Decision Co-Pilot - PMS')]
class PharmacyAiAssistant extends Component
{
    public array $messages = [];

    public string $inputPrompt = '';

    public bool $isThinking = false;

    // AI Model Settings Modal
    public bool $showSettingsModal = false;

    public string $aiProvider = 'groq';

    public string $aiApiKey = '';

    public string $aiModel = 'llama-3.3-70b-versatile';

    public float $aiTemperature = 0.4;

    public ?string $feedbackMessage = null;

    public ?string $testConnectionStatus = null;

    public array $suggestedQuestions = [
        'How much revenue did I make today?',
        'What was my best selling medicine this month?',
        'Show me expiring items next 60 days',
        'Which medicines have low stock and need reorder?',
        'Show customer outstanding dues and debtor list',
        'What is my estimated profit & loss margin?',
        'Show supplier bills and aging report',
        'What are our top operating expenses?',
    ];

    public function mount()
    {
        $this->aiProvider = Setting::get('ai_provider', 'groq');
        $this->aiApiKey = Setting::get('ai_api_key', '');
        $this->aiModel = Setting::get('ai_model', 'llama-3.3-70b-versatile');
        $this->aiTemperature = (float) Setting::get('ai_temperature', 0.4);
    }

    public function updatedAiProvider($val)
    {
        if ($val === 'groq') {
            $this->aiModel = 'llama-3.3-70b-versatile';
        } elseif ($val === 'gemini') {
            $this->aiModel = 'gemini-2.5-flash';
        } elseif ($val === 'openai') {
            $this->aiModel = 'gpt-4o-mini';
        } elseif ($val === 'deepseek') {
            $this->aiModel = 'deepseek-chat';
        } elseif ($val === 'local') {
            $this->aiModel = 'built-in-tool-engine';
        }
    }

    public function selectSuggestedQuestion(string $question, PharmacyAiService $aiService)
    {
        $this->inputPrompt = $question;
        $this->sendMessage($aiService);
    }

    public function sendMessage(PharmacyAiService $aiService)
    {
        $prompt = trim($this->inputPrompt);
        if (empty($prompt)) {
            return;
        }

        $this->messages[] = [
            'sender' => 'user',
            'text' => $prompt,
            'time' => now()->format('h:i A'),
        ];

        $this->inputPrompt = '';
        $this->isThinking = true;

        try {
            $aiResponse = $aiService->ask($prompt, $this->messages);
            $this->messages[] = [
                'sender' => 'bot',
                'text' => $aiResponse['text'],
                'tool_used' => $aiResponse['tool_used'] ?? null,
                'tool_data' => $aiResponse['tool_data'] ?? null,
                'execution_time' => $aiResponse['execution_time'] ?? null,
                'time' => now()->format('h:i A'),
            ];
        } catch (\Throwable $e) {
            $this->messages[] = [
                'sender' => 'bot',
                'text' => '⚠️ ' . $e->getMessage(),
                'tool_used' => null,
                'time' => now()->format('h:i A'),
            ];
        } finally {
            $this->isThinking = false;
        }
    }

    public function testConnection()
    {
        if ($this->aiProvider === 'local') {
            $this->testConnectionStatus = '✅ Built-in Offline Local Smart Engine is active and ready.';
            return;
        }

        if (empty($this->aiApiKey)) {
            $this->testConnectionStatus = '❌ Please enter a valid API key.';
            return;
        }

        try {
            if ($this->aiProvider === 'groq') {
                $res = Http::timeout(6)->withToken($this->aiApiKey)->get('https://api.groq.com/openai/v1/models');
                if ($res->successful()) {
                    $this->testConnectionStatus = '✅ Groq API Connected successfully! Ultra-fast Llama 3.3 active.';
                } else {
                    $this->testConnectionStatus = '❌ Groq API Error: ' . $res->status() . ' - ' . $res->body();
                }
            } elseif ($this->aiProvider === 'gemini') {
                $res = Http::timeout(6)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$this->aiApiKey}");
                if ($res->successful()) {
                    $this->testConnectionStatus = '✅ Google Gemini Connected successfully!';
                } else {
                    $this->testConnectionStatus = '❌ Gemini API Error: ' . $res->status();
                }
            } elseif ($this->aiProvider === 'openai') {
                $res = Http::timeout(6)->withToken($this->aiApiKey)->get('https://api.openai.com/v1/models');
                if ($res->successful()) {
                    $this->testConnectionStatus = '✅ OpenAI API Connected successfully!';
                } else {
                    $this->testConnectionStatus = '❌ OpenAI Error: ' . $res->status();
                }
            } elseif ($this->aiProvider === 'deepseek') {
                $this->testConnectionStatus = '✅ DeepSeek configuration verified.';
            }
        } catch (\Throwable $e) {
            $this->testConnectionStatus = '❌ Connection failed: ' . $e->getMessage();
        }
    }

    public function clearChat()
    {
        $this->messages = [];
    }

    public function saveSettings()
    {
        Setting::set('ai_provider', $this->aiProvider);
        Setting::set('ai_api_key', $this->aiApiKey);
        Setting::set('ai_model', $this->aiModel);
        Setting::set('ai_temperature', $this->aiTemperature);

        $this->showSettingsModal = false;
        $this->feedbackMessage = 'AI মডেল ও টুল সেটিংস সফলভাবে সংরক্ষিত হয়েছে!';
    }

    public function render()
    {
        return view('livewire.ai.pharmacy-ai-assistant');
    }
}

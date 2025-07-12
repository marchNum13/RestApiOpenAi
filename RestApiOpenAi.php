<?php

/**
 * Kelas RestApiOpenAi
 *
 * Sebuah pembungkus (wrapper) PHP untuk API OpenAI, menyediakan akses mudah ke fungsionalitas utama
 * seperti chat completions, pembuatan gambar, embeddings, dan lainnya.
 */
class RestApiOpenAi {
    /**
     * @var string Kunci API OpenAI.
     */
    private $apiKey;

    /**
     * @var string URL dasar untuk API OpenAI.
     */
    private $baseUrl = 'https://api.openai.com/v1/';

    /**
     * Konstruktor untuk menginisialisasi klien API.
     *
     * @param string $apiKey Kunci API OpenAI Anda.
     */
    public function __construct(string $apiKey) {
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key is required.');
        }
        $this->apiKey = $apiKey;
    }

    /**
     * Mengirimkan permintaan ke API OpenAI.
     *
     * @param string $endpoint Endpoint API yang akan dipanggil (contoh: 'chat/completions').
     * @param array $data Data yang akan dikirim bersama permintaan.
     * @param string $method Metode HTTP yang digunakan (contoh: 'POST').
     * @param bool $isMultipart Apakah permintaan ini berjenis multipart/form-data (untuk unggahan file).
     * @return mixed Respons JSON yang sudah di-decode atau data mentah untuk unduhan file.
     */
    private function sendRequest(string $endpoint, array $data, string $method = 'POST', bool $isMultipart = false) {
        $ch = curl_init($this->baseUrl . $endpoint);

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
        ];

        if ($isMultipart) {
            // Untuk unggahan file, cURL akan mengatur header Content-Type secara otomatis.
            $payload = $data;
        } else {
            $headers[] = 'Content-Type: application/json';
            $payload = json_encode($data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Batas waktu 60 detik

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Untuk endpoint yang mengembalikan data biner seperti audio
        if ($httpCode === 200 && ($endpoint === 'audio/speech')) {
            return $response; 
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400 || isset($decodedResponse['error'])) {
            $errorMessage = $decodedResponse['error']['message'] ?? 'An unknown API error occurred.';
            throw new \Exception('API Error: ' . $errorMessage . ' (HTTP Code: ' . $httpCode . ')');
        }

        return $decodedResponse;
    }

    /**
     * Membuat sebuah chat completion.
     *
     * @param array $params Parameter untuk panggilan API chat completion.
     * contoh: [
     * 'model' => 'gpt-4',
     * 'messages' => [['role' => 'user', 'content' => 'Hello!']],
     * 'max_tokens' => 150
     * ]
     * @return array Respons dari API.
     */
    public function chat(array $params): array {
        // Atur model default jika tidak disediakan
        if (!isset($params['model'])) {
            $params['model'] = 'gpt-3.5-turbo';
        }
        return $this->sendRequest('chat/completions', $params);
    }

    /**
     * Membuat gambar dari prompt teks menggunakan DALL·E.
     *
     * @param string $prompt Prompt teks untuk pembuatan gambar.
     * @param int $n Jumlah gambar yang akan dibuat.
     * @param string $size Ukuran gambar yang akan dibuat (contoh: '1024x1024').
     * @param string $model Model yang akan digunakan (contoh: 'dall-e-3').
     * @return array Respons dari API.
     */
    public function generateImage(string $prompt, int $n = 1, string $size = '1024x1024', string $model = 'dall-e-3'): array {
        $data = [
            'model'  => $model,
            'prompt' => $prompt,
            'n'      => $n,
            'size'   => $size,
        ];
        return $this->sendRequest('images/generations', $data);
    }
    
    /**
     * Membuat embeddings untuk sebuah teks input.
     *
     * @param string $input Teks yang akan dibuatkan embedding.
     * @param string $model Model yang akan digunakan (contoh: 'text-embedding-3-small').
     * @return array Respons dari API.
     */
    public function createEmbedding(string $input, string $model = 'text-embedding-3-small'): array {
        $data = [
            'input' => $input,
            'model' => $model,
        ];
        return $this->sendRequest('embeddings', $data);
    }
    
    /**
     * Mentranskripsikan file audio menjadi teks menggunakan Whisper.
     *
     * @param string $filePath Path absolut ke file audio.
     * @param string $model Model yang akan digunakan (contoh: 'whisper-1').
     * @return array Respons dari API dengan teks hasil transkripsi.
     */
    public function transcribeAudio(string $filePath, string $model = 'whisper-1'): array {
        if (!file_exists($filePath)) {
            throw new \Exception("Audio file not found at path: {$filePath}");
        }

        $data = [
            'model' => $model,
            'file'  => new \CURLFile($filePath),
        ];
        
        return $this->sendRequest('audio/transcriptions', $data, 'POST', true);
    }
    
    /**
     * Membuat suara dari teks.
     *
     * @param string $input Teks yang akan diubah menjadi suara.
     * @param string $voice Suara yang akan digunakan (contoh: 'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer').
     * @param string $model Model yang akan digunakan (contoh: 'tts-1' atau 'tts-1-hd').
     * @return string Data audio biner mentah (contoh: MP3).
     */
    public function createSpeech(string $input, string $voice = 'alloy', string $model = 'tts-1'): string {
        $data = [
            'model' => $model,
            'input' => $input,
            'voice' => $voice,
        ];

        return $this->sendRequest('audio/speech', $data);
    }
}
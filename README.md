# OpenAI API Client (PHP)

Ini adalah klien PHP dasar untuk berinteraksi dengan OpenAI REST API. Class ini menyediakan fungsionalitas untuk berinteraksi dengan berbagai model AI, termasuk model bahasa (chat), pembuatan gambar (DALL·E), embedding, transkripsi audio (Whisper), dan text-to-speech.

## Daftar Isi
- [Fitur Utama](#fitur-utama)
- [Instalasi](#instalasi)
- [Konfigurasi API Key](#konfigurasi-api-key)
- [Cara Penggunaan](#cara-penggunaan)
  - [Inisialisasi Class](#inisialisasi-class)
  - [Chat Completion (Model Teks)](#chat-completion-model-teks)
  - [Image Generation (DALL·E)](#image-generation-dalle)
  - [Text Embedding](#text-embedding)
  - [Audio Transcription (Whisper)](#audio-transcription-whisper)
  - [Text-to-Speech (TTS)](#text-to-speech-tts)
- [Catatan Penting](#catatan-penting)

## Fitur Utama
Class `RestApiOpenAi` ini mencakup fungsionalitas untuk endpoint OpenAI API berikut:

1.  `POST /v1/chat/completions` - Mengirimkan permintaan chat ke model bahasa seperti GPT-4, GPT-3.5, dll.
2.  `POST /v1/images/generations` - Membuat gambar dari deskripsi teks menggunakan DALL·E.
3.  `POST /v1/embeddings` - Mengubah teks menjadi representasi vektor numerik.
4.  `POST /v1/audio/transcriptions` - Mengubah file audio menjadi teks menggunakan model Whisper.
5.  `POST /v1/audio/speech` - Mengubah teks menjadi audio menggunakan model Text-to-Speech (TTS).

## Instalasi
1.  Pastikan Anda memiliki PHP (versi 7.0 atau lebih tinggi direkomendasikan) dengan ekstensi `cURL` diaktifkan.
2.  Unduh file `RestApiOpenAi.php` dan simpan di direktori proyek Anda.

## Konfigurasi API Key
Untuk menggunakan class ini, Anda perlu mendapatkan API Key dari akun OpenAI Anda:

1.  Masuk ke akun OpenAI Anda di [platform.openai.com](https://platform.openai.com/).
2.  Navigasi ke bagian `API keys`.
3.  Buat *secret key* baru.
4.  **Simpan Secret Key Anda dengan sangat aman.** Kunci ini hanya akan ditampilkan satu kali.

**Penting:** Untuk lingkungan produksi, **jangan pernah *hardcode*** API Key Anda langsung di dalam kode sumber. Gunakan *environment variables* atau file konfigurasi yang aman (misalnya, file `.env` yang tidak di-commit ke Git).

## Cara Penggunaan

### Inisialisasi Class
Sertakan file `RestApiOpenAi.php` dan buat instance class dengan API Key Anda.

```php
<?php
  require_once 'RestApiOpenAi.php';

  // GANTI DENGAN API KEY ASLI ANDA
  $api_key = 'YOUR_OPENAI_API_KEY';

  $openai = new RestApiOpenAi($api_key);
?>
```

### Chat Completion (Model Teks)
`chat(array $params)` - Mengirimkan percakapan ke model bahasa.
- `$params`: Array yang berisi `model`, `messages`, dan parameter opsional lain seperti `max_tokens` dan `temperature`.

```php
<?php
  // ... inisialisasi class ...

  echo "<h2>Contoh Chat Completion</h2>";
  $chatParams = [
      'model' => 'gpt-4o', // atau gpt-3.5-turbo
      'messages' => [
          ['role' => 'system', 'content' => 'Anda adalah asisten yang cerdas.'],
          ['role' => 'user', 'content' => 'Apa saja 3 keajaiban dunia kuno?']
      ],
      'max_tokens' => 150,
  ];

  $response = $openai->chat($chatParams);
  $reply = $response['choices'][0]['message']['content'];
  echo "<p><strong>Jawaban AI:</strong></p>";
  echo "<pre>" . htmlspecialchars($reply) . "</pre>";
?>
```

### Image Generation (DALL·E)
`generateImage(string $prompt, int $n = 1, string $size = '1024x1024', string $model = 'dall-e-3')` - Membuat gambar berdasarkan deskripsi teks.
- `$prompt`: Deskripsi gambar yang diinginkan.
- `$n`: Jumlah gambar yang akan dibuat.
- `$size`: Ukuran gambar.
- `$model`: Model DALL·E yang akan digunakan.

```php
<?php
  // ... inisialisasi class ...

  echo "<h2>Contoh Image Generation</h2>";
  $prompt = "A cute cat programming on a laptop, digital art";
  $imageResponse = $openai->generateImage($prompt, 1, '1024x1024', 'dall-e-3');
  
  if ($imageResponse) {
      $imageUrl = $imageResponse['data'][0]['url'];
      echo "<p>Prompt: " . htmlspecialchars($prompt) . "</p>";
      echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="Generated Image" width="400">';
  } else {
      echo "Gagal membuat gambar.";
  }
?>
```

### Text Embedding
`createEmbedding(string $input, string $model = 'text-embedding-3-small')` - Mengubah teks menjadi vektor numerik.
- `$input`: Teks yang akan di-embed.
- `$model`: Model embedding yang akan digunakan.

```php
<?php
  // ... inisialisasi class ...

  echo "<h2>Contoh Text Embedding</h2>";
  $textToEmbed = "OpenAI sangat hebat dalam pemrosesan bahasa alami.";
  $embeddingResponse = $openai->createEmbedding($textToEmbed);
  
  if ($embeddingResponse) {
      $vector = $embeddingResponse['data'][0]['embedding'];
      echo "<p>Berhasil membuat embedding untuk teks: '" . htmlspecialchars($textToEmbed) . "'</p>";
      echo "<p>Dimensi Vektor: " . count($vector) . "</p>";
      echo "<p>Contoh 5 nilai pertama: </p>";
      echo "<pre>" . print_r(array_slice($vector, 0, 5), true) . "</pre>";
  } else {
      echo "Gagal membuat embedding.";
  }
?>
```

### Audio Transcription (Whisper)
`transcribeAudio(string $filePath, string $model = 'whisper-1')` - Mengubah ucapan dalam file audio menjadi teks.
- `$filePath`: Path absolut ke file audio Anda (misal: `.mp3`, `.wav`).
- `$model`: Model Whisper yang digunakan.

```php
<?php
  // ... inisialisasi class ...

  // Anda harus memiliki file audio untuk contoh ini.
  // Ganti 'path/to/your/audio.mp3' dengan path file audio Anda.
  $audioFilePath = 'path/to/your/audio.mp3';

  echo "<h2>Contoh Audio Transcription</h2>";
  if (file_exists($audioFilePath)) {
      $transcription = $openai->transcribeAudio($audioFilePath);
      if ($transcription) {
          echo "<p>Teks hasil transkripsi:</p>";
          echo "<blockquote>" . htmlspecialchars($transcription['text']) . "</blockquote>";
      } else {
          echo "Gagal mentranskripsi audio.";
      }
  } else {
      echo "<p>File audio tidak ditemukan di '{$audioFilePath}'. Sediakan file audio yang valid untuk menjalankan fitur ini.</p>";
  }
?>
```

### Text-to-Speech (TTS)
`createSpeech(string $input, string $voice = 'alloy', string $model = 'tts-1')` - Mengubah teks menjadi file audio.
- `$input`: Teks yang ingin diubah menjadi suara.
- `$voice`: Pilihan suara (`alloy`, `echo`, `fable`, `onyx`, `nova`, `shimmer`).
- `$model`: Model TTS yang digunakan.

```php
<?php
  // ... inisialisasi class ...

  echo "<h2>Contoh Text-to-Speech</h2>";
  $textToSpeak = "Halo dunia! Ini adalah contoh suara yang dihasilkan oleh API OpenAI.";
  $audioData = $openai->createSpeech($textToSpeak, 'nova');
  
  if ($audioData) {
      $audioFileName = 'output_speech.mp3';
      file_put_contents($audioFileName, $audioData);
      echo "<p>Teks telah diubah menjadi audio dan disimpan sebagai <strong>{$audioFileName}</strong>.</p>";
      echo '<audio controls><source src="' . $audioFileName . '" type="audio/mpeg">Browser Anda tidak mendukung elemen audio.</audio>';
  } else {
      echo "Gagal membuat file audio.";
  }
?>
```

## Catatan Penting
- **Keamanan API Key**: Selalu jaga kerahasiaan API Key Anda. Jangan pernah membagikannya atau menyimpannya di tempat yang dapat diakses publik.
- **Model Terbaru**: OpenAI terus memperbarui model mereka. Pastikan untuk memeriksa dokumentasi resmi OpenAI untuk mengetahui nama model terbaru dan paling sesuai untuk kebutuhan Anda (misal: `gpt-4o`, `dall-e-3`, `tts-1-hd`).
- **Error Handling**: Implementasikan blok `try-catch` di sekitar panggilan API untuk menangani kemungkinan error, seperti kunci API yang tidak valid, permintaan yang salah format, atau masalah pada server OpenAI.
- **Rate Limits & Kuota**: Perhatikan batasan permintaan (rate limits) dan kuota penggunaan pada akun OpenAI Anda. Jika Anda membuat terlalu banyak permintaan dalam waktu singkat, API akan mengembalikan error.
- **Ekstensi PHP cURL**: Pastikan ekstensi `php_curl` sudah terpasang dan aktif di lingkungan PHP Anda, karena class ini membutuhkannya untuk melakukan permintaan HTTP.
- **Parameter Tambahan**: Sebagian besar fungsi mendukung parameter tambahan yang tersedia di API OpenAI. Anda dapat menyertakannya dalam array parameter utama (misalnya, menambahkan `temperature` atau `top_p` di dalam array `$params` untuk fungsi `chat`).

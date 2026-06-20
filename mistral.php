<?php
require_once __DIR__ . '/config.php';

function callMistralAI(string $prompt): string
{
    $payload = [
        'model' => MISTRAL_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Tu es un expert SEO, marketing local et réputation Google Business.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7
    ];

    $ch = curl_init(MISTRAL_ENDPOINT);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MISTRAL_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Erreur cURL : ' . curl_error($ch);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    return $data['choices'][0]['message']['content'] ?? 'Erreur API Mistral : réponse invalide.';
}
?>

<?php
function configuracaoOpenAI(): array
{
  $apiKey = getenv('OPENAI_API_KEY');
  if ($apiKey === false || trim($apiKey) === '') {
    $arquivoLocal = __DIR__ . '/config.local.php';
    if (is_file($arquivoLocal)) {
      $configLocal = require $arquivoLocal;
      if (!is_array($configLocal)) {
        throw new RuntimeException('config.local.php deve retornar um array de configurações.');
      }
      $apiKey = $configLocal['openai_api_key'] ?? '';
    }
  }

  return [
    'api_key' => trim((string)$apiKey),
    'model' => 'gpt-image-2.6-sunburst',
  ];
}

<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function responder(int $status, array $dados): void
{
  http_response_code($status);
  echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Allow: POST');
  responder(405, ['ok' => false, 'mensagem' => 'Método não permitido.']);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
$tokenEnviado = $_POST['csrf_token'] ?? '';
$tokenSessao = $_SESSION['csrf_token'] ?? '';
if (!is_string($tokenEnviado) || !is_string($tokenSessao) || $tokenSessao === '' || !hash_equals($tokenSessao, $tokenEnviado)) {
  responder(403, ['ok' => false, 'mensagem' => 'Atualize a página antes de tentar novamente.']);
}
if (($_POST['consentimento'] ?? '') !== '1') {
  responder(422, ['ok' => false, 'mensagem' => 'Confirme o envio da foto à OpenAI para gerar a prévia.']);
}

if (!isset($_FILES['foto']) || !is_array($_FILES['foto'])) {
  responder(422, ['ok' => false, 'mensagem' => 'Escolha uma foto de corpo inteiro para continuar.']);
}
$foto = $_FILES['foto'];
if ($foto['error'] !== UPLOAD_ERR_OK) {
  $mensagem = $foto['error'] === UPLOAD_ERR_INI_SIZE || $foto['error'] === UPLOAD_ERR_FORM_SIZE
    ? 'A foto ultrapassa o limite permitido. Escolha um arquivo de até 5 MB.'
    : 'Não foi possível receber a foto. Escolha outra imagem e tente novamente.';
  responder(422, ['ok' => false, 'mensagem' => $mensagem]);
}
if ($foto['size'] < 1 || $foto['size'] > 5 * 1024 * 1024 || !is_uploaded_file($foto['tmp_name'])) {
  responder(422, ['ok' => false, 'mensagem' => 'Escolha uma foto válida de até 5 MB.']);
}

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($foto['tmp_name']);
$dimensoes = @getimagesize($foto['tmp_name']);
if (!in_array($mime, $tiposPermitidos, true) || !$dimensoes || $dimensoes[0] < 360 || $dimensoes[1] < 480 || $dimensoes[0] * $dimensoes[1] > 20000000) {
  responder(422, ['ok' => false, 'mensagem' => 'Use uma foto JPG, PNG ou WEBP nítida, de corpo inteiro e com até 20 megapixels.']);
}

$pecas = ['dress' => 'vestido', 'suit' => 'terno', 'shirt' => 'camisa'];
$modelos = ['classic' => 'clássico', 'modern' => 'moderno', 'formal' => 'social'];
$cores = [
  '#c98e9b' => 'rosa antigo',
  '#d8c7ae' => 'bege',
  '#526b68' => 'verde sálvia',
  '#35445c' => 'azul-marinho',
  '#292929' => 'preto',
  '#eee5dc' => 'off-white',
];
$comprimentos = ['long' => 'longo', 'mid' => 'médio', 'short' => 'curto'];
$peca = $_POST['peca'] ?? '';
$modelo = $_POST['modelo'] ?? '';
$cor = $_POST['cor'] ?? '';
$comprimento = $_POST['length'] ?? '';
if (!isset($pecas[$peca], $modelos[$modelo], $cores[$cor], $comprimentos[$comprimento])) {
  responder(422, ['ok' => false, 'mensagem' => 'Confira as opções da peça, modelo, cor e comprimento.']);
}

$faixasMedidas = [
  'busto' => [30, 250],
  'cintura' => [30, 250],
  'quadril' => [30, 250],
  'ombro' => [20, 100],
  'manga' => [20, 150],
  'comprimento' => [20, 250],
];
$medidas = [];
foreach ($faixasMedidas as $campo => $faixa) {
  $valor = trim((string)($_POST[$campo] ?? ''));
  if ($valor === '') {
    continue;
  }
  if (!preg_match('/^\d{1,3}(?:[.,]\d{1,2})?$/D', $valor)) {
    responder(422, ['ok' => false, 'mensagem' => 'Confira se as medidas estão em centímetros e use apenas números.']);
  }
  $valorNumerico = (float)str_replace(',', '.', $valor);
  if ($valorNumerico < $faixa[0] || $valorNumerico > $faixa[1]) {
    responder(422, ['ok' => false, 'mensagem' => 'Confira se cada medida está dentro do intervalo indicado.']);
  }
  $medidas[] = ucfirst($campo) . ': ' . rtrim(rtrim(number_format($valorNumerico, 2, '.', ''), '0'), '.') . ' cm';
}

require_once dirname(__DIR__) . '/openai_config.php';
$configuracao = configuracaoOpenAI();
if ($configuracao['api_key'] === '') {
  responder(503, ['ok' => false, 'mensagem' => 'A IA ainda não está configurada. O ateliê precisa adicionar sua chave OpenAI em config.local.php.']);
}
if (!function_exists('curl_init')) {
  responder(503, ['ok' => false, 'mensagem' => 'A extensão cURL do PHP não está habilitada neste servidor.']);
}

$agora = time();
$ultimaGeracao = (int)($_SESSION['ultima_prova_virtual'] ?? 0);
if ($ultimaGeracao > 0 && $agora - $ultimaGeracao < 30) {
  responder(429, ['ok' => false, 'mensagem' => 'Aguarde alguns segundos antes de gerar outra prévia.']);
}
$_SESSION['ultima_prova_virtual'] = $agora;
$identificadorSeguranca = hash('sha256', session_id());
session_write_close();

$descricaoMedidas = $medidas
  ? ' Medidas informadas pelo cliente, em centímetros: ' . implode('; ', $medidas) . '. Use-as somente como referência visual; não altere o corpo nem afirme que o caimento exato foi calculado.'
  : '';
$prompt = 'Edite esta foto para criar uma prova virtual de moda fotorrealista da pessoa usando um(a) ' . $modelos[$modelo] . ' ' . $pecas[$peca] . ' ' . $comprimentos[$comprimento] . ' na cor ' . $cores[$cor] . '.' .
  ' Preserve a identidade e os traços do rosto, idade aparente, tom de pele, cabelo, proporções reais do corpo, pose, expressão, mãos, enquadramento, fundo e iluminação da foto. Mude somente a roupa visível e, quando necessário, as partes do corpo naturalmente cobertas pela peça. Mostre tecido, textura, costuras, dobras e caimento de maneira natural e plausível. Não afine, engrosse ou reconfigure o corpo, não mude a pessoa, não acrescente acessórios e não invente detalhes que não foram pedidos.' .
  $descricaoMedidas .
  ' O resultado é uma referência visual aproximada, não uma medição corporal, molde ou garantia de ajuste. Mantenha uma fotografia natural e sem texto, molduras ou marcas d’água.';

$conteudoFoto = file_get_contents($foto['tmp_name']);
if ($conteudoFoto === false) {
  error_log('Falha ao ler a foto temporária da prova virtual.');
  responder(500, ['ok' => false, 'mensagem' => 'Não foi possível preparar a foto. Tente novamente.']);
}

$curl = curl_init('https://api.openai.com/v1/images/edits');
$campos = [
  'model' => $configuracao['model'],
  'image' => function_exists('curl_file_create') ? curl_file_create($foto['tmp_name'], $mime, 'foto') : '@' . $foto['tmp_name'],
  'prompt' => $prompt,
];
if (!empty($identificadorSeguranca)) {
  $campos['safety_identifier'] = $identificadorSeguranca;
}

set_time_limit(110);
curl_setopt_array($curl, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $campos,
  CURLOPT_HTTPHEADER => [
    'Authorization: Bearer ' . $configuracao['api_key'],
  ],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CONNECTTIMEOUT => 15,
  CURLOPT_TIMEOUT => 100,
  CURLOPT_SSL_VERIFYPEER => true,
  CURLOPT_SSL_VERIFYHOST => 2,
]);
$respostaOpenAI = curl_exec($curl);
$erroCurl = curl_errno($curl);
$statusOpenAI = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($respostaOpenAI === false) {
  error_log('Falha na conexão com a API de imagem OpenAI; cURL ' . $erroCurl . '.');
  responder(502, ['ok' => false, 'mensagem' => 'Não foi possível conectar à IA agora. Tente novamente mais tarde.']);
}
$dadosOpenAI = json_decode($respostaOpenAI, true);
if ($statusOpenAI < 200 || $statusOpenAI >= 300 || !is_array($dadosOpenAI)) {
  error_log('A API de imagem OpenAI respondeu com HTTP ' . $statusOpenAI . '.');
  if ($statusOpenAI === 401 || $statusOpenAI === 403) {
    responder(502, ['ok' => false, 'mensagem' => 'A chave ou a conta da IA precisa ser conferida pelo ateliê.']);
  }
  if ($statusOpenAI === 429) {
    responder(429, ['ok' => false, 'mensagem' => 'O limite de uso da IA foi atingido. Tente novamente mais tarde.']);
  }
  responder(502, ['ok' => false, 'mensagem' => 'A IA não conseguiu gerar esta prévia. Tente outra foto ou tente mais tarde.']);
}

$imagemBase64 = $dadosOpenAI['data'][0]['b64_json'] ?? '';
$imagem = is_string($imagemBase64) && $imagemBase64 !== '' ? base64_decode($imagemBase64, true) : false;
$mimeSaida = $imagem === false ? false : $finfo->buffer($imagem);
if ($imagem === false || !in_array($mimeSaida, ['image/webp', 'image/png', 'image/jpeg'], true)) {
  error_log('A API de imagem OpenAI retornou uma imagem ausente ou em formato inesperado.');
  responder(502, ['ok' => false, 'mensagem' => 'A IA retornou uma imagem inválida. Tente gerar a prévia novamente.']);
}

responder(200, [
  'ok' => true,
  'imagem' => $imagemBase64,
  'mime' => $mimeSaida,
]);

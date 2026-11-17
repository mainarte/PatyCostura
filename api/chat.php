<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
$mensagem = trim($_POST['mensagem'] ?? '');
if ($mensagem === '') { echo json_encode(['resposta'=>'Pode escrever sua dúvida para eu ajudar.']); exit; }
if (empty($pdo)) {
  echo json_encode(['resposta'=>'No momento não consegui responder. Você pode falar com a Paty pelo WhatsApp (41) 98842-2231.'], JSON_UNESCAPED_UNICODE);
  exit;
}
try { $st=$pdo->prepare("INSERT INTO mensagens_chat (mensagem, origem) VALUES (?, 'site')"); $st->execute([$mensagem]); } catch(Throwable $e) {}
$m = mb_strtolower($mensagem, 'UTF-8');
if (preg_match('/preço|preco|valor|custa|quanto/', $m)) {
 $resposta = 'Os valores variam conforme a peça e o tipo de trabalho. Consulte a tabela de referência no site. Para um orçamento correto, envie uma foto e explique o ajuste pelo WhatsApp: (41) 98842-2231.';
} elseif (preg_match('/serviço|servico|fazem|trabalham|reforma|barra|zíper|ziper|vestido|terno|camisa/', $m)) {
 $resposta = 'Fazemos reformas e ajustes em geral, barras de calça (inclusive original, quando possível), ajustes de cintura e mangas, troca de zíper e confecção sob medida de vestidos, camisas e peças de alfaiataria.';
} elseif (preg_match('/medida|medidas|tirar medida/', $m)) {
 $resposta = 'Você pode preencher a ficha “Minhas medidas” no site. Informe somente as medidas que souber; a ficha é uma referência inicial e a Paty poderá confirmar tudo durante o atendimento.';
} elseif (preg_match('/endereço|endereco|local|onde|mapa|chegar/', $m)) {
 $resposta = 'Estamos na Rua Flávio Dallegrave, 10763, Sobrado 15, Bairro Barreirinha, Curitiba-PR. O mapa e a rota estão na seção “Onde estamos”.';
} elseif (preg_match('/horário|horario|aberto|funciona/', $m)) {
 $resposta = 'Para confirmar horários e combinar uma visita, fale diretamente com a Paty pelo WhatsApp (41) 98842-2231.';
} else {
 $resposta = 'Obrigada pela mensagem! Posso ajudar com informações sobre serviços, preços, medidas e localização. Se preferir atendimento personalizado, fale com a Paty pelo WhatsApp (41) 98842-2231.';
}
echo json_encode(['resposta'=>$resposta], JSON_UNESCAPED_UNICODE);

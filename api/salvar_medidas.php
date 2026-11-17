<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'mensagem'=>'Método não permitido.']); exit; }
if (empty($pdo)) { http_response_code(503); echo json_encode(['ok'=>false,'mensagem'=>'O banco de dados ainda não está disponível.']); exit; }
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
if ($nome === '' || $telefone === '') { http_response_code(422); echo json_encode(['ok'=>false,'mensagem'=>'Informe nome e WhatsApp.']); exit; }
$campos = ['busto','cintura','quadril','ombro','manga','comprimento'];
$valores = [];
foreach ($campos as $c) { $v = $_POST[$c] ?? ''; $valores[$c] = ($v === '') ? null : (float)$v; }
try {
 $sql = "INSERT INTO medidas (nome, telefone, tipo_peca, busto, cintura, quadril, ombro, manga, comprimento, observacoes) VALUES (:nome,:telefone,:tipo,:busto,:cintura,:quadril,:ombro,:manga,:comprimento,:obs)";
 $st = $pdo->prepare($sql);
 $st->execute([':nome'=>$nome, ':telefone'=>$telefone, ':tipo'=>$_POST['tipo_peca'] ?? 'Outra peça',
 ':busto'=>$valores['busto'], ':cintura'=>$valores['cintura'], ':quadril'=>$valores['quadril'], ':ombro'=>$valores['ombro'], ':manga'=>$valores['manga'], ':comprimento'=>$valores['comprimento'], ':obs'=>trim($_POST['observacoes'] ?? '')]);
 echo json_encode(['ok'=>true,'mensagem'=>'Medidas enviadas! Obrigada. Para agilizar, você também pode chamar a Paty pelo WhatsApp.']);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['ok'=>false,'mensagem'=>'Não foi possível salvar agora. Tente novamente ou fale pelo WhatsApp.']); }

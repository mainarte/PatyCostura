<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/config.php';
$servicos = [];
if (!empty($pdo)) {
  try {
    $stmt = $pdo->query("SELECT nome, descricao, preco_inicial, unidade FROM servicos WHERE ativo=1 ORDER BY ordem, id");
    $servicos = $stmt->fetchAll();
  } catch (Throwable $e) { $servicos = []; }
}
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Paty Costuras e Reformas em Geral. Ajustes, reformas e confecção sob medida em Curitiba.">
  <title>Paty Costuras | Costura, Reformas e Dressmaker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/simulator.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="#inicio"><span class="brand-mark">P</span><span>Paty <small>Costuras & Reformas</small></span></a>
  <button class="menu-toggle" aria-label="Abrir menu" id="menuToggle">☰</button>
  <nav id="mainNav">
    <a href="#inicio">Início</a><a href="#sobre">Sobre</a><a href="#servicos">Serviços</a>
    <a href="#precos">Preços</a><a href="#medidas">Minhas medidas</a><a href="#simulador">Simulador</a><a href="#contato">Contato</a>
  </nav>
  <a class="nav-cta" href="https://wa.me/5541988422231" target="_blank" rel="noopener">Fale com a Paty</a>
</header>

<main>
<section class="hero" id="inicio">
  <div class="hero-copy">
    <span class="eyebrow">Cuidado em cada ponto</span>
    <h1>Suas roupas, com o <em>caimento perfeito.</em></h1>
    <p>Reformas, ajustes e peças sob medida com carinho, técnica e atenção a cada detalhe.</p>
    <div class="hero-actions"><a class="button primary" href="#contato">Solicitar orçamento</a><a class="button light" href="#servicos">Conheça os serviços</a></div>
    <div class="hero-note"><span>✦</span> Atendimento personalizado em Curitiba</div>
  </div>
  <div class="hero-art">
    <div class="fabric-card"><div class="fabric-circle"></div><div class="needle">✦</div><div class="fabric-caption">Feito para<br><strong>vestir você</strong></div></div>
    <div class="floating-tag">Dressmaker<br><small>Modelagem e costura</small></div>
  </div>
</section>

<section class="trust-strip"><div><strong>01</strong><span>Atendimento próximo</span></div><div><strong>02</strong><span>Acabamento cuidadoso</span></div><div><strong>03</strong><span>Peças sob medida</span></div><div><strong>04</strong><span>Reformas em geral</span></div></section>

<section class="section about" id="sobre">
  <div class="about-visual"><div class="thread-art">〰</div><span class="round-label">Costura com<br>propósito</span></div>
  <div class="section-copy"><span class="eyebrow">Um pouco sobre nós</span><h2>Detalhes que fazem<br><em>toda a diferença.</em></h2>
    <p>A Paty Costuras e Reformas em Geral oferece soluções para valorizar suas roupas e acompanhar seu estilo. A proprietária é especializada em <strong>Dressmaker</strong>, com cursos especializados em diversas áreas da costura e confecção.</p>
    <p>Cada peça recebe atenção individual, respeitando suas preferências, necessidades e o caimento desejado.</p>
    <a class="text-link" href="#contato">Vamos conversar <span>→</span></a>
  </div>
</section>

<section class="section services-section" id="servicos">
  <div class="section-heading"><span class="eyebrow">O que fazemos</span><h2>Serviços para cuidar<br>do seu <em>guarda-roupa.</em></h2><p>Daquela pequena correção à criação de uma peça especial.</p></div>
  <div class="service-grid">
    <article class="service-card"><div class="service-icon">✂</div><h3>Reformas e ajustes</h3><p>Ajustes de cintura, laterais, mangas, zíperes, punhos e muito mais.</p></article>
    <article class="service-card"><div class="service-icon">⌁</div><h3>Confecção sob medida</h3><p>Peças desenvolvidas de acordo com suas medidas e preferências.</p></article>
    <article class="service-card"><div class="service-icon">♡</div><h3>Vestidos e ocasiões</h3><p>Modelos casuais ou especiais, com atenção ao caimento e acabamento.</p></article>
    <article class="service-card"><div class="service-icon">▤</div><h3>Alfaiataria</h3><p>Ajustes em ternos, camisas, calças sociais e peças de alfaiataria.</p></article>
  </div>
</section>

<section class="section pricing" id="precos">
  <div class="section-heading"><span class="eyebrow">Transparência e cuidado</span><h2>Tabela de <em>serviços.</em></h2><p>Valores iniciais de referência. O preço final depende do tecido, modelo, complexidade e avaliação da peça.</p></div>
  <div class="price-table-wrap"><table class="price-table"><thead><tr><th>Serviço</th><th>Detalhes</th><th>Valor inicial</th></tr></thead><tbody>
  <?php if ($servicos): foreach ($servicos as $s): ?>
    <tr><td><strong><?=h($s['nome'])?></strong></td><td><?=h($s['descricao'])?></td><td class="price">R$ <?=number_format((float)$s['preco_inicial'],2,',','.')?> <?=h($s['unidade'])?></td></tr>
  <?php endforeach; else: ?>
    <tr><td>Barra de calça original</td><td>Preservando a barra original quando possível</td><td class="price">A consultar</td></tr>
    <tr><td>Barra simples</td><td>Calças, saias e vestidos</td><td class="price">A consultar</td></tr>
    <tr><td>Ajuste de cintura</td><td>Calças, saias e vestidos</td><td class="price">A consultar</td></tr>
    <tr><td>Troca de zíper</td><td>Conforme peça e tipo de zíper</td><td class="price">A consultar</td></tr>
    <tr><td>Ajuste de camisa / manga</td><td>Peças sociais e casuais</td><td class="price">A consultar</td></tr>
    <tr><td>Confecção de vestido</td><td>Sob medida, conforme modelo e tecido</td><td class="price">Sob orçamento</td></tr>
    <tr><td>Ajuste de terno</td><td>Calça, paletó e mangas</td><td class="price">Sob orçamento</td></tr>
  <?php endif; ?></tbody></table></div>
  <p class="fine-print">Os valores exibidos são estimativas e devem ser confirmados diretamente com a profissional antes da execução.</p>
</section>

<section class="section measure-section" id="medidas">
  <div class="section-copy"><span class="eyebrow">Atendimento personalizado</span><h2>Suas medidas, <em>com praticidade.</em></h2><p>Preencha as medidas que você já conhece. A Paty poderá usar essas informações como referência inicial para conversar sobre sua peça.</p><div class="measure-tip">Dica: use uma fita métrica flexível, mantenha-a confortável e informe as medidas em centímetros.</div></div>
  <form class="measure-form" id="measureForm">
    <div class="form-title"><span>Ficha de medidas</span><small>Medidas em centímetros (cm)</small></div>
    <div class="form-grid">
      <label>Nome<input name="nome" required placeholder="Seu nome"></label><label>WhatsApp<input name="telefone" required placeholder="(41) 99999-9999"></label>
      <label>Tipo de peça<select name="tipo_peca"><option>Vestido</option><option>Camisa</option><option>Terno</option><option>Calça</option><option>Saia</option><option>Outra peça</option></select></label>
      <label>Busto<input type="number" name="busto" min="0" step="0.5" placeholder="Ex.: 92"></label>
      <label>Cintura<input type="number" name="cintura" min="0" step="0.5" placeholder="Ex.: 76"></label>
      <label>Quadril<input type="number" name="quadril" min="0" step="0.5" placeholder="Ex.: 100"></label>
      <label>Ombro<input type="number" name="ombro" min="0" step="0.5" placeholder="Ex.: 38"></label>
      <label>Manga / braço<input type="number" name="manga" min="0" step="0.5" placeholder="Ex.: 60"></label>
      <label>Comprimento desejado<input type="number" name="comprimento" min="0" step="0.5" placeholder="Ex.: 100"></label>
    </div>
    <label>Observações<textarea name="observacoes" rows="3" placeholder="Conte um pouco sobre a peça ou o ajuste desejado"></textarea></label>
    <button class="button primary full" type="submit">Enviar minhas medidas</button><p class="form-feedback" id="measureFeedback" aria-live="polite"></p>
    <small class="privacy">Suas medidas serão usadas somente para o atendimento solicitado. Evite inserir informações que não sejam necessárias.</small>
  </form>
</section>

<section class="section simulator" id="simulador">
  <div class="section-heading"><span class="eyebrow">Imagine sua peça</span><h2>Simulador de <em>modelos.</em></h2><p>Compare o avatar ilustrativo com uma prova virtual de IA baseada em uma foto sua.</p></div>
  <div class="simulator-box">
    <div class="sim-controls">
      <label>Peça<select id="garment"><option value="dress">Vestido</option><option value="suit">Terno</option><option value="shirt">Camisa</option></select></label>
      <label>Modelo<select id="styleChoice"><option value="classic">Clássico</option><option value="modern">Moderno</option><option value="formal">Social</option></select></label>
      <label>Cor<select id="colorChoice"><option value="#c98e9b">Rosa antigo</option><option value="#d8c7ae">Bege</option><option value="#526b68">Verde sálvia</option><option value="#35445c">Azul-marinho</option><option value="#292929">Preto</option><option value="#eee5dc">Off-white</option></select></label>
      <label>Comprimento<select id="lengthChoice"><option value="long">Longo</option><option value="mid">Médio</option><option value="short">Curto</option></select></label>
      <div class="sim-note">A imagem de IA é uma simulação visual: não mede o corpo nem comprova o caimento. Confirme modelo e medidas com a profissional.</div>
      <a class="button primary full" id="simWhats" href="https://wa.me/5541988422231" target="_blank" rel="noopener">Conversar sobre este modelo</a>
    </div>
    <div class="simulator-previews">
      <div class="preview-area"><div class="preview-label">Avatar ilustrativo</div><svg id="garmentSvg" viewBox="0 0 420 460" role="img" aria-label="Ilustração da peça selecionada"></svg><p id="previewCaption">Vestido clássico • rosa antigo</p></div>
      <section class="tryon-card" aria-labelledby="tryonTitle">
        <div>
          <div class="preview-label">Prova virtual com IA</div>
          <h3 id="tryonTitle">Veja uma ideia na sua foto.</h3>
          <p class="tryon-intro">Envie uma foto de corpo inteiro, de frente, com a roupa atual visível e boa iluminação. A prévia não substitui prova ou avaliação profissional.</p>
        </div>
        <form id="tryonForm" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <label class="tryon-field">Foto de corpo inteiro
            <input id="tryonPhoto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" required>
            <span class="tryon-help">JPG, PNG ou WEBP; até 5 MB. A foto só sai do seu aparelho ao gerar a imagem.</span>
          </label>
          <div class="tryon-photo-preview" id="tryonPhotoPreviewWrap" hidden>
            <img id="tryonPhotoPreview" alt="Prévia local da foto selecionada">
            <button type="button" class="tryon-remove" id="tryonPhotoRemove">Remover foto</button>
          </div>
          <fieldset class="tryon-measurements">
            <legend>Medidas opcionais em centímetros</legend>
            <div class="tryon-measure-grid">
              <label>Busto<input type="number" name="busto" min="30" max="250" step="0.5" placeholder="Ex.: 92"></label>
              <label>Cintura<input type="number" name="cintura" min="30" max="250" step="0.5" placeholder="Ex.: 76"></label>
              <label>Quadril<input type="number" name="quadril" min="30" max="250" step="0.5" placeholder="Ex.: 100"></label>
              <label>Ombro<input type="number" name="ombro" min="20" max="100" step="0.5" placeholder="Ex.: 38"></label>
              <label>Manga<input type="number" name="manga" min="20" max="150" step="0.5" placeholder="Ex.: 60"></label>
              <label>Comprimento<input type="number" name="comprimento" min="20" max="250" step="0.5" placeholder="Ex.: 100"></label>
            </div>
          </fieldset>
          <label class="tryon-consent"><input type="checkbox" name="consentimento" value="1" id="tryonConsent" required><span>Concordo que minha foto e as medidas preenchidas sejam enviadas à <a href="https://openai.com/policies/privacy-policy/" target="_blank" rel="noopener noreferrer">OpenAI</a> para criar esta prévia. O site não salva esses dados.</span></label>
          <button type="submit" class="button primary full" id="tryonSubmit" disabled>Gerar prévia com IA</button>
          <p class="tryon-feedback" id="tryonFeedback" aria-live="polite">A geração só começa depois do seu consentimento.</p>
        </form>
        <figure class="tryon-result" id="tryonResult" hidden>
          <img id="tryonResultImage" alt="Prévia ilustrativa da roupa gerada por IA">
          <figcaption>Imagem ilustrativa gerada por IA. A cor, o tecido e o caimento reais podem ser diferentes.</figcaption>
        </figure>
      </section>
    </div>
  </div>
</section>

<section class="contact-band" id="contato"><div><span class="eyebrow">Estamos à disposição</span><h2>Vamos dar uma nova vida<br>à sua peça?</h2><p>Entre em contato para tirar dúvidas, consultar valores ou combinar uma avaliação.</p></div><a class="button white" href="https://wa.me/5541988422231?text=Ol%C3%A1%2C%20Paty!%20Gostaria%20de%20um%20or%C3%A7amento." target="_blank" rel="noopener">Chamar no WhatsApp</a></section>

<section class="section location-section">
  <div class="section-copy"><span class="eyebrow">Onde estamos</span><h2>Venha nos <em>visitar.</em></h2><p>Rua Flávio Dallegrave, 10763, Sobrado 15<br>Barreirinha — Curitiba/PR</p><a class="text-link" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=Rua+Fl%C3%A1vio+Dallegrave+10763+Sobrado+15+Barreirinha+Curitiba+PR">Abrir no Google Maps →</a><p class="phone-line">WhatsApp: <strong>(41) 98842-2231</strong></p></div>
  <div class="map-frame"><iframe title="Mapa - Paty Costuras" src="https://www.google.com/maps?q=Rua%20Fl%C3%A1vio%20Dallegrave%2010763%20Sobrado%2015%20Barreirinha%20Curitiba%20PR&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
</section>
</main>

<footer><a class="brand" href="#inicio"><span class="brand-mark">P</span><span>Paty <small>Costuras & Reformas</small></span></a><p>Costura feita com cuidado, técnica e carinho.</p><small>© <?=date('Y')?> Paty Costuras e Reformas em Geral. Todos os direitos reservados.</small></footer>

<button class="chat-launch" id="chatLaunch" aria-label="Abrir atendimento virtual">✦ <span>Fale com a Paty</span></button>
<aside class="chat-widget" id="chatWidget" aria-label="Atendimento virtual" hidden>
  <div class="chat-head"><div><strong>Atendimento Paty</strong><small>Assistente virtual</small></div><button id="chatClose" aria-label="Fechar chat">×</button></div>
  <div class="chat-messages" id="chatMessages"><div class="bot-msg">Olá! Seja bem-vinda(o) à Paty Costuras. Como posso ajudar? Você pode perguntar sobre serviços, preços, medidas ou localização.</div></div>
  <div class="quick-questions"><button data-question="Quais serviços vocês fazem?">Serviços</button><button data-question="Como faço um orçamento?">Orçamento</button><button data-question="Onde fica o ateliê?">Localização</button></div>
  <form id="chatForm"><input id="chatInput" placeholder="Digite sua mensagem..." autocomplete="off" required><button aria-label="Enviar mensagem">➤</button></form>
</aside>
<script src="assets/app.js"></script>
</body></html>

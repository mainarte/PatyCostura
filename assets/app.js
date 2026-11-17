const $ = (s) => document.querySelector(s);
const menuToggle = $('#menuToggle');
menuToggle?.addEventListener('click', () => $('#mainNav').classList.toggle('open'));
document.querySelectorAll('#mainNav a').forEach(a => a.addEventListener('click', () => $('#mainNav').classList.remove('open')));

$('#measureForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const feedback = $('#measureFeedback');
  const button = e.currentTarget.querySelector('button[type="submit"]');
  button.disabled = true; button.textContent = 'Enviando...';
  try {
    const response = await fetch('api/salvar_medidas.php', {method:'POST', body:new FormData(e.currentTarget)});
    const data = await response.json();
    feedback.textContent = data.mensagem;
    feedback.className = 'form-feedback ' + (data.ok ? 'success' : 'error');
    if (data.ok) e.currentTarget.reset();
  } catch { feedback.textContent = 'Não foi possível enviar. Verifique sua conexão ou chame pelo WhatsApp.'; feedback.className = 'form-feedback error'; }
  finally { button.disabled = false; button.textContent = 'Enviar minhas medidas'; }
});

// Simulador gráfico ilustrativo: formas simplificadas para referência visual.
const svg = $('#garmentSvg');
const garment = $('#garment'), styleChoice = $('#styleChoice'), colorChoice = $('#colorChoice'), lengthChoice = $('#lengthChoice');
function renderGarment() {
  const type = garment.value, color = colorChoice.value, style = styleChoice.value, length = lengthChoice.value;
  let shape = '';
  if (type === 'dress') {
    const hem = length === 'long' ? 420 : length === 'mid' ? 350 : 285;
    const waist = style === 'modern' ? 210 : 225;
    shape = `<path d="M165 75 Q210 48 255 75 L278 155 Q${waist+28} 170 ${waist+18} 190 L${style==='modern'?300:280} ${hem} Q210 ${hem+15} ${style==='modern'?120:140} ${hem} L${waist-18} 190 Q${waist-28} 170 142 155 Z" fill="${color}" stroke="#755d5c" stroke-width="3"/><path d="M165 78 L142 155 M255 78 L278 155" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="3"/><path d="M165 78 Q210 105 255 78" fill="none" stroke="#fff" stroke-opacity=".6" stroke-width="3"/>`;
  } else if (type === 'shirt') {
    shape = `<path d="M155 90 L190 68 L210 92 L230 68 L265 90 L315 145 L280 178 L260 155 L260 365 L160 365 L160 155 L140 178 L105 145 Z" fill="${color}" stroke="#755d5c" stroke-width="3"/><path d="M190 68 L210 92 L230 68 L220 130 L200 130 Z" fill="#f4eee8" stroke="#755d5c" stroke-width="2"/><path d="M210 130 V365" stroke="#fff" stroke-opacity=".6" stroke-width="3"/>${[175,215,255,295,335].map(y=>`<circle cx="215" cy="${y}" r="3" fill="#fff"/>`).join('')}`;
  } else {
    shape = `<path d="M150 85 L190 65 L210 92 L230 65 L270 85 L310 150 L275 175 L255 145 L255 370 L165 370 L165 145 L145 175 L110 150 Z" fill="${color}" stroke="#755d5c" stroke-width="3"/><path d="M190 65 L210 92 L230 65 L220 125 L200 125 Z" fill="#f4eee8" stroke="#755d5c" stroke-width="2"/><path d="M210 125 L185 200 L210 220 L235 200 Z" fill="#fff" fill-opacity=".18" stroke="#755d5c" stroke-width="2"/><path d="M210 220 V370" stroke="#fff" stroke-opacity=".5" stroke-width="2"/>`;
  }
  svg.innerHTML = `<ellipse cx="210" cy="420" rx="115" ry="13" fill="#d9ccc3" opacity=".55"/><circle cx="210" cy="38" r="23" fill="#e6c8b8"/><path d="M185 36 Q190 4 218 12 Q240 17 235 42 Q220 25 185 36" fill="#66504b"/>${shape}`;
  const names = {dress:'Vestido',shirt:'Camisa',suit:'Terno'};
  const colors = {'#c98e9b':'rosa antigo','#d8c7ae':'bege','#526b68':'verde sálvia','#35445c':'azul-marinho','#292929':'preto','#eee5dc':'off-white'};
  const styles = {classic:'clássico',modern:'moderno',formal:'social'};
  $('#previewCaption').textContent = `${names[type]} ${styles[style]} • ${colors[color]}`;
  $('#simWhats').href = `https://wa.me/5541988422231?text=${encodeURIComponent('Olá, Paty! Gostaria de conversar sobre este modelo: '+names[type]+' '+styles[style]+', cor '+colors[color]+'.')}`;
}
const tryonForm = $('#tryonForm');
const tryonPhoto = $('#tryonPhoto');
const tryonConsent = $('#tryonConsent');
const tryonSubmit = $('#tryonSubmit');
const tryonFeedback = $('#tryonFeedback');
const tryonPhotoPreview = $('#tryonPhotoPreview');
const tryonPhotoPreviewWrap = $('#tryonPhotoPreviewWrap');
const tryonResult = $('#tryonResult');
const tryonResultImage = $('#tryonResultImage');
let localPhotoUrl = '';
let generatingTryon = false;

function updateTryonButton() {
  tryonSubmit.disabled = generatingTryon || !tryonPhoto.files.length || !tryonConsent.checked;
}
function clearTryonResult() {
  tryonResult.hidden = true;
  tryonResultImage.removeAttribute('src');
}
function forgetPhoto() {
  tryonPhoto.value = '';
  if (localPhotoUrl) URL.revokeObjectURL(localPhotoUrl);
  localPhotoUrl = '';
  tryonPhotoPreview.removeAttribute('src');
  tryonPhotoPreviewWrap.hidden = true;
  clearTryonResult();
  tryonFeedback.textContent = 'Escolha uma foto e dê seu consentimento para gerar a prévia.';
  tryonFeedback.className = 'tryon-feedback';
  updateTryonButton();
}
tryonPhoto.addEventListener('change', () => {
  const file = tryonPhoto.files[0];
  clearTryonResult();
  if (!file) {
    updateTryonButton();
    return;
  }
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if ((file.type && !allowedTypes.includes(file.type)) || file.size > 5 * 1024 * 1024) {
    forgetPhoto();
    tryonFeedback.textContent = 'Escolha uma foto JPG, PNG ou WEBP de até 5 MB.';
    tryonFeedback.className = 'tryon-feedback error';
    return;
  }
  if (localPhotoUrl) URL.revokeObjectURL(localPhotoUrl);
  localPhotoUrl = URL.createObjectURL(file);
  tryonPhotoPreview.src = localPhotoUrl;
  tryonPhotoPreviewWrap.hidden = false;
  tryonFeedback.textContent = 'A foto fica neste aparelho até você solicitar a geração.';
  tryonFeedback.className = 'tryon-feedback';
  updateTryonButton();
});
tryonConsent.addEventListener('change', updateTryonButton);
$('#tryonPhotoRemove').addEventListener('click', forgetPhoto);
function invalidateTryonResult() {
  if (!tryonResult.hidden) {
    clearTryonResult();
    tryonFeedback.textContent = 'As opções foram alteradas. Gere outra prévia para ver o novo modelo.';
    tryonFeedback.className = 'tryon-feedback';
  }
}
tryonForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (tryonSubmit.disabled) return;

  const data = new FormData(tryonForm);
  data.append('peca', garment.value);
  data.append('modelo', styleChoice.value);
  data.append('cor', colorChoice.value);
  data.append('length', lengthChoice.value);
  generatingTryon = true;
  tryonSubmit.textContent = 'Gerando sua prévia...';
  tryonFeedback.textContent = 'A IA está criando a imagem. Isso pode levar até dois minutos.';
  tryonFeedback.className = 'tryon-feedback';
  clearTryonResult();
  [garment,styleChoice,colorChoice,lengthChoice].forEach(el => { el.disabled = true; });
  tryonForm.querySelectorAll('input, button').forEach(el => { el.disabled = true; });
  updateTryonButton();
  try {
    const response = await fetch('api/prova_virtual.php', {method:'POST', body:data, credentials:'same-origin'});
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.mensagem || 'Não foi possível gerar a prévia.');
    tryonResultImage.src = `data:${result.mime};base64,${result.imagem}`;
    tryonResult.hidden = false;
    tryonFeedback.textContent = 'Prévia pronta. Compare com o avatar; confirme tecido e caimento com a Paty.';
    tryonFeedback.className = 'tryon-feedback success';
  } catch (error) {
    tryonFeedback.textContent = error.message || 'Não foi possível gerar a prévia. Tente novamente.';
    tryonFeedback.className = 'tryon-feedback error';
  } finally {
    generatingTryon = false;
    [garment,styleChoice,colorChoice,lengthChoice].forEach(el => { el.disabled = false; });
    tryonForm.querySelectorAll('input, button').forEach(el => { el.disabled = false; });
    tryonSubmit.textContent = 'Gerar prévia com IA';
    updateTryonButton();
  }
});
[garment,styleChoice,colorChoice,lengthChoice].forEach(el => {
  el.addEventListener('change', renderGarment);
  el.addEventListener('change', invalidateTryonResult);
});
renderGarment();

// Chatbot com respostas do servidor e atalhos de perguntas.
const widget = $('#chatWidget');
$('#chatLaunch').addEventListener('click', () => { widget.hidden = false; $('#chatInput').focus(); });
$('#chatClose').addEventListener('click', () => widget.hidden = true);
function addMessage(text, who) {
  const div = document.createElement('div'); div.className = who === 'user' ? 'user-msg' : 'bot-msg'; div.textContent = text;
  $('#chatMessages').appendChild(div); $('#chatMessages').scrollTop = $('#chatMessages').scrollHeight;
}
async function askBot(message) {
  addMessage(message, 'user');
  const body = new FormData(); body.append('mensagem', message);
  try { const r = await fetch('api/chat.php', {method:'POST', body}); const d = await r.json(); addMessage(d.resposta || 'Não consegui entender. Tente novamente.', 'bot'); }
  catch { addMessage('No momento não consegui responder. Você pode falar com a Paty pelo WhatsApp (41) 98842-2231.', 'bot'); }
}
$('#chatForm').addEventListener('submit', e => { e.preventDefault(); const input=$('#chatInput'); const msg=input.value.trim(); if(msg){ input.value=''; askBot(msg); }});
document.querySelectorAll('.quick-questions button').forEach(b => b.addEventListener('click', () => askBot(b.dataset.question)));

<?php
require_once __DIR__.'/ai/config.php';
if (aiReady(aiConfig())):
?>
<link rel="stylesheet" href="/assets/chat.css">
<script src="/assets/chat.js" defer></script>
<div class="ai-chat" dir="rtl" data-ai-chat data-csrf="<?= e($_SESSION['csrf']) ?>">
<button class="ai-launch" type="button" aria-controls="ai-panel" aria-expanded="false">✦ עוזר קניות</button>
<section id="ai-panel" class="ai-panel" aria-label="עוזר הקניות של iStore" hidden>
<div class="ai-header"><div><strong>iStore AI ✦</strong><span>מוצאים יחד את השדרוג הבא</span></div><button type="button" data-ai-close aria-label="סגירת הצ׳אט">×</button></div>
<div class="ai-messages" role="log" aria-live="polite" aria-relevant="additions" tabindex="0">
<div class="ai-bubble ai-assistant">שלום! אפשר להתייעץ על מוצר, להשוות דגמים או לשאול על RAM, מעבדים ו־Docker. במה אפשר לעזור?</div>
</div>
<p class="ai-privacy">הודעות השיחה נשלחות ל־Google Gemini. אין להזין מידע אישי.</p>
<form class="ai-form"><label class="sr-only" for="ai-message">הודעה לעוזר</label><textarea id="ai-message" rows="2" maxlength="1000" required placeholder="למשל: מחשב ללימודים עד 3,500 ₪"></textarea><button type="submit">שליחה ←</button></form>
<div class="ai-bottom"><button type="button" data-ai-reset>שיחה חדשה</button><a href="/?page=catalog">חיפוש בקטלוג</a></div>
</section></div>
<?php endif; ?>

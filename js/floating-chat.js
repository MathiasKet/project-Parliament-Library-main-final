// Floating Chat Button Logic
const mainChatBtn = document.getElementById('mainChatBtn');
const chatOptions = document.getElementById('chatOptions');
const whatsappBtn = document.getElementById('whatsappBtn');
const onPageChatBtn = document.getElementById('onPageChatBtn');
const onPageChatBox = document.getElementById('onPageChatBox');
const closeOnPageChat = document.getElementById('closeOnPageChat');
const chatFormBox = document.getElementById('chatFormBox');
const chatInputBox = document.getElementById('chatInputBox');
const chatMessagesBox = document.getElementById('chatMessagesBox');
const chatFallback = document.getElementById('chatFallback');
const emailFallback = document.getElementById('emailFallback');

let fallbackTimeout;

// Toggle chat options
mainChatBtn.addEventListener('click', (e) => {
  chatOptions.style.display = chatOptions.style.display === 'flex' ? 'none' : 'flex';
});

document.addEventListener('click', (e) => {
  if (!document.getElementById('floating-chat-btn').contains(e.target) && chatOptions.style.display === 'flex') {
    chatOptions.style.display = 'none';
  }
});

// WhatsApp button (opens chat to +233 53 103 2971)
whatsappBtn.addEventListener('click', () => {
  // wa.me links must be digits only: country code + number, no plus sign or spaces
  window.open('https://wa.me/233531032971', '_blank');
  chatOptions.style.display = 'none';
});

// On-page chat button
onPageChatBtn.addEventListener('click', () => {
  onPageChatBox.style.display = 'flex';
  chatOptions.style.display = 'none';
  chatMessagesBox.innerHTML = '<div style="color:#888;font-size:0.95rem;margin-bottom:8px;">Welcome! How can we help you?</div>';
  chatFallback.style.display = 'none';
  clearTimeout(fallbackTimeout);
});

// Close on-page chat
closeOnPageChat.addEventListener('click', () => {
  onPageChatBox.style.display = 'none';
  chatInputBox.value = '';
  chatMessagesBox.innerHTML = '';
  chatFallback.style.display = 'none';
  clearTimeout(fallbackTimeout);
});

// On-page chat form submit
chatFormBox.addEventListener('submit', (e) => {
  e.preventDefault();
  const msg = chatInputBox.value.trim();
  if (!msg) return;
  chatMessagesBox.innerHTML += `<div style='margin-bottom:6px;text-align:right;'><span style='background:#e0ffe0;color:#00703C;padding:6px 12px;border-radius:12px 12px 0 12px;display:inline-block;'>${msg}</span></div>`;
  chatInputBox.value = '';
  chatFallback.style.display = 'none';
  clearTimeout(fallbackTimeout);
  // Simulate no reply after 1 minute
  fallbackTimeout = setTimeout(() => {
    chatFallback.style.display = 'block';
  }, 60000); // 1 minute
});

// Email fallback (placeholder link)
emailFallback.addEventListener('click', (e) => {
  e.preventDefault();
  window.location.href = 'mailto:someone@example.com?subject=Library%20Chat%20Support'; // Replace with your email
}); 
<?php
// c:\laragon\www\TopUpin\components\chat_widget.php
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<!-- Floating Chat Button -->
<button id="chat-widget-btn" class="fixed bottom-6 right-6 z-50 bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-full shadow-2xl transition duration-300 transform hover:scale-110 flex items-center justify-center">
    <i class="fa-solid fa-comments text-xl"></i>
    <!-- Notifikasi titik merah jika ada pesan baru dari admin -->
    <span id="chat-badge" class="absolute top-0 right-0 bg-red-500 w-3.5 h-3.5 rounded-full border border-gray-900 hidden"></span>
</button>

<!-- Chat Widget Box -->
<div id="chat-widget-box" class="fixed bottom-24 right-6 z-50 w-[350px] h-[450px] rounded-3xl overflow-hidden glass border border-gray-800 flex flex-col shadow-2xl transition duration-300 opacity-0 pointer-events-none transform translate-y-4">
    <!-- Header -->
    <div class="bg-gray-900/90 border-b border-gray-800 p-4 flex justify-between items-center">
        <div class="flex items-center space-x-2.5">
            <div class="bg-indigo-500/10 text-indigo-400 p-2 rounded-xl">
                <i class="fa-solid fa-headset text-sm"></i>
            </div>
            <div>
                <h4 class="font-bold text-xs text-white">CS TopUpin</h4>
                <span class="text-[9px] text-emerald-400 flex items-center"><span class="w-1.5 h-1.5 bg-emerald-400 rounded-full mr-1"></span> Online</span>
            </div>
        </div>
        <button id="chat-close-btn" class="text-gray-400 hover:text-white transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- Body / Messages -->
    <div id="chat-messages" class="flex-grow p-4 overflow-y-auto space-y-3.5 text-xs">
        <?php if ($is_logged_in) { ?>
            <div class="bg-indigo-500/5 text-gray-400 border border-indigo-500/10 p-3 rounded-2xl text-center leading-relaxed">
                👋 Halo! Ada yang bisa kami bantu hari ini? Tulis keluhan Anda di bawah.
            </div>
        <?php } else { ?>
            <div class="h-full flex flex-col items-center justify-center text-center px-4 space-y-4">
                <i class="fa-solid fa-user-lock text-3xl text-gray-700"></i>
                <div>
                    <h5 class="font-bold text-sm text-white">Butuh Bantuan CS?</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Anda harus login terlebih dahulu untuk memulai percakapan chat dengan Customer Service.</p>
                </div>
                <div class="flex space-x-2 w-full">
                    <a href="login.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-[10px] transition text-center">Login</a>
                    <a href="register.php" class="flex-1 bg-gray-800 hover:bg-gray-750 text-gray-300 font-semibold py-2 rounded-xl text-[10px] transition text-center">Register</a>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Footer / Input Form -->
    <?php if ($is_logged_in) { ?>
        <form id="chat-form" class="p-3 border-t border-gray-800 bg-gray-950/50 flex items-center space-x-2">
            <input type="text" id="chat-input" placeholder="Ketik pesan..." required autocomplete="off"
                class="flex-grow bg-gray-900 border border-gray-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500 transition">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white p-2.5 rounded-xl transition flex items-center justify-center">
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </form>
    <?php } ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const widgetBtn = document.getElementById('chat-widget-btn');
    const widgetBox = document.getElementById('chat-widget-box');
    const closeBtn = document.getElementById('chat-close-btn');
    
    // Toggle Tampilan Kotak Chat
    widgetBtn.addEventListener('click', function() {
        if (widgetBox.classList.contains('opacity-0')) {
            widgetBox.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
            widgetBox.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
            document.getElementById('chat-badge')?.classList.add('hidden');
            scrollToBottom();
        } else {
            closeWidget();
        }
    });

    closeBtn.addEventListener('click', closeWidget);

    function closeWidget() {
        widgetBox.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
        widgetBox.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
    }

    <?php if ($is_logged_in) { ?>
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    let lastMessageCount = 0;
    
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Ambil riwayat percakapan (Polling)
    function fetchMessages() {
        const currentPath = window.location.pathname;
        const apiPath = currentPath.includes('/seller/') ? '../api/chat_handler.php' : 'api/chat_handler.php';

        fetch(apiPath + '?action=get')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.messages) {
                    let html = '';
                    
                    if (data.messages.length === 0) {
                        html = `<div class="bg-indigo-500/5 text-gray-400 border border-indigo-500/10 p-3 rounded-2xl text-center leading-relaxed">
                            👋 Halo! Ada yang bisa kami bantu hari ini? Tulis keluhan Anda di bawah.
                        </div>`;
                        chatMessages.innerHTML = html;
                        return;
                    }

                    data.messages.forEach(msg => {
                        const isUser = msg.role === 'user';
                        const bubbleClass = isUser 
                            ? 'bg-indigo-600 text-white rounded-br-none ml-auto' 
                            : 'bg-gray-850 text-gray-200 rounded-bl-none';
                        const containerClass = isUser ? 'flex justify-end' : 'flex justify-start';
                        
                        html += `
                            <div class="${containerClass} mb-2.5">
                                <div class="max-w-[80%] ${bubbleClass} p-3 rounded-2xl shadow-md">
                                    <p class="leading-relaxed break-words">${msg.text}</p>
                                    <span class="block text-[8px] text-gray-400 mt-1 text-right">${msg.time}</span>
                                </div>
                            </div>
                        `;
                    });
                    
                    const isNewMessage = data.messages.length > lastMessageCount;
                    chatMessages.innerHTML = html;
                    
                    if (isNewMessage) {
                        if (widgetBox.classList.contains('opacity-0') && lastMessageCount > 0) {
                            document.getElementById('chat-badge')?.classList.remove('hidden');
                        }
                        scrollToBottom();
                        lastMessageCount = data.messages.length;
                    }
                }
            })
            .catch(err => console.error('Error fetching chat messages:', err));
    }

    // Mengirim pesan baru
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msgText = chatInput.value.trim();
        if (!msgText) return;

        chatInput.value = '';
        const currentPath = window.location.pathname;
        const apiPath = currentPath.includes('/seller/') ? '../api/chat_handler.php' : 'api/chat_handler.php';

        fetch(apiPath + '?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msgText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchMessages();
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error('Error sending message:', err));
    });

    fetchMessages();
    setInterval(fetchMessages, 3000); // Polling setiap 3 detik
    <?php } ?>
});
</script>

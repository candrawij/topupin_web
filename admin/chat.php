<?php
// c:\laragon\www\TopUpin\admin\chat.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php"; 

/** @var mysqli $conn */

// Hitung data ringkasan untuk sidebar badge
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='Pending'"));
$pending_withdrawal = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM penarikan_dana WHERE status='Pending'"));

// Hitung unread chat untuk badge CS Chat di sidebar
$unread_chat_count = mysqli_num_rows(mysqli_query($conn, "
    SELECT DISTINCT s.id_session 
    FROM cs_chat_sessions s
    JOIN cs_chat_messages m ON s.id_session = m.id_session
    WHERE s.status = 'active' AND m.sender_role = 'user' AND m.is_read = 0
"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS Chat Support - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-gray-800 border-b md:border-b-0 md:border-r border-gray-700 p-6 space-y-6 flex flex-col justify-between">
        <div class="space-y-6">
            <div class="text-xl font-extrabold tracking-wider text-indigo-400 flex items-center">
                <i class="fa-solid fa-user-shield mr-2"></i> ADMIN<span class="text-white">PANEL</span>
            </div>
            <nav class="space-y-2">
                <a href="dashboard.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                <a href="produk.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-box w-5 mr-2"></i> Kelola Produk
                </a>
                <a href="transaksi.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-receipt w-5 mr-2"></i> Semua Transaksi
                    <?php if ($pending_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="penarikan.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-money-bill-transfer w-5 mr-2"></i> Tarik Saldo
                    <?php if ($pending_withdrawal > 0) { ?>
                        <span class="absolute right-3 bg-yellow-500 text-gray-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_withdrawal ?>
                        </span>
                    <?php } ?>
                </a>
                <!-- Menu Baru: CS Chat -->
                <a href="chat.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center relative">
                    <i class="fa-solid fa-headset w-5 mr-2"></i> CS Chat Support
                    <?php if ($unread_chat_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $unread_chat_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="../index.php" target="_blank" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-globe w-5 mr-2"></i> Lihat Website
                </a>
            </nav>
        </div>
        <div>
            <a href="logout.php" onclick="return confirm('Keluar dari panel admin?')" class="block text-red-400 hover:bg-red-500/10 px-4 py-2.5 rounded-xl text-sm font-semibold transition border-t border-gray-700/50 pt-4 flex items-center">
                <i class="fa-solid fa-right-from-bracket w-5 mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-6 md:p-10 flex flex-col space-y-6">
        <!-- Header -->
        <header class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">CS Chat Support</h1>
                <p class="text-gray-400 text-xs mt-1">Interaksi langsung dengan pembeli dan seller yang memerlukan bantuan.</p>
            </div>
            <div class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-xs text-indigo-400 font-semibold">
                <i class="fa-regular fa-clock mr-1.5"></i> <?= date('d M Y') ?>
            </div>
        </header>

        <!-- Chat Workspace Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-grow min-h-[500px]">
            
            <!-- Kolom Sesi Chat (Kiri) -->
            <div class="bg-gray-800 border border-gray-700 rounded-3xl p-5 flex flex-col h-[550px]">
                <h3 class="font-bold text-sm text-white border-b border-gray-700 pb-3 flex items-center justify-between">
                    <span>💬 Antrean Chat Aktif</span>
                    <span id="session-count-badge" class="bg-indigo-500/15 text-indigo-400 text-xs font-bold px-2.5 py-0.5 rounded-full">0 Sesi</span>
                </h3>
                <!-- List Sesi -->
                <div id="session-list" class="flex-grow overflow-y-auto mt-4 space-y-2.5 pr-1">
                    <!-- Sesi-sesi chat akan ter-render di sini via JS -->
                    <div class="text-center py-10 text-gray-500 text-xs">
                        <i class="fa-solid fa-spinner animate-spin text-lg mb-2 block"></i> Memuat sesi...
                    </div>
                </div>
            </div>

            <!-- Kolom Ruang Chat (Kanan) -->
            <div class="lg:col-span-2 bg-gray-800 border border-gray-700 rounded-3xl flex flex-col h-[550px] overflow-hidden">
                <!-- Tampilan Jika Belum Ada Chat Terpilih -->
                <div id="no-chat-selected" class="flex-grow flex flex-col items-center justify-center text-center p-6 text-gray-500 space-y-4">
                    <i class="fa-solid fa-headset text-5xl text-gray-750"></i>
                    <div>
                        <h4 class="font-bold text-sm text-white">Tidak Ada Sesi Terpilih</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm">Pilih salah satu sesi di antrean sebelah kiri untuk memulai percakapan bantuan.</p>
                    </div>
                </div>

                <!-- Tampilan Percakapan Aktif (Akan Dihilangkan/Ditampilkan lewat JS) -->
                <div id="chat-active-workspace" class="flex-grow flex flex-col h-full hidden">
                    <!-- Chat Header -->
                    <div class="bg-gray-850/50 border-b border-gray-700 p-4 flex justify-between items-center">
                        <div>
                            <h4 id="active-user-name" class="font-bold text-xs text-white">Nama User</h4>
                            <p id="active-user-email" class="text-[10px] text-gray-500 mt-0.5">user@email.com</p>
                        </div>
                        <button id="close-session-btn" class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20 text-xs font-bold px-4 py-2 rounded-xl transition flex items-center">
                            <i class="fa-solid fa-circle-xmark mr-1.5"></i> Tutup Sesi
                        </button>
                    </div>

                    <!-- Chat Bubble Messages Container -->
                    <div id="chat-messages-container" class="flex-grow p-5 overflow-y-auto space-y-3.5 text-xs bg-gray-900/10">
                        <!-- Pesan-pesan akan ter-render di sini via JS -->
                    </div>

                    <!-- Chat Input Form -->
                    <form id="admin-chat-form" class="p-4 border-t border-gray-700 bg-gray-850/30 flex items-center space-x-3">
                        <input type="text" id="admin-chat-input" placeholder="Tulis balasan Anda..." required autocomplete="off"
                            class="flex-grow bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-indigo-500 transition">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl transition flex items-center justify-center font-semibold text-xs shadow-lg shadow-indigo-600/20">
                            Kirim <i class="fa-solid fa-paper-plane ml-1.5"></i>
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionList = document.getElementById('session-list');
        const sessionCountBadge = document.getElementById('session-count-badge');
        const noChatSelected = document.getElementById('no-chat-selected');
        const chatWorkspace = document.getElementById('chat-active-workspace');
        
        const activeUserName = document.getElementById('active-user-name');
        const activeUserEmail = document.getElementById('active-user-email');
        const messagesContainer = document.getElementById('chat-messages-container');
        const chatForm = document.getElementById('admin-chat-form');
        const chatInput = document.getElementById('admin-chat-input');
        const closeSessionBtn = document.getElementById('close-session-btn');

        let selectedSessionId = null;
        let lastMessageCount = 0;

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // 1. Fetch Sesi-Sesi Aktif
        function loadSessions() {
            fetch('api/chat_handler.php?action=get_sessions')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.sessions) {
                        sessionCountBadge.textContent = `${data.sessions.length} Sesi`;

                        if (data.sessions.length === 0) {
                            sessionList.innerHTML = `
                                <div class="text-center py-10 text-gray-500 text-xs">
                                    <i class="fa-regular fa-folder-open text-2xl mb-2 block"></i> Tidak ada antrean chat aktif.
                                </div>
                            `;
                            return;
                        }

                        let html = '';
                        data.sessions.forEach(sess => {
                            const isSelected = sess.id_session === selectedSessionId;
                            const activeClass = isSelected 
                                ? 'bg-indigo-600 border-indigo-500 text-white' 
                                : 'bg-gray-900/40 border-gray-700/60 hover:bg-gray-700/50 text-gray-300';
                            
                            const unreadBadge = (sess.unread_count > 0 && !isSelected)
                                ? `<span class="bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full ml-2 animate-pulse">${sess.unread_count}</span>`
                                : '';

                            html += `
                                <div onclick="selectSession(${sess.id_session}, '${sess.nama}', '${sess.email}')" 
                                     class="p-3.5 border rounded-2xl cursor-pointer transition flex justify-between items-start ${activeClass}">
                                    <div class="space-y-1 flex-grow min-w-0 pr-2">
                                        <div class="flex items-center">
                                            <span class="font-bold text-xs truncate ${isSelected ? 'text-white' : 'text-gray-100'}">${sess.nama}</span>
                                            ${unreadBadge}
                                        </div>
                                        <p class="text-[10px] truncate ${isSelected ? 'text-indigo-200' : 'text-gray-400'}">${sess.last_message}</p>
                                    </div>
                                    <span class="text-[8px] text-right whitespace-nowrap ${isSelected ? 'text-indigo-200' : 'text-gray-500'}">${sess.updated_at}</span>
                                </div>
                            `;
                        });
                        sessionList.innerHTML = html;
                    }
                })
                .catch(err => console.error('Error loading sessions:', err));
        }

        // 2. Pilih salah satu sesi obrolan
        window.selectSession = function(id_session, nama, email) {
            selectedSessionId = id_session;
            lastMessageCount = 0;
            
            // Perbarui UI kerja
            noChatSelected.classList.add('hidden');
            chatWorkspace.classList.remove('hidden');
            activeUserName.textContent = nama;
            activeUserEmail.textContent = email;

            loadMessages();
            loadSessions(); // Update active/selected styles in the list immediately
        };

        // 3. Ambil Pesan dalam Sesi terpilih
        function loadMessages() {
            if (!selectedSessionId) return;

            fetch(`api/chat_handler.php?action=get_messages&id_session=${selectedSessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.messages) {
                        let html = '';
                        data.messages.forEach(msg => {
                            const isAdmin = msg.role === 'admin';
                            const bubbleClass = isAdmin 
                                ? 'bg-indigo-600 text-white rounded-br-none ml-auto' 
                                : 'bg-gray-700/60 text-gray-200 rounded-bl-none';
                            const containerClass = isAdmin ? 'flex justify-end' : 'flex justify-start';
                            
                            html += `
                                <div class="${containerClass} mb-2.5">
                                    <div class="max-w-[75%] ${bubbleClass} p-3 rounded-2xl shadow-md">
                                        <p class="leading-relaxed break-words">${msg.text}</p>
                                        <span class="block text-[8px] text-gray-400 mt-1 text-right">${msg.time}</span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        const isNewMessage = data.messages.length > lastMessageCount;
                        messagesContainer.innerHTML = html;
                        
                        if (isNewMessage) {
                            scrollToBottom();
                            lastMessageCount = data.messages.length;
                        }
                    }
                })
                .catch(err => console.error('Error loading messages:', err));
        }

        // 4. Kirim Balasan Admin
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const msgText = chatInput.value.trim();
            if (!selectedSessionId || !msgText) return;

            chatInput.value = '';

            fetch('api/chat_handler.php?action=send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_session: selectedSessionId, message: msgText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadMessages();
                    loadSessions();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error('Error sending reply:', err));
        });

        // 5. Tutup Sesi Percakapan
        closeSessionBtn.addEventListener('click', function() {
            if (!selectedSessionId) return;
            if (!confirm('Selesaikan bantuan dan tutup sesi chat ini?')) return;

            fetch('api/chat_handler.php?action=close', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_session: selectedSessionId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    selectedSessionId = null;
                    chatWorkspace.classList.add('hidden');
                    noChatSelected.classList.remove('hidden');
                    loadSessions();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error('Error closing session:', err));
        });

        // Start Intervals Polling
        loadSessions();
        setInterval(loadSessions, 3000);
        setInterval(loadMessages, 3000);
    });
    </script>
</body>
</html>

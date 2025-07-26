<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nama_user = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Nama User';
$inisial = strtoupper(substr($nama_user, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ark Sentient - Smart Assistant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../Asset/css/smartasis.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="menu-btn" id="burgerBtn"><i class="fas fa-bars"></i></button>
            <button class="search-btn"><i class="fas fa-search"></i></button>
        </div>
        <div class="sidebar-content">
            <div class="new-chat">
                <i class="fas fa-pen"></i>
                <span>New Chat</span>
            </div>
        </div>
        <div class="spacer"></div>
        <div class="logo-bottom">
            <i class="fas fa-cow"></i>
            <span><b>ARK Sentient</b></span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-success" href="home.php">
                <i class="fas fa-seedling me-2"></i>ARK Sentient
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Marketplace Ternak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="priksaternak.php">Pemeriksaan Ternak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="smartasis.php">Smart Assistant</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">History</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php
                            $user_display = isset($_SESSION['full_name']) && $_SESSION['full_name'] !== ''
                                ? htmlspecialchars($_SESSION['full_name'])
                                : (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User');
                            echo $user_display;
                            ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-list me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <div class="background-logo">
            <img src="../Asset/icon/logoweb.png" alt="Logo">
        </div>
        <div class="content-wrapper">
            <div class="hello-text" id="helloText">
                Hello, <?php echo htmlspecialchars($nama_user); ?>
            </div>
            <div class="chat-display" id="chatDisplay">
                </div>
        </div>
        <div class="input-area">
            <div class="input-box">
                <div class="input-row">
                    <input
                        type="text"
                        class="input-label"
                        id="messageInput"
                        placeholder="Minta smart asistant"
                    />
                    <button class="mic-btn">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <button class="enter-btn" id="sendButton">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                </div>
                <input type="file" id="fileUploadInput" class="hidden-file-input">
                <div class="input-upload" id="uploadFileButton">
                    <i class="fas fa-plus"></i> Upload File
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Referensi elemen DOM
        const sidebar = document.getElementById('sidebar');
        const burgerBtn = document.getElementById('burgerBtn');
        const uploadFileButton = document.getElementById('uploadFileButton');
        const fileUploadInput = document.getElementById('fileUploadInput');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const chatDisplay = document.getElementById('chatDisplay');
        const helloText = document.getElementById('helloText');

        // --- TAMBAHKAN LOG INI DI SINI ---
        console.log("Script dimuat.");
        console.log("messageInput:", messageInput);
        console.log("sendButton:", sendButton);
        console.log("chatDisplay:", chatDisplay);
        console.log("helloText:", helloText);
        // --- BATAS AKHIR LOG TAMBAHAN ---

        // Variabel untuk menyimpan riwayat chat
        // Ini akan digunakan untuk mengirim konteks percakapan ke AI
        let chatHistory = [];

        // Event listener untuk tombol burger sidebar
        burgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Event listener untuk tombol "Upload File"
        uploadFileButton.addEventListener('click', () => {
            fileUploadInput.click(); // Memicu klik pada input file tersembunyi
        });

        // Event listener ketika file dipilih
        fileUploadInput.addEventListener('change', () => {
            if (fileUploadInput.files.length > 0) {
                console.log('File selected:', fileUploadInput.files[0].name);
                // Di sini Anda bisa menambahkan logika untuk mengunggah file.
                // Untuk demo chat teks, kita fokus pada pesan teks.
            } else {
                console.log('No file selected.');
            }
        });

        /**
         * Menambahkan pesan ke tampilan chat.
         * @param {string} sender - 'user' atau 'ai'.
         * @param {string} text - Isi pesan.
         * @param {boolean} isLoading - True jika ini adalah indikator loading.
         * @returns {HTMLElement} Elemen div pesan yang baru dibuat.
         */
        function addMessageToChat(sender, text, isLoading = false) {
            // Sembunyikan 'Hello, PlyPlaPlo' setelah pesan pertama dikirim/diterima
            if (helloText.style.display !== 'none' && (sender === 'user' || sender === 'ai')) {
                helloText.style.display = 'none';
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}`; // Tambah kelas 'user' atau 'ai'
            
            if (isLoading) {
                messageDiv.classList.add('loading');
                messageDiv.textContent = 'Mengetik...'; // Tampilkan indikator loading
            } else {
                messageDiv.textContent = text;
                // Simpan pesan ke riwayat chat hanya jika itu bukan indikator loading
                // Format riwayat sesuai yang diharapkan oleh Gemini API (role dan teks)
                chatHistory.push({ sender: sender, text: text });
            }
            
            chatDisplay.appendChild(messageDiv);
            chatDisplay.scrollTop = chatDisplay.scrollHeight; // Gulir ke bawah otomatis
            
            return messageDiv; // Mengembalikan elemen untuk manipulasi lebih lanjut (misal: menghapus loading)
        }

        /**
         * Mengirim pesan pengguna ke API backend dan menampilkan balasan AI.
         */
        async function sendMessage() {
    // Log ini akan memberitahu kita jika fungsi sendMessage dipanggil
            console.log("Fungsi sendMessage() dipanggil."); 

            const message = messageInput.value.trim();
            if (message === '') return;

            addMessageToChat('user', message);
            messageInput.value = '';

            const loadingMessage = addMessageToChat('ai', '', true);
            sendButton.disabled = true;
            messageInput.disabled = true;

            try {
                // Log ini akan memberitahu kita jika fetch akan dijalankan
                console.log("Mencoba fetch ke ../api/chat.php"); 
                const response = await fetch('../api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message, history: chatHistory }) 
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("Fetch response NOT OK:", errorText); // Log error respons
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }

                const data = await response.json();
                console.log("Fetch successful, data:", data); // Log data sukses

                loadingMessage.remove();
                addMessageToChat('ai', data.reply);

            } catch (error) {
                console.error('Error in sendMessage catch block:', error); // Log error di catch
                loadingMessage.remove();
                addMessageToChat('ai', 'Maaf, terjadi kesalahan saat berkomunikasi dengan AI. Silakan coba lagi.');
            } finally {
                sendButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
                console.log("Fungsi sendMessage() selesai."); // Log selesai
            }
        }

        // Event listener untuk tombol kirim (panah atas)
        sendButton.addEventListener('click', sendMessage);

        // Event listener untuk menekan tombol Enter di input pesan
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Opsional: Anda bisa menambahkan logika di sini jika ingin menginisialisasi riwayat chat
        // dari database saat halaman dimuat (jika Anda memiliki fitur riwayat chat persisten)
    </script>
</body>
</html>
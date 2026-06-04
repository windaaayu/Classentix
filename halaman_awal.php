<?php
/**
 * Halaman_Awal.php
 * Router & Controller Utama
 */

require_once 'koneksi/koneksi.php';
$page = $_GET['p'] ?? '';
$error = ''; 

/* ============================================================
   BAGIAN 1: PROSES BACKEND (AJAX & POST FORM)
   ============================================================ */

// 1A. HANDLER: AJAX get_jadwal
if ($page === 'get_jadwal') {
    if (!isset($_GET['kelas_id']) || empty($_GET['kelas_id'])) {
        echo '<div style="text-align:center; padding:40px; color:#fff;"><p>Pilih kelas terlebih dahulu.</p></div>';
        exit;
    }

    $id_kelas = mysqli_real_escape_string($koneksi, $_GET['kelas_id']);
    $hari     = mysqli_real_escape_string($koneksi, $_GET['hari'] ?? 'Senin');

    // PERUBAHAN DB: Menyesuaikan dengan tabel jadwal_pelajaran dan relasi guru -> users
    $query = "SELECT jp.*, m.nama_mapel, u.nama_lengkap as nama_guru
              FROM jadwal_pelajaran jp
              LEFT JOIN mapel m ON jp.id_mapel = m.id
              LEFT JOIN guru g ON jp.id_guru = g.id
              LEFT JOIN users u ON g.id_user = u.id
              WHERE jp.id_kelas = '$id_kelas' AND jp.hari = '$hari'
              ORDER BY jp.jam_mulai ASC";

    $res = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($res) > 0) {
        echo '<div class="chalk-table-wrapper"><table class="chalk-table">
                <thead><tr><th>Waktu</th><th>Mata Pelajaran</th><th>Guru Pengajar</th></tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($res)) {
            $mulai   = date("H:i", strtotime($row['jam_mulai']));
            $selesai = !empty($row['jam_selesai']) ? date("H:i", strtotime($row['jam_selesai'])) : "Selesai";
            $waktu_tampil = "$mulai - $selesai";

            $mapel = !empty($row['nama_mapel']) ? $row['nama_mapel'] : "Umum";
            $guru  = !empty($row['nama_guru'])  ? $row['nama_guru']  : "Belum ditentukan";

            echo '<tr>
                    <td class="waktu-cell">' . htmlspecialchars($waktu_tampil) . ' WIB</td>
                    <td>' . htmlspecialchars($mapel) . '</td>
                    <td><i class="fa-solid fa-user-tie" style="margin-right:5px;"></i>' . htmlspecialchars($guru) . '</td>
                  </tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div style="text-align:center; padding:60px 20px; color:#f8fafc;">
                <div style="font-size:50px; margin-bottom:15px; opacity:0.7;"><i class="fa-solid fa-mug-hot"></i></div>
                <h3 style="font-family: \'Comic Sans MS\', cursive, sans-serif;">Kosong!</h3>
                <p style="font-size:15px;">Tidak ada jadwal mengajar pada hari <strong>' . htmlspecialchars($hari) . '</strong>.</p>
              </div>';
    }
    exit;
}

/* ============================================================
   BAGIAN 2: ROUTER HALAMAN
   ============================================================ */
switch ($page) {
    case 'guru_list':  renderGuruList(); break;
    case 'game':       renderGame(); break;
    case 'tentang':    renderTentang(); break;
    default:           renderLanding(); break;
}
exit;


/* ============================================================
   BAGIAN 3: TEMPLATE HTML (HEADER & FOOTER)
   ============================================================ */
function renderHeader($title, $bodyClass = '', $navClass = '', $activePage = '') {
    global $koneksi;
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/css/halaman_awal.css?v=<?php echo time(); ?>">
    </head>
    <body class="<?php echo $bodyClass; ?>">
    
    <?php 
    if ($bodyClass !== 'login-body' && $bodyClass !== 'register-body'): 
        $isLoggedIn = isset($_SESSION['nama']);
        $navRight = '';
        if ($isLoggedIn) {
            $dashboard_link = ($_SESSION['role'] === 'admin') ? 'Admin.php' : 'Guru.php';
            $user_ses       = $_SESSION['user'];
            $q_prof         = mysqli_query($koneksi, "SELECT foto, nama_lengkap FROM users WHERE username='$user_ses'");
            $d_prof         = mysqli_fetch_assoc($q_prof);

            $img_prof = (!empty($d_prof['foto']) && file_exists('uploads/' . $d_prof['foto']))
                ? 'uploads/' . $d_prof['foto']
                : 'https://ui-avatars.com/api/?name=' . urlencode($d_prof['nama_lengkap'] ?? 'User') . '&background=fff&color=4F46E5';

            // Navigasi Kanan (Avatar & Icon Logout Pintu Keluar)
            $navRight = '<a href="' . $dashboard_link . '" class="profile-nav-link">
                            <span class="nav-user-name">Hai, <strong>' . htmlspecialchars(explode(' ', $d_prof['nama_lengkap'] ?? 'User')[0]) . '</strong></span>
                            <img src="' . $img_prof . '" class="nav-avatar" alt="User">
                         </a>
                         <a href="logout.php" title="Keluar" class="btn-logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                         </a>';
        } else {
            // Tombol Login Standar
            $navRight = '<a href="login_register.php?action=login" class="btn-login">Portal Login</a>';
        }
    ?>
        <nav class="<?php echo $navClass; ?>">
            <a href="Halaman_Awal.php" class="logo">
                <img src="assets/images/logo_classentix.png" alt="Logo Classentix">
                Classentix
            </a>
            
            <div class="nav-links">
                <a href="<?php echo ($navClass ? 'Halaman_Awal.php' : ''); ?>#jadwal" class="<?php echo (empty($_GET['p'])) ? 'active' : ''; ?>">Jadwal</a>
                
                <a href="Halaman_Awal.php?p=guru_list" class="<?php echo ($activePage == 'guru_list') ? 'active' : ''; ?>">Daftar Guru</a>
                <a href="Halaman_Awal.php?p=game" class="<?php echo ($activePage == 'game') ? 'active' : ''; ?>">Mini Game</a>
                <a href="Halaman_Awal.php?p=tentang" class="<?php echo ($activePage == 'tentang') ? 'active' : ''; ?>">Tentang</a> </div>
            </div>
            
            <div class="nav-right"><?php echo $navRight; ?></div>
        </nav>
    <?php endif; 
}


function renderFooter($showFooter = true, $footerClass = '', $footerText = '&copy; 2026 <strong>Classentix Platform</strong>.') {
    global $koneksi; // Panggil koneksi database
    
    if ($showFooter): ?>
        <footer class="<?php echo $footerClass; ?>"><?php echo $footerText; ?></footer>
    <?php endif; 
    
    /* === MENGAMBIL SELURUH JADWAL UNTUK OTAK AI === */
    $q_dump = mysqli_query($koneksi, "
        SELECT k.nama_kelas, jp.hari, jp.jam_mulai, jp.jam_selesai, m.nama_mapel, u.nama_lengkap as nama_guru
        FROM jadwal_pelajaran jp
        LEFT JOIN kelas k ON jp.id_kelas = k.id
        LEFT JOIN mapel m ON jp.id_mapel = m.id
        LEFT JOIN guru g ON jp.id_guru = g.id
        LEFT JOIN users u ON g.id_user = u.id
    ");
    $data_jadwal = [];
    if($q_dump) {
        while($row = mysqli_fetch_assoc($q_dump)) {
            $data_jadwal[] = $row;
        }
    }
    $json_jadwal = json_encode($data_jadwal);
    ?>


        
        
        <script src="assets/js/halaman_awal.js"></script>
    </body>
    </html>
    <?php
}

/* ============================================================
   BAGIAN 4: KONTEN HALAMAN (VIEWS)
   ============================================================ */

function renderLanding() {
    global $koneksi;
    renderHeader('Classentix - Platform Jadwal & Edukasi', '', '', '');
    ?>
    <div class="container">
        <div class="hero">
            <h1>Platform Belajar <br><span style="color: var(--primary);">Lebih Terstruktur.</span></h1>
            <p>Kelola jadwal harian, pantau materi, dan temukan pengajar terbaikmu dalam satu platform yang simpel dan modern.</p>
        </div>

        <div id="jadwal" class="kelas-section">
    <h2 class="section-title">Pilih Kelas Aktif</h2>

    <div class="class-slider-wrapper">
        <button type="button" class="class-slider-btn class-slider-left" onclick="scrollKelas(-1)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="class-grid" id="classSlider">
            <?php
            $q_kelas = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");

            if (mysqli_num_rows($q_kelas) > 0) {
                while ($k = mysqli_fetch_assoc($q_kelas)) {
                    $id_kelas = htmlspecialchars($k['id']);
                    $nama_kelas = htmlspecialchars($k['nama_kelas']);

            echo "<div class='class-card' onclick=\"openBoard('{$id_kelas}', '{$nama_kelas}')\">
        <span class='class-status'>Aktif</span>
        <span class='icon'>🏫</span>
        <h3>{$nama_kelas}</h3>
      </div>";
                }
            } else {
                echo "<p class='kelas-empty'>Belum ada kelas yang terdaftar.</p>";
            }
            ?>
        </div>

        <button type="button" class="class-slider-btn class-slider-right" onclick="scrollKelas(1)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

        <div id="blackboardOverlay" class="blackboard-overlay">
            <div id="blackboardContent" class="blackboard-content">
                <button onclick="closeBoard()" class="btn-back-board" title="Kembali / Tutup">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <h2 id="boardTitle" class="chalk-text">Jadwal Kelas</h2>
                
                <div class="board-tabs" id="boardTabs">
                    <button class="day-tab" data-hari="Senin" onclick="loadDay('Senin')">Senin</button>
                    <button class="day-tab" data-hari="Selasa" onclick="loadDay('Selasa')">Selasa</button>
                    <button class="day-tab" data-hari="Rabu" onclick="loadDay('Rabu')">Rabu</button>
                    <button class="day-tab" data-hari="Kamis" onclick="loadDay('Kamis')">Kamis</button>
                    <button class="day-tab" data-hari="Jumat" onclick="loadDay('Jumat')">Jumat</button>
                    <button class="day-tab" data-hari="Sabtu" onclick="loadDay('Sabtu')">Sabtu</button>
                </div>

                <div id="boardBody" style="min-height: 200px;"></div>
            </div>
        </div>

        <div id="game" class="game-section">
            <h2 style="font-size:36px; font-weight:800; position:relative; z-index:2;">Butuh Istirahat Sejenak?</h2>
            <p style="font-size:17px; opacity:0.9; max-width:600px; margin:15px auto 0; position:relative; z-index:2;">
                Segarkan pikiranmu dengan mini game edukasi kami. Selesaikan tantangan dan raih skor tertinggimu!
            </p>
            <a href="Halaman_Awal.php?p=game" class="btn-play">MAIN SEKARANG</a>
        </div>
    </div>
    <?php
    renderFooter();
    
}

function renderGuruList() {
    // KODE GURU LIST (Tetap Sama Seperti Sebelumnya, saya singkat agar rapih)
    // ... [Anda bisa menempelkan fungsi renderGuruList() lama Anda di sini, tidak ada ubahan logika DB di bagian ini] ...
    global $koneksi;
    renderHeader('Daftar Guru - Classentix', 'game-body', 'game-nav', 'guru_list');
    ?>
    <div class="guru-list-container">
        <div class="guru-list-header">
            <h1>Profil Pengajar Kami</h1>
            <p>Mengenal lebih dekat bapak dan ibu guru Classentix yang telah terverifikasi.</p>
        </div>
        <div class="guru-grid">
            <?php
            $q_guru = mysqli_query($koneksi, "SELECT * FROM users WHERE (role='guru' AND status_validasi='aktif') ORDER BY nama_lengkap ASC");
            if ($q_guru && mysqli_num_rows($q_guru) > 0) {
                while ($g = mysqli_fetch_assoc($q_guru)) {
                    $display_name = !empty($g['nama_lengkap']) ? $g['nama_lengkap'] : $g['username'];
                    $foto_path    = !empty($g['foto']) ? 'uploads/' . $g['foto'] : '';
                    $foto_final   = (!empty($g['foto']) && file_exists($foto_path)) ? $foto_path : 'https://ui-avatars.com/api/?name=' . urlencode($display_name) . '&background=4F46E5&color=fff&size=200';
                    ?>
                    <div class="guru-card">
                        <img src="<?php echo $foto_final; ?>" class="guru-img" alt="Foto Profil">
                        <div class="role-badge"><?php echo strtoupper($g['role']); ?></div>
                        <h3><?php echo htmlspecialchars($display_name); ?></h3>
                        <p style="color:var(--text-muted); font-size:13px; font-weight:600;">@<?php echo htmlspecialchars($g['username']); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php
    renderFooter(true, 'game-footer', '&copy; 2026 Classentix Platform. Dibuat dengan ❤️ untuk kemudahan belajar.');
}

function renderGame() {
    // KODE GAME (Tetap Sama Seperti Sebelumnya)
    // ... [Anda bisa menempelkan fungsi renderGame() lama Anda di sini] ...
    renderHeader('Game vs Robot - Classentix', 'game-body', 'game-nav', 'game');
    ?>
    <div class="game-container">
        <h1>Tic Tac Toe</h1>
        <p class="status" id="statusText">Giliran Kamu (X)</p>
        <div class="board" id="board">
            <div class="cell" data-index="0" onclick="userMove(this, 0)"></div>
            <div class="cell" data-index="1" onclick="userMove(this, 1)"></div>
            <div class="cell" data-index="2" onclick="userMove(this, 2)"></div>
            <div class="cell" data-index="3" onclick="userMove(this, 3)"></div>
            <div class="cell" data-index="4" onclick="userMove(this, 4)"></div>
            <div class="cell" data-index="5" onclick="userMove(this, 5)"></div>
            <div class="cell" data-index="6" onclick="userMove(this, 6)"></div>
            <div class="cell" data-index="7" onclick="userMove(this, 7)"></div>
            <div class="cell" data-index="8" onclick="userMove(this, 8)"></div>
        </div>
        <button class="btn-reset" onclick="resetGame()">Ulang Pertandingan</button>
    </div>
    <?php
    renderFooter(true, 'game-footer');
}

function renderTentang() {
    renderHeader('Tentang Classentix - Platform Edukasi', 'game-body', 'game-nav', 'tentang');
    ?>
    <div class="about-container">
        <div class="about-header">
            <h1>Tentang Classentix</h1>
            <p>Mengenal lebih dekat ekosistem digital managemen kelas dan asisten belajar modern.</p>
        </div>

        <div class="about-main-card">
            <h2>Visi & Misi Platform</h2>
            <p>
                <strong>Classentix</strong> dirancang khusus untuk menjembatani kebutuhan informasi akademik antara guru dan murid secara real-time. Kami percaya bahwa transparansi jadwal yang terstruktur dan didukung teknologi asisten pintar dapat menciptakan lingkungan belajar-mengajar yang jauh lebih efisien, produktif, dan menyenangkan.
            </p>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <div class="about-icon">📅</div>
                <h3>Jadwal Real-Time</h3>
                <p>Akses jadwal pelajaran harian tiap kelas menggunakan simulasi visual papan tulis kapur yang interaktif dan responsif.</p>
            </div>

            <div class="about-card">
                <div class="about-icon">👩‍🏫</div>
                <h3>Profil Terverifikasi</h3>
                <p>Menampilkan jajaran data guru pengajar resmi yang kredibel dan divalidasi langsung oleh pihak administrasi sekolah.</p>
            </div>

            

            <div class="about-card">
                <div class="about-icon">🎮</div>
                <h3>Zona Refreshing</h3>
                <p>Menyediakan mini-game edukasi kognitif ringan untuk menyegarkan konsentrasi murid di sela-sela padatnya jam belajar.</p>
            </div>
        </div>
    </div>
    <?php
    renderFooter(true, 'game-footer');
}

?>
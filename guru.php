<?php
/**
 * Guru.php
 * Router untuk semua halaman Guru
 */

session_start();
require_once 'koneksi/koneksi.php';

// Proteksi: Hanya guru yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: Halaman_Awal.php?p=login");
    exit;
}

$page     = $_GET['p'] ?? '';
$username = $_SESSION['user'];

/* ============================================================
   HANDLER: Update Profil (POST)
   ============================================================ */
   if ($page === 'profil' && isset($_POST['update'])) {
    $q_user_foto = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    $user_foto   = mysqli_fetch_assoc($q_user_foto);

    $nama_lengkap_input = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $pass_baru          = $_POST['password'];
    $nama_file          = $user_foto['foto'];

    // Validasi Backend Password
    if (!empty($pass_baru)) {
        if (!preg_match('/[a-z]/', $pass_baru) || !preg_match('/[A-Z]/', $pass_baru) || !preg_match('/[0-9]/', $pass_baru)) {
            header("Location: guru.php?p=profil&msg=error_password");
            exit;
        }
    }

    // Hanya proses upload jika tidak ada error
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg'];
        $nama_asli  = $_FILES['foto']['name'];
        $x          = explode('.', $nama_asli);
        $ekstensi   = strtolower(end($x));
        $ukuran     = $_FILES['foto']['size'];
        $nama_file_baru = $username . '_' . time() . '.' . $ekstensi;

        if (in_array($ekstensi, $ekstensi_diperbolehkan) && $ukuran <= 2097152) {
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $nama_file_baru)) {
                if (!empty($user_foto['foto']) && file_exists('uploads/' . $user_foto['foto'])) {
                    unlink('uploads/' . $user_foto['foto']);
                }
                $nama_file = $nama_file_baru;
            }
        } else {
            header("Location: guru.php?p=profil&msg=error_upload");
            exit;
        }
    }

    // Eksekusi query UPDATE
    if (!empty($pass_baru)) {
        $password_hashed = password_hash($pass_baru, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama_lengkap='$nama_lengkap_input', password='$password_hashed', foto='$nama_file' WHERE username='$username'";
    } else {
        $sql = "UPDATE users SET nama_lengkap='$nama_lengkap_input', foto='$nama_file' WHERE username='$username'";
    }

    if (mysqli_query($koneksi, $sql)) {
        $_SESSION['nama'] = $nama_lengkap_input;
        header("Location: guru.php?p=profil&msg=success_profile");
        exit;
    } else {
        header("Location: guru.php?p=profil&msg=error_db");
        exit;
    }
}

/* ============================================================
   AMBIL DATA GURU (Untuk Semua Halaman)
   ============================================================ */
$query_identitas = mysqli_query($koneksi, "
    SELECT u.*, g.id as id_guru_fix, g.nip
    FROM users u
    LEFT JOIN guru g ON g.id_user = u.id
    WHERE u.username = '$username'
");
$user_data = mysqli_fetch_assoc($query_identitas);

$id_guru_user = $user_data['id_guru_fix'] ?? 0;
$nip_guru     = $user_data['nip']         ?? 'NIP Tidak Ditemukan';
$nama_user    = $user_data['nama_lengkap'] ?? $_SESSION['nama'];

$foto_tampil = (!empty($user_data['foto']) && file_exists('uploads/' . $user_data['foto']))
    ? 'uploads/' . $user_data['foto']
    : 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=4F46E5&color=fff&size=128';

/* ============================================================
   ROUTING
   ============================================================ */
switch ($page) {
    case 'murid':
        renderMuridGuru($user_data, $foto_tampil, $nama_user, $id_guru_user);
        break;
    case 'profil':
        renderProfilGuru($user_data, $foto_tampil, $nama_user, $success ?? '', $error ?? '');
        break;
    default:
        renderDashboardGuru($foto_tampil, $nama_user, $nip_guru, $id_guru_user);
        break;
}
exit;

/* ============================================================
   HELPER: Sidebar Guru
   ============================================================ */
function getSidebarGuru($activePage) {
    $dashActive   = ($activePage === 'dashboard') ? ' active' : '';
    $muridActive  = ($activePage === 'murid')     ? ' active' : '';
    $profilActive = ($activePage === 'profil')    ? ' active' : '';
    return '
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo_classentix.png" alt="Logo Classentix">
            Classentix
        </div>
        <div class="menu-section">
            <div class="menu-label">Main Menu</div>
            <a href="guru.php" class="menu-item' . $dashActive . '">
                <span><i class="fa-solid fa-calendar-days"></i></span> Jadwal Mengajar
            </a>
            <a href="guru.php?p=murid" class="menu-item' . $muridActive . '">
                <span><i class="fa-solid fa-graduation-cap"></i></span> Daftar Murid
            </a>
            <a href="guru.php?p=profil" class="menu-item' . $profilActive . '">
                <span><i class="fa-solid fa-user-gear"></i></span> Profil
            </a>
            <div class="menu-label" style="margin-top:20px;">Eksternal</div>
            <a href="Halaman_Awal.php" class="menu-item">
                <span><i class="fa-solid fa-house"></i></span> Landing Page
            </a>
        </div>
        <a href="logout.php" class="logout-btn">
            <span><i class="fa-solid fa-right-from-bracket"></i></span> Logout
        </a>
    </div>';
}

/* ============================================================
   RENDER: Dashboard Guru
   ============================================================ */
function renderDashboardGuru( $foto_tampil, $nama_user, $nip_guru, $id_guru_user) {
    global $koneksi;

    $query_jadwal = "SELECT jp.*, m.nama_mapel, k.nama_kelas
                     FROM jadwal_pelajaran jp
                     LEFT JOIN mapel m ON jp.id_mapel = m.id
                     LEFT JOIN kelas k ON jp.id_kelas = k.id
                     WHERE jp.id_guru = '$id_guru_user'
                     ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'),
                     jp.jam_mulai ASC";
    $res_jadwal = mysqli_query($koneksi, $query_jadwal);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Guru Dashboard - Classentix</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/css/guru.css">
    </head>
    <body>
        <?php echo getSidebarGuru('dashboard'); ?>
        <div class="content">
            <div class="profile-widget">
                <div class="user-info">
                    <img src="<?php echo $foto_tampil; ?>" alt="Profile">
                    <div>
                        <p>NIP: <?php echo htmlspecialchars($nip_guru); ?></p>
                        <h2>Halo, <?php echo htmlspecialchars(explode(' ', $nama_user)[0]); ?></h2>
                    </div>
                </div>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-users" style="color:var(--primary); margin-right:8px;"></i> Agenda Mengajar Anda</h3>
                    <select id="filterHariGuru" class="search-input select-filter">
                            <option value="all">Semua Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Waktu (WIB)</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($id_guru_user > 0 && mysqli_num_rows($res_jadwal) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($res_jadwal)): 
                                    $mulai   = date("H:i", strtotime($row['jam_mulai']));
                                    $selesai = !empty($row['jam_selesai']) ? date("H:i", strtotime($row['jam_selesai'])) : "Selesai";
                                ?>
                                <tr class="jadwal-guru-row" data-hari="<?php echo htmlspecialchars($row['hari']); ?>">
                                    <td class="day-badge"><?php echo htmlspecialchars($row['hari']); ?></td>
                                    <td class="time-text"><?php echo htmlspecialchars("$mulai - $selesai"); ?></td>
                                    <td><span class="subject-badge"><?php echo htmlspecialchars($row['nama_mapel'] ?? 'Umum'); ?></span></td>
                                    <td><span class="class-badge"><?php echo htmlspecialchars($row['nama_kelas'] ?? '-'); ?></span></td>
                                </tr>
                                <?php endwhile; ?>

                                <tr id="emptyRowGuru" style="display: none;">
                                    <td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);">
                                        <i class="fa-regular fa-calendar-xmark" style="font-size:35px; margin-bottom:10px; display:block; opacity:0.3;"></i>
                                        <p style="font-weight:600;">Tidak ada agenda mengajar untuk hari yang dipilih.</p>
                                    </td>
                                </tr>

                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:60px; color:var(--text-muted);">
                                        <i class="fa-regular fa-calendar-xmark" style="font-size:40px; margin-bottom:15px; display:block; opacity:0.3;"></i>
                                        <p style="font-weight:600;">Jadwal mengajar tidak ditemukan.</p>
                                        <p style="font-size:12px; margin-top:5px;">Belum ada jadwal yang dialokasikan untuk Anda oleh Admin.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script src="assets/js/guru.js"></script>
    </body>
    </html>
    <?php
}

/* ============================================================
   RENDER: Daftar Murid Guru (Hanya Melihat & Filter Kelas)
   ============================================================ */
function renderMuridGuru($user_data, $foto_tampil, $nama_user, $id_guru_user) {
    global $koneksi;

    // 1. Ambil list kelas yang hanya diajar oleh guru bersangkutan untuk menu dropdown filter
    $q_kelas_ajar = mysqli_query($koneksi, "
        SELECT DISTINCT k.id, k.nama_kelas 
        FROM jadwal_pelajaran jp
        INNER JOIN kelas k ON jp.id_kelas = k.id
        WHERE jp.id_guru = '$id_guru_user'
        ORDER BY k.nama_kelas ASC
    ");
    
    $kelas_options = "";
    $array_id_kelas = [];
    while($rk = mysqli_fetch_assoc($q_kelas_ajar)) {
        $array_id_kelas[] = $rk['id'];
        $kelas_options .= "<option value='{$rk['id']}'>{$rk['nama_kelas']}</option>";
    }

    // Ubah array ID kelas menjadi string untuk kondisi query siswa IN (1,2,3)
    $string_id_kelas = !empty($array_id_kelas) ? implode(',', $array_id_kelas) : '0';

    // 2. Query ambil data siswa yang berada di cakupan kelas yang diajar oleh guru tersebut
    $query_siswa = "
        SELECT s.*, k.nama_kelas 
        FROM siswa s
        INNER JOIN kelas k ON s.id_kelas = k.id
        WHERE s.id_kelas IN ($string_id_kelas)
        ORDER BY k.nama_kelas ASC, s.absen ASC
    ";
    $res_siswa = mysqli_query($koneksi, $query_siswa);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daftar Murid - Classentix</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/css/guru.css">
    </head>
    <body>
        <?php echo getSidebarGuru('murid'); ?>
        <div class="content">
            <div class="profile-widget">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:var(--text-main);">Daftar Murid</h1>
                    <p style="color:var(--text-muted); font-size:14px; margin-top:4px;">Melihat data murid aktif pada kelas yang Anda ajar.</p>
                </div>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-users" style="color:var(--primary); margin-right:8px;"></i> Data Murid</h3>
                    <select id="filterKelas" class="search-input select-filter">
                        <option value="all">-- Semua Kelas Anda --</option>
                        <?php echo $kelas_options; ?>
                    </select>
                </div>

                <div class="table-container">
                    <table id="tabelSiswa">
                        <thead>
                            <tr>
                                <th class="th-absen">Nomor Absen</th>
                                <th class="th-nama" style="text-align: left; padding-left: 20px;">Nama Lengkap</th>
                                <th class="th-kelas">Kelas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySiswa">
                            <?php if (mysqli_num_rows($res_siswa) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($res_siswa)): ?>
                                    <tr class="siswa-row" data-kelas="<?php echo $row['id_kelas']; ?>">
                                        <td class="text-center"><strong><?php echo $row['absen']; ?></strong></td>
                                        <td style="text-align: left; padding-left: 20px;" class="nama-siswa-bold"><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td class="text-center"><span class="badge-kelas-table"><?php echo htmlspecialchars($row['nama_kelas']); ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                            <tr id="emptyRow" style="display: none;">
                                <td colspan="3" style="text-align:center; padding:50px; color:var(--text-muted);">
                                    <i class="fa-regular fa-folder-open" style="font-size:36px; margin-bottom:10px; display:block; opacity:0.4;"></i>
                                    Tidak ada data murid pada spesifikasi ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="pagination-container"></div>
            </div>
        </div>
        <script src="assets/js/guru.js"></script>
    </body>
    </html>
    <?php
}

/* ============================================================
   RENDER: Profil Guru
   ============================================================ */
function renderProfilGuru($user_data, $foto_tampil, $nama_user, $success, $error) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pengaturan Akun - Classentix</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/css/guru.css?v=<?php echo time(); ?>">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <?php echo getSidebarGuru('profil'); ?>
        
        <div class="content">
            <div class="profil-widget-admin">
                <div class="profil-user-top">
                    <img src="<?php echo $foto_tampil; ?>" alt="Profile">
                    <div class="profil-header-info">
                        <p class="subtitle">Guru</p>
                        <h2><?php echo htmlspecialchars($nama_user); ?> ⚡</h2>
                        <p class="status"><i class="fa-solid fa-shield-check"></i> Akun Terverifikasi</p>
                    </div>
                </div>
                <a href="Guru.php" class="change-photo-btn" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
            </div>

            <div class="profil-card">
                <div class="profil-section-title">
                    <h3>Informasi Personal</h3>
                    <p>Perbarui data diri dan keamanan akun pengajar Anda secara berkala.</p>
                </div>

                <form id="formProfil" action="" method="POST" enctype="multipart/form-data">
                    
                    <div class="preview-wrapper">
                        <div class="foto-relative">
                            <img src="<?php echo $foto_tampil; ?>" id="preview">
                            <label for="inputFotoProfil" class="foto-badge">
                                <i class="fa-solid fa-camera" style="font-size: 12px;"></i>
                            </label>
                        </div>
                        <div>
                            <label for="inputFotoProfil" class="change-photo-btn">Ganti Foto Profil</label>
                            <p style="font-size:11px; color:var(--text-muted); margin-top:10px; line-height: 1.5;">
                                Disarankan foto formal ukuran kotak.<br>Format: JPG, JPEG, PNG. Maks 2MB.
                            </p>
                        </div>
                        <input type="file" name="foto" id="inputFotoProfil" hidden accept="image/*">
                    </div>

                    <div class="profil-form-grid">
                        <div class="profil-form-group">
                            <label><i class="fa-solid fa-address-card"></i> Nomor Induk Pegawai (NIP)</label>
                            <input type="text" value="<?php echo htmlspecialchars($user_data['nip'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="profil-form-group">
                            <label><i class="fa-solid fa-id-badge"></i> Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                        </div>

                        <div class="profil-form-group">
                            <label><i class="fa-solid fa-signature"></i> Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required placeholder="Masukkan nama lengkap">
                        </div>

                        <div class="profil-form-group">
                            <label><i class="fa-solid fa-lock"></i> Password Baru</label>
                            <input type="password" id="passwordProfil" name="password" placeholder="Sandi baru (Opsional)">
                        </div>
                    </div>

                    <div class="profil-form-footer">
                        <button type="submit" name="update" class="btn-save">
                            <i class="fa-solid fa-check-double"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <script src="assets/js/guru.js"></script>
    </body>
    </html>
    <?php
}
?>
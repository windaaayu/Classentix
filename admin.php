<?php

session_start();
require_once 'koneksi/koneksi.php';

// PROTEKSI & INISIALISASI
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login_register.php?action=login");
    exit;
}

$page      = $_GET['p'] ?? '';
$username  = $_SESSION['user'];
$nama_user = $_SESSION['nama'];

// Ambil data profil
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$user_data  = mysqli_fetch_assoc($query_user);
$foto = (!empty($user_data['foto']) && file_exists('uploads/' . $user_data['foto']))
    ? 'uploads/' . $user_data['foto']
    : 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=4F46E5&color=fff&size=128';

$query_pending   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='guru' AND status_validasi='pending'");
$data_pending    = mysqli_fetch_assoc($query_pending);
$jumlah_pending  = $data_pending['total'];

$error_siswa = '';


// HANDLER CRUD
$success_profil = '';
if ($page === 'profil' && isset($_POST['update_profil'])) {
    $nama_lengkap_input = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $pass_baru          = $_POST['password'];
    $nama_file          = $user_data['foto']; // Gunakan foto lama sebagai default
    $username_aktif = $user_data['username'];

    if (!empty($pass_baru)) {
        // Regex PHP: Harus ada huruf kecil (?=.*[a-z]), huruf besar (?=.*[A-Z]), dan angka (?=.*\d)
        if (!preg_match('/[a-z]/', $pass_baru) || !preg_match('/[A-Z]/', $pass_baru) || !preg_match('/[0-9]/', $pass_baru)) {
            header("Location: Admin.php?p=profil&msg=error_password");
            exit;
        }
    }
    // Cek jika ada upload foto baru DAN pastikan tidak ada error saat upload (UPLOAD_ERR_OK)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg'];
        $nama_asli  = $_FILES['foto']['name'];
        $x          = explode('.', $nama_asli);
        $ekstensi   = strtolower(end($x));
        $nama_file_baru = 'admin_' . $username_aktif . '_' . time() . '.' . $ekstensi;

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            // Pastikan folder tersedia
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            
            // Pindahkan file ke folder uploads
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $nama_file_baru)) {
                // Hapus foto lama jika ada dan pastikan file fisiknya benar-benar ada
                if (!empty($user_data['foto']) && file_exists('uploads/' . $user_data['foto'])) {
                    unlink('uploads/' . $user_data['foto']);
                }
                // Update variabel dengan nama file yang baru agar masuk ke database
                $nama_file = $nama_file_baru;
            }
        }
    }
    // Update query tergantung apakah password diisi atau tidak
    if (!empty($pass_baru)) {
        $password_hashed = password_hash($pass_baru, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama_lengkap='$nama_lengkap_input', password='$password_hashed', foto='$nama_file' WHERE username='$username_aktif'";
    } else {
        $sql = "UPDATE users SET nama_lengkap='$nama_lengkap_input', foto='$nama_file' WHERE username='$username_aktif'";
    }

    if (mysqli_query($koneksi, $sql)) {
        $_SESSION['nama'] = $nama_lengkap_input;
        // Refresh halaman agar foto dan nama di sidebar langsung berubah
        header("Location: Admin.php?p=profil&msg=success_profil");
        exit;
    }
}



// --- KELAS ---
if (isset($_POST['tambah_kelas'])) {
    $nama_k = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $cek = mysqli_query($koneksi, "SELECT * FROM kelas WHERE nama_kelas = '$nama_k'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=kelas&msg=duplicate"); exit;
    } else {
        mysqli_query($koneksi, "INSERT INTO kelas (nama_kelas) VALUES ('$nama_k')");
        header("Location: Admin.php?p=kelas&msg=added_kelas"); exit;
    }
}

if (isset($_POST['update_kelas'])) {
    $id = (int)$_POST['id_kelas'];
    $nama_k = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $cek = mysqli_query($koneksi, "SELECT * FROM kelas WHERE nama_kelas = '$nama_k' AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=kelas&msg=duplicate"); exit;
    } else {
        mysqli_query($koneksi, "UPDATE kelas SET nama_kelas='$nama_k' WHERE id='$id'");
        header("Location: Admin.php?p=kelas&msg=updated_kelas"); exit;
    }
}

if (isset($_GET['hapus_kelas'])) {
    $id = (int)$_GET['hapus_kelas'];
    mysqli_query($koneksi, "DELETE FROM kelas WHERE id='$id'");
    header("Location: Admin.php?p=kelas&msg=deleted_kelas"); exit;
}


// --- SISWA / MURID ---
if (isset($_POST['simpan'])) {
    $absen = (int)$_POST['absen'];
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_k  = (int)$_POST['id_kelas'];

    $cek = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas = '$id_k' AND (absen = '$absen' OR nama = '$nama')");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=siswa&msg=duplicate"); exit;
    } else {
        if (mysqli_query($koneksi, "INSERT INTO siswa (absen, nama, id_kelas) VALUES ('$absen','$nama','$id_k')")) {
            header("Location: Admin.php?p=siswa&msg=added_murid"); exit;
        } else { header("Location: Admin.php?p=siswa&msg=error_system"); exit; }
    }
}

if (isset($_POST['update'])) {
    $id    = (int)$_POST['id'];
    $absen = (int)$_POST['absen'];
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_k  = (int)$_POST['id_kelas'];

    $cek = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas = '$id_k' AND (absen = '$absen' OR nama = '$nama') AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=siswa&msg=duplicate"); exit;
    } else {
        if (mysqli_query($koneksi, "UPDATE siswa SET absen='$absen', nama='$nama', id_kelas='$id_k' WHERE id='$id'")) {
            header("Location: Admin.php?p=siswa&msg=updated_murid"); exit;
        } else { header("Location: Admin.php?p=siswa&msg=error_system"); exit; }
    }
}

if (isset($_GET['hapus_siswa'])) {
    $id = (int)$_GET['hapus_siswa'];
    mysqli_query($koneksi, "DELETE FROM siswa WHERE id='$id'");
    // Perbaikan: Menambahkan p=siswa agar halaman kembali ke menu siswa
    header("Location: Admin.php?p=siswa&msg=deleted_murid"); exit;
}


// ------- GURU ------- //
if (isset($_POST['simpan_validasi'])) {
    $id_user = (int)$_POST['id_user'];

    $stmt = mysqli_prepare($koneksi, "UPDATE users SET status_validasi='aktif' WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: Admin.php?p=guru&panel=validasi&msg=approved");
    } else {
        header("Location: Admin.php?p=guru&panel=validasi&msg=error_system");
    }
    exit;
}

// B. Tolak & Hapus Akun Pending
if (isset($_GET['hapus_user_pending'])) {
    $id_hapus = (int)$_GET['hapus_user_pending'];
    
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ? AND status_validasi = 'pending'");
    mysqli_stmt_bind_param($stmt, "i", $id_hapus);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: Admin.php?p=guru&panel=validasi&msg=rejected");
    } else {
        header("Location: Admin.php?p=guru&panel=validasi&msg=error_system");
    }
    exit;
}

// C. Hapus Permanen Data Guru Aktif
if (isset($_GET['hapus_guru'])) {
    $id_guru = (int)$_GET['hapus_guru'];
    
    // Cari id_user terkait
    $stmt_cari = mysqli_prepare($koneksi, "SELECT id_user FROM guru WHERE id = ?");
    mysqli_stmt_bind_param($stmt_cari, "i", $id_guru);
    mysqli_stmt_execute($stmt_cari);
    $result = mysqli_stmt_get_result($stmt_cari);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id_user = (int)$row['id_user'];
        
        // Gunakan Transaction agar jika salah satu gagal, semua dibatalkan (Rollback)
        mysqli_begin_transaction($koneksi);
        try {
            // Hapus data dependen (child) terlebih dahulu
            mysqli_query($koneksi, "DELETE FROM jadwal_pelajaran WHERE id_guru = $id_guru");
            mysqli_query($koneksi, "DELETE FROM guru_kelas WHERE id_guru = $id_guru");
            
            // Hapus data utama
            mysqli_query($koneksi, "DELETE FROM guru WHERE id = $id_guru");
            mysqli_query($koneksi, "DELETE FROM users WHERE id = $id_user");
            
            mysqli_commit($koneksi);
            header("Location: Admin.php?p=guru&msg=deleted_guru");
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            header("Location: Admin.php?p=guru&msg=error_system");
        }
    } else {
        header("Location: Admin.php?p=guru&msg=not_found");
    }
    exit;
}

// =========================================================================
// 2. SIMPAN UPDATE DETAIL GURU
// =========================================================================

if (isset($_POST['update_detail_guru'])) {
    $id_guru  = (int)$_POST['id_guru'];
    $id_user  = (int)$_POST['id_user'];
    
    $nama     = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $no_telp  = trim($_POST['no_telp']);
    $nip      = trim($_POST['nip']);
    $id_mapel = (int)$_POST['id_mapel'];

    // Validasi Backend (Mencegah Bypass dari Inspect Element)
    if (!preg_match('/^[0-9]{11,13}$/', $no_telp)) {
        header("Location: Admin.php?p=detail_guru&id=$id_guru&msg=error_phone"); exit;
    }
    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        header("Location: Admin.php?p=detail_guru&id=$id_guru&msg=error_nip"); exit;
    }

    mysqli_begin_transaction($koneksi);
    try {
        // A. Update Users
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt_user = mysqli_prepare($koneksi, "UPDATE users SET nama_lengkap=?, username=?, no_telp=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_user, "ssssi", $nama, $username, $no_telp, $password, $id_user);
        } else {
            $stmt_user = mysqli_prepare($koneksi, "UPDATE users SET nama_lengkap=?, username=?, no_telp=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_user, "sssi", $nama, $username, $no_telp, $id_user);
        }
        mysqli_stmt_execute($stmt_user);

        // B. Update Guru
        $stmt_guru = mysqli_prepare($koneksi, "UPDATE guru SET nip=?, id_mapel=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_guru, "sii", $nip, $id_mapel, $id_guru);
        mysqli_stmt_execute($stmt_guru);

        // C. Update Kelas (Hapus relasi lama, masukkan yang baru)
        mysqli_query($koneksi, "DELETE FROM guru_kelas WHERE id_guru=$id_guru");
        if (!empty($_POST['kelas'])) {
            $stmt_kelas = mysqli_prepare($koneksi, "INSERT INTO guru_kelas (id_guru, id_kelas) VALUES (?, ?)");
            foreach ($_POST['kelas'] as $id_kelas) {
                $id_k = (int)$id_kelas;
                mysqli_stmt_bind_param($stmt_kelas, "ii", $id_guru, $id_k);
                mysqli_stmt_execute($stmt_kelas);
            }
        }

        mysqli_commit($koneksi);
        header("Location: Admin.php?p=detail_guru&id=$id_guru&msg=updated");
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: Admin.php?p=detail_guru&id=$id_guru&msg=error_system");
    }
    exit;
}

// --- MAPEL ---
if (isset($_POST['tambah'])) {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    $cek = mysqli_query($koneksi, "SELECT * FROM mapel WHERE code_mapel = '$kode' OR nama_mapel = '$nama'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=mapel&msg=duplicate"); exit;
    } else {
        mysqli_query($koneksi, "INSERT INTO mapel (code_mapel, nama_mapel) VALUES ('$kode', '$nama')");
        header("Location: Admin.php?p=mapel&msg=added_mapel"); exit;
    }
}

if (isset($_POST['update_mapel'])) {
    $id = $_POST['id'];
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    $cek = mysqli_query($koneksi, "SELECT * FROM mapel WHERE (code_mapel = '$kode' OR nama_mapel = '$nama') AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: Admin.php?p=mapel&msg=duplicate"); exit;
    } else {
        mysqli_query($koneksi, "UPDATE mapel SET code_mapel='$kode', nama_mapel='$nama' WHERE id='$id'");
        header("Location: Admin.php?p=mapel&msg=updated_mapel"); exit;
    }
}

if (isset($_GET['hapus_mapel'])) {
    mysqli_query($koneksi, "DELETE FROM mapel WHERE id=".(int)$_GET['hapus_mapel']);
    header("Location: Admin.php?p=mapel&msg=deleted_mapel"); exit;
}



// --- LOGIKA JADWAL (SIMPAN & UPDATE) ---
if (isset($_POST['simpan_jadwal'])) {
    $id_edit = $_POST['id_edit']; 
    $hari = $_POST['hari'];
    $jam_m = $_POST['jam_mulai'];
    $jam_s = $_POST['jam_selesai'];
    $id_k = $_POST['id_kelas'];
    $id_g = $_POST['id_guru'];
    $id_m = $_POST['id_mapel']; 
    
    // --- CEK BENTROK (Backend Validation) ---
    // Logika bentrok: Hari sama, dan rentang jam saling tumpang tindih untuk Kelas ATAU Guru yang sama
    $query_cek = "SELECT id FROM jadwal_pelajaran 
                  WHERE hari = '$hari' 
                  AND (jam_mulai < '$jam_s' AND jam_selesai > '$jam_m') 
                  AND (id_kelas = '$id_k' OR id_guru = '$id_g')";
                  
    // Jika sedang edit, kecualikan ID jadwal ini sendiri agar tidak dianggap bentrok dengan dirinya sendiri
    if (!empty($id_edit)) {
        $query_cek .= " AND id != '$id_edit'";
    }

    $cek_bentrok = mysqli_query($koneksi, $query_cek);

    if (mysqli_num_rows($cek_bentrok) > 0) {
        // Jika ada bentrok, gagalkan simpan dan kirim pesan error
        header("Location: Admin.php?p=jadwal&msg=conflict"); exit;
    } else {
        // Lanjutkan Simpan / Update jika aman
        if (!empty($id_edit)) {
            $query = "UPDATE jadwal_pelajaran SET 
                      id_kelas='$id_k', id_mapel='$id_m', id_guru='$id_g', 
                      hari='$hari', jam_mulai='$jam_m', jam_selesai='$jam_s' 
                      WHERE id='$id_edit'";
            $msg = "updated_jampel";
        } else {
            $query = "INSERT INTO jadwal_pelajaran (id_kelas, id_mapel, id_guru, hari, jam_mulai, jam_selesai) 
                      VALUES ('$id_k','$id_m','$id_g','$hari','$jam_m','$jam_s')";
            $msg = "added_jampel";
        }

        if (mysqli_query($koneksi, $query)) {
            header("Location: Admin.php?p=jadwal&msg=$msg"); exit;
        }
    }
}
// LOGIKA HAPUS
if (isset($_GET['hapus_jadwal'])) {
    $id = $_GET['hapus_jadwal'];
    mysqli_query($koneksi, "DELETE FROM jadwal_pelajaran WHERE id='$id'");
    header("Location: Admin.php?p=jadwal&msg=deleted_jampel"); exit;
}



// ============================================================
// TAMPILAN (MASTER LAYOUT)
// ============================================================
$body_class = ($page === 'edit_siswa' || $page === 'tambah_siswa' || $page === 'edit_guru') ? 'siswa-form-body' : '';
$show_sidebar = ($body_class == '') ? true : false;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Classentix</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="<?php echo $body_class; ?>">

    <?php if ($show_sidebar) echo getSidebarAdmin($page, $jumlah_pending); ?>

    <?php 
    switch ($page) {
        case 'profil': viewAdminProfil($user_data, $foto, $nama_user); break;
        case 'guru': viewAdminGuru($foto, $nama_user, $jumlah_pending); break;
        case 'detail_guru': viewDetailGuru((int)$_GET['id']); break;
        case 'mapel': viewAdminMapel(); break;
        case 'jadwal': viewAdminJadwal(); break;
        case 'kelas': viewAdminKelas(); break; 
        default: viewMurid(); break;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/admin.js"></script>

</body>
</html>

<?php



// VIEW FUNCTIONS

function getSidebarAdmin($activePage, $jumlah_pending) {
    $a = function($page) use ($activePage) { return ($activePage === $page) ? ' active' : ''; };

    $dot_guru = ($jumlah_pending > 0) ? '<span class="dot-notif"></span>' : '';
    return '
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo_classentix.png" alt="Logo Classentix">
            Classentix
        </div>
        <div class="menu-section">
            
            <div class="menu-label">Manajemen Data</div>
            <a href="Admin.php?p=kelas" class="menu-item'.$a('kelas').'"><div><span><i class="fa-solid fa-school"></i></span> Kelas</div></a>
            <a href="Admin.php" class="menu-item'.$a('').'"><div><span><i class="fa-solid fa-users"></i></span> Daftar Murid</div></a>
            <a href="Admin.php?p=guru" class="menu-item'.$a('guru').'"><div><span><i class="fa-solid fa-chalkboard-user"></i></span> Guru</div>'.(($jumlah_pending > 0)?'<span class="badge-sidebar">'.$jumlah_pending.'</span>':'').'</a>
            <a href="Admin.php?p=mapel" class="menu-item'.$a('mapel').'"><div><span><i class="fa-solid fa-book"></i></span> Mata Pelajaran</div></a>
            <a href="Admin.php?p=jadwal" class="menu-item'.$a('jadwal').'"><div><span><i class="fa-solid fa-calendar-days"></i></span> Jadwal</div></a>
            
            <div class="menu-label" style="margin-top: 20px;">Pengaturan</div>
            <a href="Admin.php?p=profil" class="menu-item'.$a('profil').'"><div><span><i class="fa-solid fa-user-gear"></i></span> Profil</div></a>

            <div class="menu-label" style="margin-top:20px;">Eksternal</div>
            <a href="Halaman_Awal.php" class="menu-item"><div><span><i class="fa-solid fa-house"></i></span> Landing Page</div></a>
            
        </div>
        <a href="logout.php" class="logout-btn"><span><i class="fa-solid fa-right-from-bracket"></i></span> Logout</a>
    </div>';
}



function viewAdminProfil($user_data, $foto_tampil, $nama_user) {
    ?>
    <div class="profil-content-admin">
        <div class="profil-widget-admin">
            <div class="profil-user-top">
                <img src="<?php echo $foto_tampil; ?>" alt="Profile">
                <div class="profil-header-info">
                    <p class="subtitle">Administrator</p>
                    <h2><?php echo htmlspecialchars($nama_user); ?> </h2>
                    <p class="status"><i class="fa-solid fa-shield-check"></i> Akun Terverifikasi</p>
                </div>
            </div>
            <a href="Admin.php" class="change-photo-btn" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>

        <div class="profil-card">
            <div class="profil-section-title">
                <h3>Informasi Personal</h3>
                <p>Perbarui data diri dan keamanan akun administrator Anda secara berkala.</p>
            </div>

            <form id="formProfil" action="Admin.php?p=profil" method="POST" enctype="multipart/form-data">
                
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
                            Disarankan foto formal ukuran kotak.<br>Format: JPG, PNG. Maks 2MB.
                        </p>
                    </div>
                    <input type="file" name="foto" id="inputFotoProfil" hidden accept="image/*">
                </div>

                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label><i class="fa-solid fa-id-badge"></i> Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                    </div>

                    <div class="profil-form-group">
                        <label><i class="fa-solid fa-user-tag"></i> Role Akses</label>
                        <input type="text" value="Super Administrator" disabled>
                    </div>

                    <div class="profil-form-group">
                        <label><i class="fa-solid fa-signature"></i> Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="profil-form-group">
                        <label><i class="fa-solid fa-lock"></i> Password Baru</label>
                        <input type="password" name="password" id="passwordProfil" placeholder="Sandi baru (Opsional)">
                    </div>
                </div>

                <div class="profil-form-footer">
                    <button type="submit" name="update_profil" class="btn-save">
                        <i class="fa-solid fa-check-double"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}



function viewAdminKelas() {
    global $koneksi;
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
    ?>
    <div class="content">
        <div class="jadwal-header">
            <h1>Manajemen Kelas</h1>
            <p>Daftar kelas yang tersedia di sistem.</p>
        </div>
        
        <div class="card" id="areaTambahGlobal">
            <h3>Tambah Kelas Baru</h3>
            <form method="POST" action="Admin.php?p=kelas" class="form-row">
                <input type="text" name="nama_kelas" placeholder="Contoh: XII RPL 1" required class="flex-grow">
                <button type="submit" name="tambah_kelas" id="btnTriggerTambah" class="btn-submit">Tambah</button>
            </form>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-school" style="color:var(--primary); margin-right:8px;"></i> Data Kelas</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Nomor</th>
                            <th>Nama Kelas</th>
                            <th>Jumlah Murid</th>
                            <th style="text-align:center;" width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($koneksi, "SELECT kelas.*, COUNT(siswa.id) AS total_murid 
                                                    FROM kelas 
                                                    LEFT JOIN siswa ON kelas.id = siswa.id_kelas 
                                                    GROUP BY kelas.id 
                                                    ORDER BY kelas.id DESC");
                        $no = 1; 

                        while($r = mysqli_fetch_assoc($q)) {
                            if ($edit_id == $r['id']) {
                                // TAMPILAN BARIS EDIT
                                echo "<tr>
                                        <form method='POST' action='Admin.php?p=kelas'>
                                            <td>{$no}<input type='hidden' name='id_kelas' value='{$r['id']}'></td>
                                            <td><input type='text' name='nama_kelas' value='{$r['nama_kelas']}' required class='search-input w-100'></td>
                                            <td><span class='kode-badge'>{$r['total_murid']} Siswa</span></td> 
                                            <td style='text-align:center;'>
                                                <button type='submit' name='update_kelas' class='link-edit' style='border:none; background:none; cursor:pointer;'><i class='fas fa-save'></i> Simpan</button>
                                                <a href='Admin.php?p=kelas' class='link-delete'><i class='fas fa-times'></i> Batal</a>
                                            </td>
                                        </form>
                                    </tr>";
                            } else {
                                // TAMPILAN BARIS NORMAL
                                echo "<tr>
                                        <td>{$no}</td>
                                        <td><b>{$r['nama_kelas']}</b></td>
                                        <td><span class='day-badge' style='background: #f1f5f9; color: #475569;'><b>{$r['total_murid']}</b> Murid</span></td> 
                                        <td style='text-align:center;'>
                                            <a href='Admin.php?p=kelas&edit_id={$r['id']}' class='link-edit btn-aksi'><i class='fas fa-edit'></i> Edit</a>
                                            <a href='Admin.php?p=kelas&hapus_kelas={$r['id']}' class='link-delete btn-aksi' onclick='konfirmasiHapus(event)'><i class='fas fa-trash'></i> Hapus</a>
                                        </td>
                                    </tr>";
                            }
                            
                            // 4. Tambahkan angka 1 setiap kali loop selesai untuk baris berikutnya
                            $no++; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}



function viewMurid() {
    global $koneksi;
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

    $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas");
    $kelas_array = [];
    $kelas_options = "";
    while($rk = mysqli_fetch_assoc($q_kelas)) {
        $kelas_array[] = $rk;
        $kelas_options .= "<option value='{$rk['id']}'>{$rk['nama_kelas']}</option>";
    }
    ?>
    <div class="content">
        <div class="jadwal-header">
            <h1>Manajemen Murid</h1>
            <p>Kelola data siswa berdasarkan kelas masing-masing.</p>
        </div>

        <div class="card" id="areaTambahGlobal">
            <h3>Tambah Murid Baru</h3>
            <form method="POST" action="Admin.php?p=siswa" class="form-inline-flex">
                <input type="number" name="absen" placeholder="No" class="search-input input-no" required>
                <input type="text" name="nama" placeholder="Nama Lengkap Siswa" class="search-input input-nama" required>
                <select name="id_kelas" class="search-input input-kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php echo $kelas_options; ?>
                </select>
                <button type="submit" name="simpan" id="btnTriggerTambah" class="btn btn-primary">
                    Tambah
                </button>
            </form>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-users" style="color:var(--primary); margin-right:8px;"></i> Data Murid</h3>
                <select id="filterKelas" class="search-input select-filter">
                    <option value="all">-- Semua Kelas Anda --</option>
                    <?php echo $kelas_options; ?>
                </select>
            </div>

            <table id="tabelSiswa">
                <thead>
                    <tr>
                        <th class="th-absen">Nomor Absen</th>
                        <th class="th-nama">Nama Lengkap</th>
                        <th class="th-kelas">Kelas</th>
                        <th class="th-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodySiswa">
                    <?php
                    $q = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id ORDER BY kelas.nama_kelas, siswa.absen ASC");
                    while ($row = mysqli_fetch_assoc($q)) { 
                        if ($edit_id == $row['id']) { ?>
                            <tr class="row-editing">
                                <form method="POST" action="Admin.php?p=siswa">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <td class="text-center">
                                        <input type="number" name="absen" value="<?php echo $row['absen']; ?>" class="input-table" required>
                                    </td>
                                    <td>
                                        <input type="text" name="nama" value="<?php echo htmlspecialchars($row['nama']); ?>" class="input-table" required>
                                    </td>
                                    <td>
                                        <select name="id_kelas" class="input-table" required>
                                            <?php foreach($kelas_array as $k) {
                                                $sel = ($k['id'] == $row['id_kelas']) ? 'selected' : '';
                                                echo "<option value='{$k['id']}' $sel>{$k['nama_kelas']}</option>";
                                            } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="action-wrapper">
                                            <button type="submit" name="update" class="link-edit" style="border:none; background:none; cursor:pointer;"><i class="fas fa-save"></i> Simpan</button>
                                            <a href="Admin.php?p=siswa" class="link-delete"><i class="fas fa-times"></i> Batal</a>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        <?php } else { ?>
                            <tr class="siswa-row" data-kelas="<?php echo $row['id_kelas']; ?>">
                                <td class="text-center"><strong><?php echo $row['absen']; ?></strong></td>
                                <td style="text-align: left;"><b><?php echo htmlspecialchars($row['nama']); ?></b></td>
                                <td class="text-center"><span class="day-badge"><?php echo htmlspecialchars($row['nama_kelas']); ?></span></td>
                                <td>
                                    <div class="action-wrapper">
                                        <a href="Admin.php?p=siswa&edit_id=<?php echo $row['id']; ?>" class="link-edit btn-aksi"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="Admin.php?p=siswa&hapus_siswa=<?php echo $row['id']; ?>" class="link-delete btn-aksi" onclick="konfirmasiHapus(event)"><i class="fas fa-trash"></i> Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    <tr id="emptyRow" style="display: none;"><td colspan="4" class="empty-state">Tidak ada data murid.</td></tr>
                </tbody>
            </table>
            <div id="paginationContainer" class="pagination-container" style="display: none; padding: 15px;"></div>
        </div>
    </div>
    <?php
}



function viewAdminGuru($foto, $nama_user, $jumlah_pending) {
    global $koneksi;

    $is_validasi = (isset($_GET['panel']) && $_GET['panel'] == 'validasi');
    $header_title = $is_validasi ? "Persetujuan Akun Guru" : "Manajemen Guru";
    $header_desc = $is_validasi ? "Verifikasi data pendaftaran pengajar baru." : "Kelola Profil Pengajar Aktif.";
    
    // Atur display awal boks berdasarkan parameter URL
    $panel_guru_style = $is_validasi ? 'style="display:none;"' : '';
    $panel_validasi_style = $is_validasi ? 'style="display:block;"' : 'style="display:none;"';
    ?>
    <div class="content"><div class="jadwal-header">
            <h1 id="main-header-title"><?php echo $header_title; ?></h1>
            <p id="main-header-desc"><?php echo $header_desc; ?></p>
        </div>

        <div id="panel-guru" <?php echo $panel_guru_style; ?>>
            <div class="header-content" style="margin-bottom:20px;">
                <div></div>
                <button id="btn-surat" class="btn-surat-toggle" title="Lihat Permintaan Validasi Guru" style="position: relative;" onclick="window.location.href='Admin.php?p=guru&panel=validasi'">
                    <i class="fas fa-envelope"></i>
                    <?php if ($jumlah_pending > 0): ?>
                        <span class="dot-notif"><?php echo $jumlah_pending; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-chalkboard-user" style="color:var(--primary); margin-right:8px;"></i> Daftar Guru Aktif</h3>
                </div>
                <div class="table-card">
                    <table>
                        <thead>
                            <tr><th>NIP</th><th>Nama & Username</th><th>Mata Pelajaran</th><th style="text-align:center;">Opsi</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT g.id, g.nip, u.nama_lengkap, u.username, m.nama_mapel 
                                                         FROM guru g 
                                                         JOIN users u ON g.id_user = u.id 
                                                         LEFT JOIN mapel m ON g.id_mapel = m.id
                                                         WHERE u.status_validasi = 'aktif' 
                                                         ORDER BY g.id DESC");
                            if (mysqli_num_rows($q) > 0) {
                                while ($row = mysqli_fetch_assoc($q)) { ?>
                                    <tr>
                                        <td><strong style="color:var(--primary);"><?php echo htmlspecialchars($row['nip']); ?></strong></td>
                                        <td class="pad-20">
                                            <div style="font-weight:700; color:var(--text-main); font-size:15px;">
                                                <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                                            </div>
                                            <span class="kode-badge">@<?php echo htmlspecialchars($row['username']); ?></span>
                                        </td>
                                        <td><span class="kode-badge"><?php echo $row['nama_mapel'] ? htmlspecialchars($row['nama_mapel']) : 'Kosong'; ?></span></td>
                                        <td style="text-align:center;">
                                            <div class="action-wrapper">
                                                <a href="Admin.php?p=detail_guru&id=<?php echo $row['id']; ?>" class="link-edit"><i class="fas fa-edit"></i> Detail</a>
                                                <a href="Admin.php?hapus_guru=<?php echo $row['id']; ?>" class="link-delete" onclick="konfirmasiHapus(event, this.href)"><i class="fas fa-trash"></i> Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                            } else {
                                echo "<tr><td colspan='4' class='empty-state'>Belum ada guru aktif.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- END panel-guru -->



        <!--  PANEL VALIDASI  -->
        <div id="panel-validasi" <?php echo $panel_validasi_style; ?>>
            <div class="header-content" style="margin-bottom:20px;">
                <div></div>
                <button id="btn-back-guru" class="btn btn-outline" onclick="window.location.href='Admin.php?p=guru'">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Guru
                </button>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-user-clock" style="color:var(--primary); margin-right:8px;"></i> Permintaan Validasi Akun Guru</h3>
                </div>
                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama & Username</th>
                                <th style="text-align:center;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($koneksi, "SELECT u.*, g.nip FROM users u 
                                                             LEFT JOIN guru g ON u.id = g.id_user 
                                                             WHERE u.role='guru' AND u.status_validasi='pending' 
                                                             ORDER BY u.id DESC");
                            
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { 
                                    $form_id = "form_validasi_" . $data['id']; ?>
                                    <tr>
                                        <td style="padding: 10px; vertical-align: middle;">
                                            <form id="<?php echo $form_id; ?>" method="POST" action="Admin.php" style="display:none;">
                                                <input type="hidden" name="id_user" value="<?php echo $data['id']; ?>">
                                                <input type="hidden" name="simpan_validasi" value="1">
                                            </form>
                                            <strong style="color:var(--primary); font-size:15px;"><?php echo !empty($data['nip']) ? htmlspecialchars($data['nip']) : '-'; ?></strong>
                                        </td>
                                        <td class="pad-20">
                                            <div style="font-weight:700; color:var(--text-main); font-size:15px;">
                                                <?php echo htmlspecialchars($data['nama_lengkap']); ?>
                                            </div>
                                            <span class="kode-badge">@<?php echo htmlspecialchars($data['username']); ?></span>
                                        </td>
                                        <td style="text-align:center; padding: 10px;">
                                            <div class="btn-group" style="justify-content:center; display:flex; gap:10px;">
                                                <button type="button" class="btn-action btn-approve" style="background:#10B981; color:white; padding:8px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:600;" onclick="document.getElementById('<?php echo $form_id; ?>').submit();">
                                                    <i class="fas fa-check"></i> Terima
                                                </button>
                                                <a href="Admin.php?hapus_user_pending=<?php echo $data['id']; ?>" class="btn-action btn-reject" style="background:#EF4444; color:white; padding:8px 16px; text-decoration:none; border-radius:6px; font-weight:600;" onclick="konfirmasiHapus(event, this.href)">
                                                    <i class="fas fa-times"></i> Tolak
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                            } else {
                                echo '<tr><td colspan="3" class="empty-state" style="text-align:center; padding:30px;">Tidak ada permintaan validasi saat ini.</td></tr>';
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /#panel-validasi -->
    </div>
    <?php
}


// --- 2. HALAMAN DETAIL & EDIT GURU ---
function viewDetailGuru($id_guru) {
    global $koneksi;
    
    $id_guru_safe = (int)$id_guru;
    $q = mysqli_query($koneksi, "SELECT u.*, g.id as id_guru, g.nip, g.id_mapel 
                                 FROM guru g 
                                 JOIN users u ON g.id_user = u.id 
                                 WHERE g.id = $id_guru_safe");
    $data = mysqli_fetch_assoc($q);
    
    if (!$data) {
        echo "<script>window.location='Admin.php?p=guru&msg=not_found';</script>";
        exit;
    }

    // Ambil array kelas yang diampu guru ini
    $kelas_diampu = [];
    $q_gk = mysqli_query($koneksi, "SELECT id_kelas FROM guru_kelas WHERE id_guru = $id_guru_safe");
    while($row = mysqli_fetch_assoc($q_gk)){
        $kelas_diampu[] = $row['id_kelas'];
    }
    ?>
    <div class="content">
        <div class="header-title">
            <div>
                <h1>Detail & Edit Pengajar</h1>
                <p>Kelola data lengkap <strong><?php echo htmlspecialchars($data['nama_lengkap']); ?></strong>.</p>
            </div>
            <a href="Admin.php?p=guru" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="profil-card">
            <form id="formDetailGuru" method="POST" action="Admin.php">
                <input type="hidden" name="id_guru" value="<?php echo $data['id_guru']; ?>">
                <input type="hidden" name="id_user" value="<?php echo $data['id']; ?>">

                <h3 style="margin-bottom:20px; color:var(--primary); border-bottom:1px solid #eee; padding-bottom:10px;">Informasi Pribadi & Akun</h3>
                <div class="profil-form-grid" style="margin-bottom: 40px;">
                    <div class="profil-form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="profil-form-group">
                        <label>NIP</label>
                        <input type="text" name="nip" value="<?php echo htmlspecialchars($data['nip']); ?>" minlength="18" maxlength="18" required>
                    </div>
                    <div class="profil-form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>" minlength="11" maxlength="13" required>
                    </div>
                    <div class="profil-form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required>
                    </div>
                    <div class="profil-form-group">
                        <label>Password Baru <span style="color:var(--text-muted); text-transform:none; font-weight:normal;">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" placeholder="Ketik password baru...">
                    </div>
                </div>

                <h3 style="margin-bottom:20px; color:var(--primary); border-bottom:1px solid #eee; padding-bottom:10px;">Penugasan Mengajar</h3>
                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label>Mata Pelajaran (1 Mapel)</label>
                        <select name="id_mapel" style="width:100%; padding:14px 18px; border-radius:12px; border:1px solid var(--border-color); outline:none;" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php
                            $q_mapel = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");
                            while($m = mysqli_fetch_assoc($q_mapel)) {
                                $sel = ($m['id'] == $data['id_mapel']) ? 'selected' : '';
                                echo "<option value='{$m['id']}' $sel>{$m['nama_mapel']} ({$m['code_mapel']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="profil-form-group">
                        <label>Kelas Yang Diampu (Bisa Lebih Dari 1)</label>
                        <div class="checkbox-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; background:#F8FAFC; padding:15px; border-radius:12px; border:1px solid var(--border-color);">
                            <?php
                            $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_assoc($q_kelas)) {
                                $checked = in_array($k['id'], $kelas_diampu) ? 'checked' : '';
                                echo "<label style='display:flex; align-items:center; gap:8px; font-weight:600; font-size:14px; text-transform:none; margin:0; cursor:pointer;'>
                                        <input type='checkbox' name='kelas[]' value='{$k['id']}' $checked style='width:auto; accent-color: var(--primary);'> {$k['nama_kelas']}
                                      </label>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <button type="submit" name="update_detail_guru" class="btn-save" style="margin-top: 20px;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>
    <?php
}



function viewAdminMapel() {
    global $koneksi;
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
    ?>
    <div class="content">
        <div class="jadwal-header">
            <h1>Data Mata Pelajaran</h1>
            <p>Kelola daftar mapel sekolah.</p>
        </div>
        
        <div class="card" id="areaTambahGlobal">
            <h3>Tambah Mata Pelajaran Baru</h3>
            <form method="POST" action="Admin.php?p=mapel" class="form-inline-flex">
                <input type="text" name="kode" id="inputKodeTambah" placeholder="Kode (Cth: MTK-1)" class="search-input" style="width: 160px;" required>
                <input type="text" name="nama" id="inputNamaTambah" placeholder="Nama Mata Pelajaran" class="search-input flex-grow" required>
                <button type="submit" name="tambah" id="btnTriggerTambah" class="btn btn-primary">Simpan</button>
            </form>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border-color); background:#FFF;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-book" style="color:var(--primary); margin-right:8px;"></i> Daftar Mata Pelajaran</h3>
            </div>
            <div class="table-card">
                <table id="tabelMapel" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Kode</th>
                            <th>Nama Mata Pelajaran</th>
                            <th style="width: 180px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($q)) { 
                            if ($edit_id == $row['id']) { ?>
                                <tr class="row-editing">
                                    <form method="POST" action="Admin.php?p=mapel">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <td>
                                            <input type="text" name="kode" value="<?php echo htmlspecialchars($row['code_mapel']); ?>" class="input-table" required>
                                        </td>
                                        <td>
                                            <input type="text" name="nama" value="<?php echo htmlspecialchars($row['nama_mapel']); ?>" class="input-table" required>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-wrapper">
                                                <button type="submit" name="update_mapel" class="link-edit" style="border:none; background:none; cursor:pointer;"><i class="fas fa-save"></i> Simpan</button>
                                                <a href="Admin.php?p=mapel" class="link-delete"><i class="fas fa-times"></i> Batal</a>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td><span class="kode-badge"><?php echo htmlspecialchars($row['code_mapel']); ?></span></td>
                                    <td style="font-weight:600; color:var(--text-main);"><?php echo htmlspecialchars($row['nama_mapel']); ?></td>
                                    <td class="text-center">
                                        <div class="action-wrapper">
                                            <a href="Admin.php?p=mapel&edit_id=<?php echo $row['id']; ?>" class="link-edit btn-aksi"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="Admin.php?p=mapel&hapus_mapel=<?php echo $row['id']; ?>" class="link-delete btn-aksi" onclick="return konfirmasiHapus(event)"><i class="fas fa-trash"></i> Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}



function viewAdminJadwal() {
    global $koneksi;

    // --- Logika Jam Pelajaran (Sama seperti sebelumnya) ---
    $start_time = "07:00";
    $jp_duration = 45;
    $break_duration = 30;
    $jam_list = [];
    $current = strtotime($start_time);
    for ($i = 1; $i <= 13; $i++) {
        $jam_list[$i] = date("H:i", $current);
        $current = strtotime("+$jp_duration minutes", $current);
        if ($i == 6) $current = strtotime("+$break_duration minutes", $current);
    }

    // --- Ambil Data Master ---
    $g_res = mysqli_query($koneksi, "SELECT guru.id, users.nama_lengkap, guru.id_mapel, mapel.nama_mapel 
                                     FROM guru JOIN users ON guru.id_user = users.id 
                                     JOIN mapel ON guru.id_mapel = mapel.id WHERE users.status_validasi='aktif'");
    $guru_data = [];
    while($g = mysqli_fetch_assoc($g_res)) { $guru_data[] = $g; }

    $k_res = mysqli_query($koneksi, "SELECT kelas.id, kelas.nama_kelas, guru_kelas.id_guru FROM kelas JOIN guru_kelas ON kelas.id = guru_kelas.id_kelas");
    $kelas_data = [];
    while($k = mysqli_fetch_assoc($k_res)) { $kelas_data[] = $k; }

    // --- AMBIL SEMUA JADWAL UNTUK VALIDASI JS ---
    $all_schedules = [];
    $sch_res = mysqli_query($koneksi, "SELECT id, id_kelas, id_guru, hari, jam_mulai, jam_selesai FROM jadwal_pelajaran");
    while($s = mysqli_fetch_assoc($sch_res)) { $all_schedules[] = $s; }

    $is_editing = isset($_GET['edit_id']);
    $edit_id = $is_editing ? $_GET['edit_id'] : null;
    ?>
    
    <script>
        const dataJadwalGlobal = <?= json_encode($all_schedules) ?>;
    </script>

    <div class="content" style="overflow-x: hidden;">
        <div class="jadwal-header">
            <h1>Manajemen Jadwal</h1>
        </div>


        <div class="card" id="areaTambahGlobal" <?= $is_editing ? 'style="opacity:0.5; pointer-events:none;"' : '' ?>>
            <h3>Tambah Jadwal Baru</h3>
            <form method="POST" id="formJadwal">
                <input type="hidden" name="id_mapel" id="id_mapel_hidden">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="hari" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <select name="jam_mulai" id="jam_mulai" required>
                            <option value="">- Mulai -</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $jam_list[$i] ?>" data-jp="<?= $i ?>">JP<?= $i ?> (<?= $jam_list[$i] ?>)</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <select name="jam_selesai" id="jam_selesai" required disabled>
                            <option value="">- Selesai -</option>
                            <?php for ($i = 2; $i <= 13; $i++): ?>
                                <option value="<?= $jam_list[$i] ?>" data-jp="<?= $i-1 ?>">JP<?= $i ?> (<?= $jam_list[$i] ?>)</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Guru & Mata Pelajaran</label>
                        <select name="id_guru" id="id_guru" required>
                            <option value="">- Pilih Guru -</option>
                            <?php foreach($guru_data as $r): ?>
                                <option value="<?= $r['id'] ?>" data-mapel-id="<?= $r['id_mapel'] ?>"><?= $r['nama_lengkap'] ?> - <?= $r['nama_mapel'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="id_kelas" id="id_kelas" required disabled>
                            <option value="">- Pilih Guru Dulu -</option>
                            <?php foreach($kelas_data as $r): ?>
                                <option value="<?= $r['id'] ?>" class="opt-kelas guru-<?= $r['id_guru'] ?>" style="display:none;"><?= $r['nama_kelas'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-button">
                        <button type="submit" name="simpan_jadwal" class="btn-submit" id="btnTriggerTambah">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
        
        
        <div class="card" style="margin-bottom: 20px; padding: 15px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <strong style="margin-right: 10px;">Filter Tabel:</strong>
            
            <select id="filterHari" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
                <option value="all">Semua Hari</option>
                <option value="Senin">Senin</option>
                <option value="Selasa">Selasa</option>
                <option value="Rabu">Rabu</option>
                <option value="Kamis">Kamis</option>
                <option value="Jumat">Jumat</option>
                <option value="Sabtu">Sabtu</option>
            </select>

            <select id="filterKelasJadwal" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
                <option value="all">Semua Kelas</option>
                <?php foreach($kelas_data as $r): ?>
                    <option value="<?= $r['nama_kelas'] ?>"><?= $r['nama_kelas'] ?></option>
                <?php endforeach; ?>
            </select>

            <select id="filterGuruJadwal" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; flex: 1; min-width: 150px;">
                <option value="all">Semua Guru</option>
                <?php foreach($guru_data as $r): ?>
                    <option value="<?= $r['nama_lengkap'] ?>"><?= $r['nama_lengkap'] ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" id="btnResetFilter" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Reset</button>
            <button type="button" id="btnCetakPDF" style="padding: 8px 15px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;"><i class="fas fa-file-pdf"></i> Cetak PDF</button>
        </div>


        <div class="table-container">
            <form method="POST" id="formEditInline">
                <input type="hidden" name="id_edit" value="<?= $edit_id ?? '' ?>">
                <input type="hidden" name="id_mapel" id="edit_id_mapel_hidden" value="">

                <table>
                    <thead>
                        <tr><th>Hari</th><th>Waktu</th><th>Kelas</th><th>Guru & Mata Pelajaran</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT jp.*, k.nama_kelas, m.nama_mapel, u.nama_lengkap as nama_guru 
                                FROM jadwal_pelajaran jp
                                JOIN kelas k ON jp.id_kelas = k.id
                                JOIN mapel m ON jp.id_mapel = m.id
                                JOIN guru g ON jp.id_guru = g.id
                                JOIN users u ON g.id_user = u.id
                                ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_mulai ASC";
                        $q = mysqli_query($koneksi, $sql);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            if ($is_editing && $edit_id == $row['id']): 
                                // --- TAMPILAN BARIS EDIT ---
                                ?>
                                <tr style="background-color: #f8f9fa;">
                                    <td>
                                        <select name="hari" required style="width:100%; padding:5px;">
                                            <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d): ?>
                                                <option value="<?= $d ?>" <?= $row['hari'] == $d ? 'selected' : '' ?>><?= $d ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="jam_mulai" id="edit_jam_mulai" required style="width:100%; padding:5px; margin-bottom:5px;">
                                            <option value="">- Mulai -</option>
                                            <?php for ($i = 1; $i <= 12; $i++): $val = $jam_list[$i]; ?>
                                                <option value="<?= $val ?>" data-jp="<?= $i ?>" <?= date('H:i', strtotime($row['jam_mulai'])) == $val ? 'selected' : '' ?>><?= $val ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <select name="jam_selesai" id="edit_jam_selesai" required style="width:100%; padding:5px;">
                                            <option value="">- Selesai -</option>
                                            <?php for ($i = 2; $i <= 13; $i++): $val = $jam_list[$i]; ?>
                                                <option value="<?= $val ?>" data-jp="<?= $i-1 ?>" <?= date('H:i', strtotime($row['jam_selesai'])) == $val ? 'selected' : '' ?>><?= $val ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="id_kelas" id="edit_id_kelas" required style="width:100%; padding:5px;">
                                            <?php foreach($kelas_data as $r): ?>
                                                <option value="<?= $r['id'] ?>" class="edit-opt-kelas edit-guru-<?= $r['id_guru'] ?>" <?= $row['id_kelas'] == $r['id'] ? 'selected' : '' ?> hidden><?= $r['nama_kelas'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="id_guru" id="edit_id_guru" required style="width:100%; padding:5px;">
                                            <?php foreach($guru_data as $r): ?>
                                                <option value="<?= $r['id'] ?>" data-mapel-id="<?= $r['id_mapel'] ?>" <?= $row['id_guru'] == $r['id'] ? 'selected' : '' ?>><?= $r['nama_lengkap'] ?> - <?= $r['nama_mapel'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="submit" name="simpan_jadwal" style="background:var(--primary); color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px;"><i class="fas fa-save"></i> Simpan</button>
                                        <a href="Admin.php?p=jadwal" style="background:gray; color:white; padding:5px 10px; text-decoration:none; border-radius:3px; display:inline-block; margin-top:5px;"><i class="fas fa-times"></i> Batal</a>
                                    </td>
                                </tr>
                            <?php else: 
                                // --- TAMPILAN BARIS NORMAL ---
                                ?>
                                <tr class="jadwal-row" data-hari="<?= $row['hari'] ?>" data-kelas="<?= $row['nama_kelas'] ?>" data-guru="<?= $row['nama_guru'] ?>">
                                    <td><span class="day-badge"><?= $row['hari'] ?></span></td>
                                    <td><?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?></td>
                                    <td><?= $row['nama_kelas'] ?></td>
                                    <td><?= $row['nama_guru'] ?> <br> <small>(<?= $row['nama_mapel'] ?>)</small></td>
                                    <td>
                                    <a href="Admin.php?p=jadwal&edit_id=<?= $row['id'] ?>" class="link-edit btn-aksi"><i class="fas fa-edit"></i> Edit</a> 
                                    <a href="Admin.php?hapus_jadwal=<?= $row['id'] ?>" class="link-delete btn-aksi" onclick="konfirmasiHapus(event)"><i class="fas fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endif; 
                        endwhile; ?>
                    </tbody>
                </table>
                <div id="paginationJadwal" class="pagination-container" style="display: none;"></div>
            </form>
        </div>
    </div>

    <div id="printArea" style="display:none;"></div>
    
    <?php
}
?>
<?php
session_start();
require_once 'koneksi/koneksi.php';

$action = $_GET['action'] ?? 'login';
$error_login = '';
$error_register = '';

// Variabel penampung data lama (agar input tidak tereset saat error)
$old_username_login = '';
$old_nama_lengkap   = '';
$old_no_telp        = '';
$old_nip            = '';
$old_username_reg   = '';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'Admin.php' : 'Guru.php'));
    exit;
}

/* ============================================================
   PROSES BACKEND: LOGIN & REGISTER
   ============================================================ */

// PROSES LOGIN
if (isset($_POST['login'])) {
    $u = mysqli_real_escape_string($koneksi, $_POST['username']);
    $p = $_POST['password'];
    
    // Simpan input username agar tidak hilang jika login gagal
    $old_username_login = $_POST['username'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$u'");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {
        if (password_verify($p, $data['password'])) {
            if ($data['status_validasi'] !== 'aktif') {
                $error_login = "Akun Anda berstatus pending. Tunggu validasi Admin!";
            } else {
                $_SESSION['status'] = "login";
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['user']   = $data['username'];
                $_SESSION['nama']   = $data['nama_lengkap'];
                $_SESSION['role']   = $data['role'];
                header("Location: " . ($data['role'] === 'admin' ? 'Admin.php' : 'Guru.php'));
                exit;
            }
        } else {
            $error_login = "Password yang Anda masukkan salah!";
        }
    } else {
        $error_login = "Username tidak terdaftar!";
    }
}

// PROSES REGISTER
if (isset($_POST['register'])) {
    $action = 'register'; // Pastikan panel tetap di Register jika gagal
    
    // Simpan input lama agar data tidak hilang ketika terjadi error
    $old_nama_lengkap   = $_POST['nama_lengkap'];
    $old_no_telp        = $_POST['no_telp'];
    $old_nip            = $_POST['nip'];
    $old_username_reg   = $_POST['username'];

    $nama_lengkap    = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username        = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password        = $_POST['password'];
    $no_telp         = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $nip             = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $role            = 'guru';
    $status_validasi = 'pending';

    if (!preg_match('/^[0-9]{11,13}$/', $no_telp)) {
        $error_register = "Gagal: Nomor Telepon harus angka & 11-13 karakter!";
    } elseif (!empty($nip) && !preg_match('/^[0-9]{18}$/', $nip)) {
        $error_register = "Gagal: NIP harus berupa angka tepat 18 karakter!";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
        $error_register = "Gagal: Password minimal 6 karakter & butuh kombinasi huruf besar, kecil, dan angka!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        
        if (mysqli_num_rows($cek_username) > 0) {
            $error_register = "Username sudah terdaftar! Gunakan yang lain.";
        } else {
            $query_user = "INSERT INTO users(username, password, nama_lengkap, role, status_validasi, no_telp)
                VALUES ('$username', '$hashed_password', '$nama_lengkap', '$role', '$status_validasi', '$no_telp')";
            
            if (mysqli_query($koneksi, $query_user)) {
                $id_user_baru = mysqli_insert_id($koneksi);
                $nip_final = empty($nip) ? '-' : $nip;
                $query_guru = "INSERT INTO guru (id_user, nip) VALUES ('$id_user_baru', '$nip_final')";
                mysqli_query($koneksi, $query_guru);
                
                // Beritahu sistem bahwa registrasi sukses & bersihkan form input lama
                $success_register = true;
                $old_nama_lengkap = $old_no_telp = $old_nip = $old_username_reg = '';
            } else {
                $error_register = "Registrasi gagal: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - Classentix</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/login_register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="container <?php echo $action === 'register' ? 'active' : ''; ?>">
        
        <div class="form-box login">
            <form action="login_register.php?action=login" method="POST">
                <h1>Login</h1>
                
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($old_username_login); ?>" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
               
                <button type="submit" name="login" class="btn">Masuk</button>

                <a href="Halaman_Awal.php" class="back-home">
                    <i class='bx bx-arrow-back'></i> Kembali ke Beranda
                </a>
            </form>
        </div>

        <div class="form-box register">
            <form action="login_register.php?action=register" method="POST">
                <h1>Registration</h1>

                <div class="input-box">
                    <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($old_nama_lengkap); ?>" required>
                    <i class='bx bxs-id-card'></i>
                </div>
                <div class="input-box">
                    <input type="text" name="no_telp" placeholder="No. Telepon (11-13 digit)" value="<?php echo htmlspecialchars($old_no_telp); ?>" required>
                    <i class='bx bxs-phone'></i>
                </div>
                <div class="input-box">
                    <input type="text" name="nip" placeholder="NIP Guru (18 digit)" value="<?php echo htmlspecialchars($old_nip); ?>" required>
                    <i class='bx bxs-briefcase'></i>
                </div>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($old_username_reg); ?>" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                
                <button type="submit" name="register" class="btn">Register</button>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <img src="assets/images/logo_classentix.png" alt="Logo Classentix" class="toggle-logo">
                <h1>Welcome to Classentix</h1>
                <p>Belum memiliki akun guru di Classentix?</p>
                <button class="btn register-btn">Register</button>
            </div>

            <div class="toggle-panel toggle-right">
                <img src="assets/images/logo_classentix.png" alt="Logo Classentix" class="toggle-logo">
                <h1>Welcome to Classentix</h1>
                <p>Sudah memiliki akun? Silakan masuk.</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <script src="assets/js/login_register.js"></script>

    <?php if (!empty($error_login)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Masuk',
                    text: <?php echo json_encode($error_login); ?>,
                    confirmButtonColor: '#4F46E5'
                });
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error_register)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Registrasi',
                    text: <?php echo json_encode($error_register); ?>,
                    confirmButtonColor: '#4F46E5'
                });
            });
        </script>
    <?php endif; ?>

    <?php if (isset($success_register) && $success_register): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil',
                    text: 'Akun pengajar Anda berhasil dibuat dan berstatus PENDING.',
                    confirmButtonColor: '#4F46E5'
                }).then((result) => {
                    window.location.href = 'login_register.php?action=login';
                });
            });
        </script>
    <?php endif; ?>

</body>
</html>
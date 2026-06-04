/* ============================================================
   GURU JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    
    
    // --- 1. PREVIEW FOTO PROFIL ---
    const primaryColor = '#4F46E5'; // Warna tema utama Classentix

    // ==========================================
    // --- 1. HANDLING REDIRECT ALERT (URL) ---
    // ==========================================
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg) {
        const alertList = {
            'success_profile': {
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil Anda berhasil diperbarui.'
            },
            'error_password': {
                icon: 'error',
                title: 'Gagal Validasi',
                text: 'Password harus mengandung kombinasi huruf besar, huruf kecil, dan angka!'
            },
            'error_upload': {
                icon: 'error',
                title: 'Gagal Upload',
                text: 'Pastikan format gambar berupa JPG/JPEG/PNG dan ukuran maksimal 2MB.'
            },
            'error_db': {
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: 'Terjadi kesalahan saat menyimpan data ke database.'
            }
        };

        if (alertList[msg]) {
            Swal.fire({
                icon: alertList[msg].icon,
                title: alertList[msg].title,
                text: alertList[msg].text,
                confirmButtonColor: primaryColor
            });

            // Bersihkan parameter URL browser tanpa memicu reload halaman
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?p=profil";
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
    }

    // ==========================================
    // --- 2. PREVIEW & VALIDASI UKURAN FOTO ---
    // ==========================================
    const inputFotoProfil = document.getElementById('inputFotoProfil');
    const previewImg = document.getElementById('preview');

    if (inputFotoProfil) {
        inputFotoProfil.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                // Validasi ukuran gambar (Maksimal 2MB)
                if (this.files[0].size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Terlalu Besar',
                        text: 'Ukuran gambar terlalu besar! Maksimal adalah 2MB.',
                        confirmButtonColor: primaryColor
                    });
                    this.value = ''; // Reset input file
                    return;
                }

                // Render preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImg) previewImg.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // ==========================================
    // --- 3. VALIDASI FORM & PASSWORD PROFIL ---
    // ==========================================
    const formProfil = document.getElementById('formProfil');
    const passwordInput = document.getElementById('passwordProfil');

    if (formProfil && passwordInput) {
        formProfil.addEventListener('submit', function (e) {
            const password = passwordInput.value;

            // Jika user mengisi password baru, lakukan pengecekan format
            if (password.length > 0) {
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
                
                if (!passwordPattern.test(password)) {
                    e.preventDefault(); // Menghentikan pengiriman form ke PHP
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sandi Kurang Kuat',
                        text: 'Password baru wajib mengandung kombinasi huruf besar, huruf kecil, dan angka!',
                        confirmButtonColor: primaryColor
                    });
                    
                    passwordInput.focus();
                    return;
                }
            }
        });
    }

    // --- 2. FILTER & PAGINATION DAFTAR MURID (CLIENT-SIDE) ---
    const filterKelas = document.getElementById('filterKelas');
    const tbodySiswa = document.getElementById('tbodySiswa');
    const paginationContainer = document.getElementById('paginationContainer');
    const emptyRow = document.getElementById('emptyRow');
    
    if (filterKelas && tbodySiswa) {
        const allRows = Array.from(document.querySelectorAll('.siswa-row'));
        const rowsPerPage = 10;
        let currentPage = 1;

        function renderTable() {
            const selectedKelas = filterKelas.value;

            // Filter data siswa berdasarkan atribut data-kelas
            const filteredRows = allRows.filter(row => {
                return selectedKelas === 'all' || row.getAttribute('data-kelas') === selectedKelas;
            });
            
            // Sembunyikan semua baris
            allRows.forEach(row => row.style.display = 'none');
            if (emptyRow) emptyRow.style.display = 'none';

            // Jika kosong tampilkan baris info kosong
            if (filteredRows.length === 0) {
                if (emptyRow) emptyRow.style.display = 'table-row';
                if (paginationContainer) paginationContainer.style.display = 'none';
                return;
            }

            // Hitung total halaman data ter-filter
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage > totalPages) currentPage = 1;

            // Menampilkan data sesuai limit baris per halaman
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            filteredRows.slice(start, end).forEach(row => {
                row.style.display = 'table-row';
            });

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (!paginationContainer) return;
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'flex';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                btn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
                btn.onclick = () => {
                    currentPage = i;
                    renderTable();
                    window.scrollTo(0, 0); // Reset scroll ke atas halaman
                };
                paginationContainer.appendChild(btn);
            }
        }

        filterKelas.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });

        // Inisialisasi awal table rendering
        renderTable();
    }


    // --- 3. FILTER AGENDA MENGAJAR GURU (CLIENT-SIDE) ---
    const filterHariGuru = document.getElementById('filterHariGuru');
    const emptyRowGuru = document.getElementById('emptyRowGuru');
    
    if (filterHariGuru) {
        // Ambil semua baris jadwal guru yang dicetak oleh PHP
        const allJadwalRows = Array.from(document.querySelectorAll('.jadwal-guru-row'));

        function renderJadwalGuru() {
            const selectedHari = filterHariGuru.value;
            let barisTercetak = 0;

            allJadwalRows.forEach(row => {
                const hariJadwal = row.getAttribute('data-hari');

                // Jika memilih 'all' atau hari pada baris cocok dengan filter
                if (selectedHari === 'all' || hariJadwal === selectedHari) {
                    row.style.display = 'table-row';
                    barisTercetak++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan pesan kosong jika tidak ada satupun jadwal yang cocok
            if (emptyRowGuru) {
                if (barisTercetak === 0 && allJadwalRows.length > 0) {
                    emptyRowGuru.style.display = 'table-row';
                } else {
                    emptyRowGuru.style.display = 'none';
                }
            }
        }

        // Jalankan fungsi setiap kali dropdown filter berubah nilai
        filterHariGuru.addEventListener('change', renderJadwalGuru);
    }

});
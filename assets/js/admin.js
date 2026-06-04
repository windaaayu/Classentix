// --- LOGIKA PENGUNCIAN GLOBAL ---
let isAddingManual = false; // Untuk melacak jika form tambah manual sedang diisi

function checkGlobalLock() {
    const urlParams = new URLSearchParams(window.location.search);
    const isEditing = urlParams.has('edit_id');

    if (isEditing || isAddingManual) {
        disableSemuaTombol();
        
        // Redupkan area form tambah jika sedang edit
        const areaTambah = document.getElementById('areaTambahGlobal');
        if (areaTambah && isEditing) {
            areaTambah.style.opacity = '0.5';
            areaTambah.style.pointerEvents = 'none';
        }
    } else {
        enableSemuaTombol();
    }
}

function disableSemuaTombol() {
    const filterBtn = document.getElementById('filterKelas');
    const tambahBtn = document.getElementById('btnTriggerTambah');
    
    if(filterBtn) filterBtn.disabled = true;
    if(tambahBtn) {
        tambahBtn.style.opacity = '0.5';
        tambahBtn.style.cursor = 'not-allowed';
    }
    
    document.querySelectorAll('.btn-aksi').forEach(btn => {
        btn.style.opacity = '0.3';
        btn.style.pointerEvents = 'none'; 
    });
}

function enableSemuaTombol() {
    const filterBtn = document.getElementById('filterKelas');
    const tambahBtn = document.getElementById('btnTriggerTambah');
    
    if(filterBtn) filterBtn.disabled = false;
    if(tambahBtn) {
        tambahBtn.style.opacity = '1';
        tambahBtn.style.cursor = 'pointer';
    }
    
    document.querySelectorAll('.btn-aksi').forEach(btn => {
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto'; 
    });
}

// Fungsi konfirmasi hapus universal
function konfirmasiHapus(e) {
    // 1. Hentikan paksa aksi default tautan (mencegah langsung terhapus)
    if (e) e.preventDefault();

    // 2. Cek validasi kondisi edit atau tambah manual terlebih dahulu
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_id') || (typeof isAddingManual !== 'undefined' && isAddingManual)) {
        Swal.fire({
            icon: 'error',
            title: 'Selesaikan Apa Yang Anda Mulai',
            text: 'Selesaikan aksi form saat ini terlebih dahulu!',
            confirmButtonColor: '#4F46E5'
        });
        return false; // Berhenti di sini, jangan tampilkan konfirmasi hapus
    }

    // 3. Ambil URL tujuan dari atribut href milik tag <a> yang diklik
    const targetUrl = e.currentTarget.getAttribute('href');

    // 4. Jalankan SweetAlert2 Konfirmasi Hapus
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444', // Warna merah untuk konfirmasi hapus
        cancelButtonColor: '#6B7280',  // Warna abu-abu untuk batal
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        // 5. Jika user mengklik tombol "Ya, Hapus!"
        if (result.isConfirmed) {
            // Arahkan halaman browser ke URL hapus siswa yang disimpan tadi
            window.location.href = targetUrl;
        }
    });
}


function konfirmasiSetuju(e) {
    // 1. Hentikan submit otomatis dari form
    if (e) e.preventDefault();

    // 2. Ambil elemen tombol dan ID form targetnya
    const button = e.currentTarget;
    const formId = button.getAttribute('form');
    const targetForm = document.getElementById(formId);

    // 3. Tampilkan SweetAlert2 Konfirmasi
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan menyetujui data ini.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981', // Warna hijau untuk persetujuan
        cancelButtonColor: '#6B7280',  // Warna abu-abu untuk batal
        confirmButtonText: 'Ya, Terima!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        // 4. Jika user mengklik tombol "Ya, Terima!"
        if (result.isConfirmed && targetForm) {
            
            // TRIK: Buat input hidden darurat agar $_POST['simpan_validasi'] di PHP tetap terbaca
            if (!targetForm.querySelector('input[name="simpan_validasi"]')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'simpan_validasi';
                hiddenInput.value = 'true';
                targetForm.appendChild(hiddenInput);
            }

            // 5. Jalankan submit form secara manual melalui JS
            targetForm.submit();
        }
    });
}



document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg) {
        // DAFTAR PESAN (Dictionary) - Semuanya berpusat di sini
        const alertList = {
            'success_profil': { icon: 'success', title: 'Berhasil!', text: 'Profil Anda berhasil diperbarui.' },
            'added': { icon: 'success', title: 'Berhasil!', text: 'Data baru berhasil ditambahkan.' },
            'updated': { icon: 'success', title: 'Berhasil!', text: 'Perubahan data berhasil disimpan.' },
            'deleted': { icon: 'success', title: 'Terhapus!', text: 'Data telah berhasil dihapus dari sistem.' },

            'added_kelas': { icon: 'success', title: 'Berhasil!', text: 'Kelas berhasil ditambahkan.' },
            'updated_kelas': { icon: 'success', title: 'Berhasil!', text: 'Data Kelas berhasil diperbarui.' },
            'deleted_kelas': { icon: 'success', title: 'Terhapus!', text: 'Kelas berhasil dihapus.' },

            'added_murid': { icon: 'success', title: 'Berhasil!', text: 'Murid berhasil ditambahkan.' },
            'updated_murid': { icon: 'success', title: 'Berhasil!', text: 'Data Murid berhasil diperbarui.' },
            'deleted_murid': { icon: 'success', title: 'Terhapus!', text: 'Murid berhasil dihapus.' },

            'added_mapel': { icon: 'success', title: 'Berhasil!', text: 'Mata Pelajaran berhasil ditambahkan.' },
            'updated_mapel': { icon: 'success', title: 'Berhasil!', text: 'Data Mata Pelajaran berhasil diperbarui.' },
            'deleted_mapel': { icon: 'success', title: 'Terhapus!', text: 'Mata Pelajaran berhasil dihapus.' },

            'added_jampel': { icon: 'success', title: 'Berhasil!', text: 'Jadwal Pelajaran berhasil ditambahkan.' },
            'updated_jampel': { icon: 'success', title: 'Berhasil!', text: 'Data Jadwal Pelajaran berhasil diperbarui.' },
            'deleted_jampel': { icon: 'success', title: 'Terhapus!', text: 'Jadwal Pelajaran berhasil dihapus.' },
            
            // Notif Khusus Guru
            'approved': { icon: 'success', title: 'Diterima!', text: 'Akun guru berhasil divalidasi dan diaktifkan.' },
            'rejected': { icon: 'success', title: 'Ditolak!', text: 'Akun guru berhasil ditolak dan dihapus.' },
            'deleted_guru': { icon: 'success', title: 'Terhapus!', text: 'Data Guru dan Akun Pengguna berhasil dihapus permanen.' },
            
            // Error Handling
            'duplicate': { icon: 'warning', title: 'Data Kembar!', text: 'Data yang Anda masukkan sudah ada di sistem (Duplikat).' },
            'conflict': { icon: 'error', title: 'Jadwal Bentrok!', text: 'Jadwal tumpang tindih untuk Kelas atau Guru pada hari dan jam yang sama.' },
            'error_system': { icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan sistem, data gagal diproses.' },
            'not_found': { icon: 'error', title: 'Oops!', text: 'Data yang Anda cari tidak ditemukan.' },

            // Validasi Input
            'error_phone': { icon: 'error', title: 'Gagal!', text: 'Nomor Telepon harus berjumlah antara 11 sampai 13 digit angka!' },
            'error_nip': { icon: 'error', title: 'Gagal!', text: 'NIP harus berupa angka dan panjang tepat 18 karakter!' },
            'error_password': { icon: 'error', title: 'Gagal!', text: 'Password harus mengandung kombinasi huruf besar, huruf kecil, dan angka!' }
        };

        if (alertList[msg]) {
            Swal.fire({
                icon: alertList[msg].icon,
                title: alertList[msg].title,
                text: alertList[msg].text,
                confirmButtonColor: '#4F46E5'
            });

            // Bersihkan parameter msg dari URL agar tidak berulang saat direfresh
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + (urlParams.get('p') ? '?p=' + urlParams.get('p') : '');
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
    }


    // --- FOTO PROFIL LOGIC ---
    const inputFotoProfil = document.getElementById('inputFotoProfil');
    const previewImg = document.getElementById('preview');
    const displayImg = document.getElementById('display-foto');

    if(inputFotoProfil) {
        inputFotoProfil.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Terlalu Besar',
                        text: 'Ukuran gambar terlalu besar! Maksimal 2MB.',
                        confirmButtonColor: '#4F46E5'
                    });
                    this.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(e) {
                    if(previewImg) previewImg.src = e.target.result;
                    if(displayImg) displayImg.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // --- PROFIL PASSWORD LOGIC ---
    const formProfil = document.getElementById('formProfil');
    const passwordInput = document.getElementById('passwordProfil');

    if (formProfil && passwordInput) {
        formProfil.addEventListener('submit', function (e) {
            const password = passwordInput.value;
            if (password.length > 0) {
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
                if (!passwordPattern.test(password)) {
                    e.preventDefault(); 
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Terlalu Lemah',
                        text: 'Password harus mengandung kombinasi huruf besar, huruf kecil, dan angka!',
                        confirmButtonColor: '#4F46E5'
                    });
                    passwordInput.focus();
                    return;
                }
            }
        });
    }

    // --- FORM DETAIL GURU LOGIC (REPLACED ALERT TO SWAL) ---
    const formDetailGuru = document.getElementById('formDetailGuru');

    if (formDetailGuru) {
        const phoneInput = formDetailGuru.querySelector('input[name="no_telp"]');
        const nipInput = formDetailGuru.querySelector('input[name="nip"]');
        const passDetailInput = formDetailGuru.querySelector('input[name="password"]');

        if (phoneInput) {
            phoneInput.addEventListener('keypress', function (e) {
                if (e.which < 48 || e.which > 57) e.preventDefault();
            });
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        if (nipInput) {
            nipInput.addEventListener('keypress', function (e) {
                if (e.which < 48 || e.which > 57) e.preventDefault();
            });
            nipInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        formDetailGuru.addEventListener('submit', function (e) {
            const phone = phoneInput ? phoneInput.value : '';
            const nip = nipInput ? nipInput.value : '';
            const password = passDetailInput ? passDetailInput.value : '';

            if (phone.length < 11 || phone.length > 13) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Gagal', text: 'Nomor Telepon harus berjumlah antara 11 sampai 13 digit angka!', confirmButtonColor: '#4F46E5' });
                phoneInput.focus();
                return;
            }

            if (nip.length > 0 && nip.length !== 18) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Gagal', text: 'NIP harus berupa angka dan panjang tepat 18 karakter!', confirmButtonColor: '#4F46E5' });
                nipInput.focus();
                return;
            }

            if (password.length > 0) {
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
                if (!passwordPattern.test(password) && password.length < 6) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Gagal', text: 'Panjang password minimal 6 karakter dan harus mengandung kombinasi huruf besar, huruf kecil, dan angka!', confirmButtonColor: '#4F46E5' });
                    passDetailInput.focus();
                    return;
                }
            }
        });
    }






    
    checkGlobalLock();
    

    // ── Toggle Panel Guru / Validasi ─────────────────────────
    // Ambil elemen yang dibutuhkan
    const btnSurat    = document.getElementById('btn-surat');
    const btnBack     = document.getElementById('btn-back-guru');
    const panelGuru   = document.getElementById('panel-guru');
    const panelValidasi = document.getElementById('panel-validasi');
    const mainHeaderTitle = document.getElementById('main-header-title');
    const mainHeaderDesc = document.getElementById('main-header-desc');

    if (btnSurat && panelGuru && panelValidasi) {

        // Jika URL mengandung ?panel=validasi (misalnya setelah redirect aksi terima/tolak),
        // langsung tampilkan panel validasi tanpa klik tombol
        const params = new URLSearchParams(window.location.search);
        if (params.get('panel') === 'validasi') {
            panelGuru.style.display   = 'none';
            panelValidasi.style.display = 'block';
            mainHeaderTitle.innerText = "Persetujuan Akun Guru";
            mainHeaderDesc.innerText = "Verifikasi data pendaftaran pengajar baru.";
        }

        // Klik ikon surat → tampilkan panel validasi
        btnSurat.addEventListener('click', function () {
            panelGuru.style.display     = 'none';
            panelValidasi.style.display = 'block';
            mainHeaderTitle.innerText = "Persetujuan Akun Guru";
            mainHeaderDesc.innerText = "Verifikasi data pendaftaran pengajar baru.";
        });

        // Klik tombol kembali → tampilkan kembali panel guru
        if (btnBack) {
            btnBack.addEventListener('click', function () {
                panelValidasi.style.display = 'none';
                panelGuru.style.display     = 'block';
                mainHeaderTitle.innerText = "Manajemen Guru";
                mainHeaderDesc.innerText = "Kelola Profil Pengajar Aktif.";
            });
        }
    }


    const filterKelas = document.getElementById('filterKelas');
    const tbodySiswa = document.getElementById('tbodySiswa');
    const paginationContainer = document.getElementById('paginationContainer');
    const emptyRow = document.getElementById('emptyRow');
    
    //Filter Murid
    if (filterKelas && tbodySiswa) {
        // Ambil SEMUA baris asli (kecuali baris pesan kosong)
        const allRows = Array.from(document.querySelectorAll('.siswa-row'));
        const rowsPerPage = 10;
        let currentPage = 1;

        function renderTable() {
            const selectedKelas = filterKelas.value;

            // 1. Filter data berdasarkan kelas
            const filteredRows = allRows.filter(row => {
                return selectedKelas === 'all' || row.getAttribute('data-kelas') === selectedKelas;
            });
            
            // 2. Sembunyikan SEMUA baris terlebih dahulu
            allRows.forEach(row => row.style.display = 'none');
            if(emptyRow) emptyRow.style.display = 'none';
            // 3. Jika tidak ada hasil filter, munculkan baris "Kosong"
            if (filteredRows.length === 0) {
                if(emptyRow) emptyRow.style.display = 'table-row';
                if(paginationContainer) paginationContainer.style.display = 'none';
                return;
            }
            // 4. Hitung Pagination
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage > totalPages) currentPage = 1;
            // 5. Tampilkan data sesuai halaman aktif
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            filteredRows.slice(start, end).forEach(row => {
                row.style.display = 'table-row';
            });
            // 6. Gambar ulang tombol penomoran
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if(!paginationContainer) return;
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
                    window.scrollTo(0, 0); // Scroll ke atas saat ganti halaman
                };
                paginationContainer.appendChild(btn);
            }
        }
        filterKelas.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });
        renderTable(); // Jalankan pertama kali
    }









    const selectGuru = document.getElementById('id_guru');
    const selectKelas = document.getElementById('id_kelas');
    const selectMulai = document.getElementById('jam_mulai');
    const selectSelesai = document.getElementById('jam_selesai');
    const inputMapelHidden = document.getElementById('id_mapel_hidden');

    if (selectGuru && !document.getElementById('areaTambahGlobal').style.pointerEvents) {
        selectGuru.addEventListener('change', function () {
            const mapelId = this.options[this.selectedIndex].getAttribute('data-mapel-id');
            inputMapelHidden.value = mapelId || "";
            selectKelas.value = "";
            selectKelas.disabled = !this.value;

            document.querySelectorAll('.opt-kelas').forEach(opt => {
                opt.style.display = opt.classList.contains('guru-' + this.value) ? 'block' : 'none';
                opt.disabled = !opt.classList.contains('guru-' + this.value);
            });
            document.querySelectorAll('.opt-kelas').forEach(opt => {
                if(opt.getAttribute('data-bentrok') === 'true') {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            });
            if(this.value) selectKelas.options[0].text = "- Pilih Kelas -";
        });
        
        selectMulai.addEventListener('change', function() {
            const jpMulai = parseInt(this.options[this.selectedIndex].getAttribute('data-jp'));
            selectSelesai.disabled = !this.value;
            selectSelesai.value = "";
            
            Array.from(selectSelesai.options).forEach(opt => {
                const jpSelesai = parseInt(opt.getAttribute('data-jp'));
                opt.style.display = (jpSelesai && jpSelesai < jpMulai) ? 'none' : 'block';
            });
        });
    }

    const editGuru = document.getElementById('edit_id_guru');
    const editKelas = document.getElementById('edit_id_kelas');
    const editMulai = document.getElementById('edit_jam_mulai');
    const editSelesai = document.getElementById('edit_jam_selesai');
    const editMapelHidden = document.getElementById('edit_id_mapel_hidden');

    if (editGuru) {
        // Fungsi untuk filter kelas di Edit Inline
        function filterEditKelas(guruId) {
            let hasClass = false;
            document.querySelectorAll('.edit-opt-kelas').forEach(opt => {
                if (opt.classList.contains('edit-guru-' + guruId)) {
                    opt.hidden = false;
                    opt.disabled = false;
                    hasClass = true;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            document.querySelectorAll('.edit-opt-kelas').forEach(opt => {
                if(opt.getAttribute('data-bentrok') === 'true') {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            if(!hasClass && guruId) {
                editKelas.options[0] = new Option("- Tidak ada kelas -", "");
            }
        }

        // Jalankan saat pertama load (Agar kelas terpilih saat ini muncul)
        const initialMapelId = editGuru.options[editGuru.selectedIndex].getAttribute('data-mapel-id');
        
        editMapelHidden.value = initialMapelId;
        filterEditKelas(editGuru.value);

        // Jalankan saat Guru diganti di baris edit
        editGuru.addEventListener('change', function () {
            editMapelHidden.value = this.options[this.selectedIndex].getAttribute('data-mapel-id') || "";
            filterEditKelas(this.value);
            editKelas.value = ""; // Reset kelas
        });

        // Logika jam edit
        editMulai.addEventListener('change', function() {
            const jpMulai = parseInt(this.options[this.selectedIndex].getAttribute('data-jp'));
            Array.from(editSelesai.options).forEach(opt => {
                const jpSelesai = parseInt(opt.getAttribute('data-jp'));
                opt.hidden = (jpSelesai && jpSelesai < jpMulai);
                opt.disabled = (jpSelesai && jpSelesai < jpMulai);
            });
            if(parseInt(editSelesai.options[editSelesai.selectedIndex].getAttribute('data-jp')) < jpMulai) {
                editSelesai.value = ""; // Reset jika jam selesai tidak valid
            }
        });
        // Trigger manual untuk setup awal batas jam selesai
        editMulai.dispatchEvent(new Event('change'));
    }


    function checkAvailability(prefix = '') {
        const selectHari = document.querySelector(`select[name="hari"]${prefix ? '#' + prefix + 'hari' : ':not([id^="edit_"])'}`);
        // Perbaiki pemanggilan id dengan prefix (jika form edit punya awalan id)
        const hariId = prefix === 'edit_' ? 'edit_hari' : 'hari'; // Misal jika Anda menambah ID ke <select name="hari">
        const selectHariNode = prefix === 'edit_' ? document.querySelector('select[name="hari"]') : document.querySelector('select[name="hari"]:not([id])');
        
        // Kita tangkap select element (karena form Anda tidak memiliki ID pada select 'hari')
        const hariVal = prefix === 'edit_' ? document.querySelector('.table-container select[name="hari"]')?.value : document.querySelector('#formJadwal select[name="hari"]')?.value;
        const jamMulai = document.getElementById(prefix + 'jam_mulai')?.value;
        const jamSelesai = document.getElementById(prefix + 'jam_selesai')?.value;
        const selectGuru = document.getElementById(prefix + 'id_guru');
        const selectKelas = document.getElementById(prefix + 'id_kelas');
        const currentEditId = prefix === 'edit_' ? document.querySelector('input[name="id_edit"]').value : null;

        if (!hariVal || !jamMulai || !jamSelesai || !selectGuru || !selectKelas) return;

        let busyGurus = [];
        let busyKelas = [];

        // Evaluasi jadwal yang tumpang tindih
        if (typeof dataJadwalGlobal !== 'undefined') {
            dataJadwalGlobal.forEach(sch => {
                // Abaikan jadwal yang sedang di-edit itu sendiri
                if (currentEditId && sch.id === currentEditId) return;

                if (sch.hari === hariVal) {
                    // POTONG DETIK DARI DATABASE ("09:15:00" -> "09:15")
                    const dbJamMulai = sch.jam_mulai.substring(0, 5);
                    const dbJamSelesai = sch.jam_selesai.substring(0, 5);

                    // Logika Overlap Jam: StartBaru < EndLama && EndBaru > StartLama
                    if (jamMulai < dbJamSelesai && jamSelesai > dbJamMulai) {
                        busyGurus.push(sch.id_guru);
                        busyKelas.push(sch.id_kelas);
                    }
                }
            });
        }

        // 1. Filter Dropdown Guru
        Array.from(selectGuru.options).forEach(opt => {
            if (opt.value === "") return; // Abaikan option placeholder
            if (busyGurus.includes(opt.value)) {
                opt.disabled = true;
                opt.hidden = true;
                if (selectGuru.value === opt.value) selectGuru.value = ""; // Reset jika yang dipilih ternyata sibuk
            } else {
                opt.disabled = false;
                opt.hidden = false;
            }
        });

        // 2. Filter Dropdown Kelas
        // Catatan: Karena Kelas juga di-filter berdasarkan Guru, kita tambahkan data class (sibuk) 
        Array.from(selectKelas.options).forEach(opt => {
            if (opt.value === "") return;
            // Cek apakah kelas ini bentrok
            const isBentrok = busyKelas.includes(opt.value);
            
            if (isBentrok) {
                opt.disabled = true;
                opt.hidden = true;
                opt.setAttribute('data-bentrok', 'true');
                if (selectKelas.value === opt.value) selectKelas.value = "";
            } else {
                opt.removeAttribute('data-bentrok');
            }
        });
        
        // Memaksa trigger fungsi filter kelas bawaan Anda agar berjalan ulang 
        // dengan data bentrok yang sudah di-update
        if(selectGuru.value) {
            selectGuru.dispatchEvent(new Event('change'));
        }
    }

    // Pasang Event Listeners untuk form TAMBAH
    const formTambahHari = document.querySelector('#formJadwal select[name="hari"]');
    const formTambahMulai = document.getElementById('jam_mulai');
    const formTambahSelesai = document.getElementById('jam_selesai');

    if (formTambahHari) formTambahHari.addEventListener('change', () => checkAvailability(''));
    if (formTambahMulai) formTambahMulai.addEventListener('change', () => { setTimeout(() => checkAvailability(''), 50); });
    if (formTambahSelesai) formTambahSelesai.addEventListener('change', () => checkAvailability(''));

    // Pasang Event Listeners untuk form EDIT (Jika sedang mode edit)
    const formEditHari = document.querySelector('.table-container select[name="hari"]');
    const formEditMulai = document.getElementById('edit_jam_mulai');
    const formEditSelesai = document.getElementById('edit_jam_selesai');

    if (formEditHari) formEditHari.addEventListener('change', () => checkAvailability('edit_'));
    if (formEditMulai) formEditMulai.addEventListener('change', () => { setTimeout(() => checkAvailability('edit_'), 50); });
    if (formEditSelesai) formEditSelesai.addEventListener('change', () => checkAvailability('edit_'));
    
    
    // --- Modifikasi Sedikit Event Guru Change Milik Anda ---
    // Di dalam JS Anda sebelumnya, Anda memiliki event listener untuk Guru ('id_guru' dan 'edit_id_guru').
    // Kita pastikan saat Guru diganti, kelas yang "Bentrok" tetap disembunyikan.
    


    const jadwalRows = document.querySelectorAll('.jadwal-row');

    function applyTableFilters() {
        if (!filterHari || !filterKelasJadwal || !filterGuruJadwal) return;

        const valHari = filterHari.value;
        const valKelas = filterKelasJadwal.value;
        const valGuru = filterGuruJadwal.value;

        jadwalRows.forEach(row => {
            const matchHari = (valHari === 'all') || (row.getAttribute('data-hari') === valHari);
            const matchKelas = (valKelas === 'all') || (row.getAttribute('data-kelas') === valKelas);
            const matchGuru = (valGuru === 'all') || (row.getAttribute('data-guru') === valGuru);

            // Jika baris cocok dengan KETIGA filter, tampilkan. Jika tidak, sembunyikan.
            if (matchHari && matchKelas && matchGuru) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Pasang listener pada setiap dropdown filter
    if (filterHari) filterHari.addEventListener('change', applyTableFilters);
    if (filterKelasJadwal) filterKelasJadwal.addEventListener('change', applyTableFilters);
    if (filterGuruJadwal) filterGuruJadwal.addEventListener('change', applyTableFilters);

    // Pasang listener untuk tombol Reset
    if (btnResetFilter) {
        btnResetFilter.addEventListener('click', () => {
            filterHari.value = 'all';
            filterKelasJadwal.value = 'all';
            filterGuruJadwal.value = 'all';
            applyTableFilters(); // Jalankan ulang filter untuk menampilkan semua data
        });
    }




    const allJadwalRows = Array.from(document.querySelectorAll('.jadwal-row'));
    const paginationJadwal = document.getElementById('paginationJadwal');
    const tbodyJadwal = document.querySelector('.table-container tbody');

    // Buat baris "Kosong" dinamis jika filter tidak menemukan data
    let emptyRowJadwal = document.getElementById('emptyRowJadwal');
    if (!emptyRowJadwal && tbodyJadwal) {
        emptyRowJadwal = document.createElement('tr');
        emptyRowJadwal.id = 'emptyRowJadwal';
        emptyRowJadwal.style.display = 'none';
        emptyRowJadwal.innerHTML = '<td colspan="5" style="text-align:center; padding:15px;">Tidak ada jadwal untuk kriteria tersebut.</td>';
        tbodyJadwal.appendChild(emptyRowJadwal);
    }

    const rowsPerPageJadwal = 10;
    let currentJadwalPage = 1;

    function applyTableFiltersAndPagination() {
        if (!filterHari || !filterKelasJadwal || !filterGuruJadwal) return;

        const valHari = filterHari.value;
        const valKelas = filterKelasJadwal.value;
        const valGuru = filterGuruJadwal.value;

        // 1. Filter data berdasarkan ke-3 kriteria
        const filteredRows = allJadwalRows.filter(row => {
            const matchHari = (valHari === 'all') || (row.getAttribute('data-hari') === valHari);
            const matchKelas = (valKelas === 'all') || (row.getAttribute('data-kelas') === valKelas);
            const matchGuru = (valGuru === 'all') || (row.getAttribute('data-guru') === valGuru);
            return matchHari && matchKelas && matchGuru;
        });

        // 2. Sembunyikan SEMUA baris terlebih dahulu
        allJadwalRows.forEach(row => row.style.display = 'none');
        if (emptyRowJadwal) emptyRowJadwal.style.display = 'none';

        // 3. Jika tidak ada hasil filter, munculkan teks kosong
        if (filteredRows.length === 0) {
            if (emptyRowJadwal) emptyRowJadwal.style.display = 'table-row';
            if (paginationJadwal) paginationJadwal.style.display = 'none';
            return;
        }

        // 4. Hitung Pagination
        const totalPages = Math.ceil(filteredRows.length / rowsPerPageJadwal);
        if (currentJadwalPage > totalPages) currentJadwalPage = 1;

        // 5. Tampilkan data sesuai halaman aktif
        const start = (currentJadwalPage - 1) * rowsPerPageJadwal;
        const end = start + rowsPerPageJadwal;
        filteredRows.slice(start, end).forEach(row => {
            row.style.display = 'table-row'; 
        });

        // 6. Gambar ulang tombol paging
        renderPaginationJadwal(totalPages);
    }

    function renderPaginationJadwal(totalPages) {
        if (!paginationJadwal) return;
        paginationJadwal.innerHTML = '';
        
        if (totalPages <= 1) {
            paginationJadwal.style.display = 'none';
            return;
        }
        
        paginationJadwal.style.display = 'flex';
        
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button'; 
            btn.innerText = i;
            
            // PENGGABUNGAN CSS TERJADI DI SINI:
            // Menggunakan class yang sama persis dengan halaman murid
            btn.className = `page-btn ${i === currentJadwalPage ? 'active' : ''}`;

            btn.onclick = (e) => {
                e.preventDefault();
                currentJadwalPage = i;
                applyTableFiltersAndPagination();
                window.scrollTo(0, 0); // Efek scroll ke atas yang sama
            };
            
            paginationJadwal.appendChild(btn);
        }
    }

    // Trigger saat filter diganti
    const handleFilterChange = () => {
        currentJadwalPage = 1;
        applyTableFiltersAndPagination();
    };

    if (filterHari) filterHari.addEventListener('change', handleFilterChange);
    if (filterKelasJadwal) filterKelasJadwal.addEventListener('change', handleFilterChange);
    if (filterGuruJadwal) filterGuruJadwal.addEventListener('change', handleFilterChange);

    if (btnResetFilter) {
        btnResetFilter.onclick = () => {
            filterHari.value = 'all';
            filterKelasJadwal.value = 'all';
            filterGuruJadwal.value = 'all';
            currentJadwalPage = 1;
            applyTableFiltersAndPagination(); 
        };
    }

    // Jalankan pertama kali
    applyTableFiltersAndPagination();




    // Cetak Jadwal
    const btnCetakPDF = document.getElementById('btnCetakPDF');
    
    if (btnCetakPDF) {
        btnCetakPDF.addEventListener('click', function() {
            // Ambil elemen filter manual jika variabel globalnya tidak ada
            const elFilterHari = document.getElementById('filterHari');
            const elFilterKelas = document.getElementById('filterKelasJadwal');
            const elFilterGuru = document.getElementById('filterGuruJadwal');

            const valHari = elFilterHari ? elFilterHari.value : 'all';
            const valKelas = elFilterKelas ? elFilterKelas.value : 'all';
            const valGuru = elFilterGuru ? elFilterGuru.value : 'all';

            // 1. Rangkai Judul Secara Dinamis
            let judul = "";
            
            if (valHari === 'all' && valKelas === 'all' && valGuru === 'all') {
                judul = "Semua Jadwal Pelajaran";
            } else {
                // Cek apakah guru dipilih untuk merubah awalan judul
                if (valGuru !== 'all') {
                    judul = "Jadwal Mengajar " + valGuru;
                } else {
                    judul = "Jadwal Pelajaran";
                }

                // Tambahkan filter kelas dan hari jika dipilih
                if (valKelas !== 'all') judul += " Kelas " + valKelas;
                if (valHari !== 'all') judul += " Hari " + valHari;
            }

            // 2. Siapkan Area Print
            const printArea = document.getElementById('printArea');
            printArea.innerHTML = ''; // Bersihkan sisa sebelumnya
            printArea.style.display = 'block';

            // Masukkan Judul
            const h2 = document.createElement('h2');
            h2.innerText = judul;
            printArea.appendChild(h2);

            // 3. Clone Tabel Asli
            const tableAsli = document.querySelector('.table-container table');
            const tableClone = document.createElement('table');

            // Clone THEAD dan hapus kolom "Aksi"
            const theadClone = tableAsli.querySelector('thead').cloneNode(true);
            theadClone.querySelector('tr').lastElementChild.remove();
            tableClone.appendChild(theadClone);

            // Clone TBODY (Hanya baris yang terlihat/bukan display none)
            const tbodyClone = document.createElement('tbody');
            const rowsAsli = tableAsli.querySelectorAll('tbody tr.jadwal-row'); // Hanya ambil baris jadwal normal (abaikan mode edit)
            
            let countVisible = 0;
            rowsAsli.forEach(row => {
                if (row.style.display !== 'none') {
                    const rowClone = row.cloneNode(true);
                    rowClone.lastElementChild.remove(); // Hapus sel/kolom Aksi terakhir
                    tbodyClone.appendChild(rowClone);
                    countVisible++;
                }
            });

            // Handle tabel kosong jika filter tidak menghasilkan apa-apa
            if (countVisible === 0) {
                const trEmpty = document.createElement('tr');
                const tdEmpty = document.createElement('td');
                tdEmpty.colSpan = 4;
                tdEmpty.style.textAlign = 'center';
                tdEmpty.innerText = "Tidak ada jadwal untuk kriteria tersebut.";
                trEmpty.appendChild(tdEmpty);
                tbodyClone.appendChild(trEmpty);
            }

            tableClone.appendChild(tbodyClone);
            printArea.appendChild(tableClone);

            // 4. Panggil Window Print
            window.print();

            // 5. Kembalikan kondisi semula setelah dialog print selesai/ditutup
            printArea.style.display = 'none';
        });
    }



});
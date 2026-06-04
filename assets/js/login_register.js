document.addEventListener('DOMContentLoaded', function () {
    
    const primaryColor = '#4F46E5';

    // ==========================================
    // 1. LOGIKA ANIMASI TOGGLE (LOGIN & REGISTER)
    // ==========================================
    const container = document.querySelector('.container');
    const registerBtn = document.querySelector('.register-btn');
    const loginBtn = document.querySelector('.login-btn');

    if (registerBtn && loginBtn && container) {
        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    }

    // ==========================================
    // 2. LOGIKA VALIDASI INPUT & FORM
    // ==========================================
    const phoneInput = document.querySelector('input[name="no_telp"]');
    const nipInput = document.querySelector('input[name="nip"]');

    // Blokir karakter selain angka saat mengetik
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

    // Validasi Sisi Frontend saat menekan tombol Register
    const registerForm = document.querySelector('form[action*="action=register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const phone = phoneInput ? phoneInput.value : '';
            const nip = nipInput ? nipInput.value : '';
            const passwordInput = document.querySelector('.register input[name="password"]');
            const password = passwordInput ? passwordInput.value : '';

            // Validasi Nomor Telepon
            if (phone.length < 11 || phone.length > 13) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Format Salah',
                    text: 'Nomor Telepon harus berjumlah antara 11 sampai 13 digit angka!',
                    confirmButtonColor: primaryColor
                });
                phoneInput.focus();
                return;
            }

            // Validasi NIP (Opsional, jika diisi wajib 18 digit)
            if (nip.length > 0 && nip.length !== 18) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Format Salah',
                    text: 'NIP harus berupa angka dan panjang tepat 18 karakter!',
                    confirmButtonColor: primaryColor
                });
                nipInput.focus();
                return;
            }

            // Validasi Format dan Panjang Password
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
            if (password.length < 6 || !passwordPattern.test(password)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Sandi Kurang Kuat',
                    text: 'Panjang password minimal 6 karakter dan wajib mengandung kombinasi huruf besar, huruf kecil, serta angka!',
                    confirmButtonColor: primaryColor
                });
                passwordInput.focus();
                return;
            }
        });
    }

    // ==========================================
    // 3. LOGIKA FITUR INTIP PASSWORD (SHOW/HIDE)
    // ==========================================
    const passwordInputs = document.querySelectorAll('input[name="password"]');

    passwordInputs.forEach(input => {
        const icon = input.nextElementSibling;

        if (icon && icon.classList.contains('bx')) {
            icon.style.cursor = 'pointer';

            icon.addEventListener('click', function () {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bxs-lock-alt');
                    icon.classList.add('bxs-lock-open-alt');
                    icon.style.color = '#4F46E5';
                } else {
                    input.type = 'password';
                    icon.classList.remove('bxs-lock-open-alt');
                    icon.classList.add('bxs-lock-alt');
                    icon.style.color = '#777';
                }
            });
        }
    });
});
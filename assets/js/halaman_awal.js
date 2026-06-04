/* ============================================================
   HALAMAN AWAL JS
   Landing Page, Papan Tulis Jadwal, Carousel Kelas, Game
   ============================================================ */

let activeClassId = null;

/* ============================================================
   LANDING PAGE - Papan Tulis Interaktif
   ============================================================ */

function openBoard(idKelas, namaKelas) {
    const overlay = document.getElementById("blackboardOverlay");
    const content = document.getElementById("blackboardContent");
    const title = document.getElementById("boardTitle");

    if (!overlay || !content || !title) return;

    activeClassId = idKelas;
    title.innerText = "📅 Jadwal Kelas " + namaKelas;

    overlay.style.display = "flex";
    content.classList.remove("board-shrink");
    content.classList.add("board-expand");

    loadDay("Senin");
}

function closeBoard() {
    const overlay = document.getElementById("blackboardOverlay");
    const content = document.getElementById("blackboardContent");

    if (!overlay || !content) return;

    content.classList.remove("board-expand");
    content.classList.add("board-shrink");

    setTimeout(() => {
        overlay.style.display = "none";
        activeClassId = null;
    }, 300);
}

function loadDay(hari) {
    if (!activeClassId) return;

    const tableBody = document.getElementById("boardBody");
    const tabs = document.querySelectorAll(".day-tab");

    if (!tableBody) return;

    tabs.forEach((tab) => {
        if (tab.getAttribute("data-hari") === hari) {
            tab.classList.add("active");
        } else {
            tab.classList.remove("active");
        }
    });

    tableBody.innerHTML = `
        <div style="text-align:center; padding:40px; color:#fff;">
            <div class="loader" style="border-top-color:#fff; margin:0 auto;"></div>
            <p style="margin-top:20px; font-family:'Comic Sans MS', cursive;">Menyalin jadwal ke papan...</p>
        </div>
    `;

    fetch(`Halaman_Awal.php?p=get_jadwal&kelas_id=${encodeURIComponent(activeClassId)}&hari=${encodeURIComponent(hari)}`)
        .then((response) => {
            if (!response.ok) throw new Error("Gagal terhubung ke server");
            return response.text();
        })
        .then((data) => {
            tableBody.innerHTML = data;
        })
        .catch(() => {
            tableBody.innerHTML = `
                <div style="text-align:center; padding:40px; color:#fca5a5;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size:30px;"></i>
                    <p style="font-weight:700; margin-top:10px; font-family:'Comic Sans MS', cursive;">Kapur Patah! Gagal memuat data.</p>
                </div>
            `;
        });
}

/* ============================================================
   CAROUSEL KELAS
   ============================================================ */

function scrollKelas(direction) {
    const slider = document.getElementById("classSlider");

    if (!slider) {
        console.log("classSlider tidak ditemukan");
        return;
    }

    slider.scrollBy({
        left: direction * 300,
        behavior: "smooth"
    });
}

function initClassCarousel() {
    const grid = document.getElementById("classSlider");
    if (!grid) return;

    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    grid.addEventListener("mousedown", (e) => {
        isDown = true;
        grid.classList.add("dragging");
        startX = e.pageX - grid.offsetLeft;
        scrollLeft = grid.scrollLeft;
    });

    grid.addEventListener("mouseleave", () => {
        isDown = false;
        grid.classList.remove("dragging");
    });

    grid.addEventListener("mouseup", () => {
        isDown = false;
        grid.classList.remove("dragging");
    });

    grid.addEventListener("mousemove", (e) => {
        if (!isDown) return;

        e.preventDefault();
        const x = e.pageX - grid.offsetLeft;
        const walk = (x - startX) * 1.5;
        grid.scrollLeft = scrollLeft - walk;
    });
}

/* ============================================================
   GAME PAGE - Tic Tac Toe
   ============================================================ */

function initGame() {
    const boardElement = document.getElementById("board");
    if (!boardElement) return;

    let board = ["", "", "", "", "", "", "", "", ""];
    let gameActive = true;

    const statusText = document.getElementById("statusText");
    const cells = document.querySelectorAll(".cell");

    const winPatterns = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8],
        [0, 4, 8],
        [2, 4, 6]
    ];

    function updateCell(cell, index, player) {
        board[index] = player;
        cell.innerText = player;
        cell.classList.add(player.toLowerCase(), "taken");
    }

    function checkWin(player) {
        return winPatterns.some((pattern) => {
            return pattern.every((index) => board[index] === player);
        });
    }

    function endGame(message) {
        if (statusText) statusText.innerText = message;
        gameActive = false;
    }

    function smartMove() {
        for (const pattern of winPatterns) {
            const values = pattern.map((index) => board[index]);

            if (
                values.filter((value) => value === "O").length === 2 &&
                values.filter((value) => value === "").length === 1
            ) {
                return pattern[values.indexOf("")];
            }
        }

        for (const pattern of winPatterns) {
            const values = pattern.map((index) => board[index]);

            if (
                values.filter((value) => value === "X").length === 2 &&
                values.filter((value) => value === "").length === 1
            ) {
                return pattern[values.indexOf("")];
            }
        }

        return null;
    }

    function robotMove() {
        if (gameActive || !board.includes("")) return;

        const emptyCells = board
            .map((value, index) => (value === "" ? index : null))
            .filter((value) => value !== null);

        const move = smartMove() ?? emptyCells[Math.floor(Math.random() * emptyCells.length)];
        const targetCell = document.querySelector(`[data-index="${move}"]`);

        if (!targetCell) return;

        updateCell(targetCell, move, "O");

        if (checkWin("O")) {
            endGame("Robot Menang! 🤖");
        } else if (board.every((cell) => cell !== "")) {
            endGame("Seri! 🤝");
        } else {
            gameActive = true;
            if (statusText) statusText.innerText = "Giliran Kamu (X)";
        }
    }

    window.userMove = function (cell, index) {
        if (board[index] !== "" || !gameActive) return;

        updateCell(cell, index, "X");

        if (checkWin("X")) {
            endGame("Kamu Menang! 🎉");
        } else if (board.every((item) => item !== "")) {
            endGame("Seri! 🤝");
        } else {
            gameActive = false;
            if (statusText) statusText.innerText = "Robot sedang mikir...";
            setTimeout(robotMove, 600);
        }
    };

    window.resetGame = function () {
        board = ["", "", "", "", "", "", "", "", ""];
        gameActive = true;

        if (statusText) statusText.innerText = "Giliran Kamu (X)";

        cells.forEach((cell) => {
            cell.innerText = "";
            cell.classList.remove("x", "o", "taken");
        });
    };
}

/* ============================================================
   INIT
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
    initClassCarousel();
    initGame();
});
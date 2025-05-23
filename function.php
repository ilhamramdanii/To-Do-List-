<?php
// Inisialisasi array 'tasks' dalam sesi jika belum ada
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Fungsi untuk menambahkan tugas baru
function tambahTugas($text, $deadline) {
    $_SESSION['tasks'][] = [
        'text' => trim($text),     // Hapus spasi di awal dan akhir teks
        'deadline' => $deadline,   // Tanggal deadline tugas
        'done' => false            // Status awal: belum selesai
    ];
}

// Fungsi untuk mengubah status 'done' dari tugas (selesai <-> belum)
function toggleTugas($index) {
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['done'] = !$_SESSION['tasks'][$index]['done'];
    }
}

// Fungsi untuk menghapus tugas berdasarkan index
function hapusTugas($index) {
    if (isset($_SESSION['tasks'][$index])) {
        unset($_SESSION['tasks'][$index]); // Hapus tugas
        $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Reset index array
    }
}

// Fungsi untuk mengedit teks dan deadline tugas
function editTugas($index, $text, $deadline) {
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['text'] = trim($text);
        $_SESSION['tasks'][$index]['deadline'] = $deadline;
    }
}

// Fungsi untuk memformat tanggal ke format Indonesia
function formatTanggal($datetime) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $tanggal = date('d', strtotime($datetime));
    $bulanNum = date('m', strtotime($datetime));
    $tahun = date('Y', strtotime($datetime));
    $jam = date('H:i', strtotime($datetime));

    return "$tanggal {$bulan[$bulanNum]} $tahun $jam";
}

// Fungsi untuk menampilkan daftar tugas
function tampilkanDaftar() {
    $tasks = $_SESSION['tasks'];

    // Tambahkan index ke setiap tugas untuk referensi
    $indexedTasks = [];
    foreach ($tasks as $index => $task) {
        $task['index'] = $index;
        $indexedTasks[] = $task;
    }

    // Urutkan: tugas yang belum selesai di atas, lalu berdasarkan deadline
    usort($indexedTasks, function ($a, $b) {
        if ($a['done'] === $b['done']) {
            // Jika sama-sama selesai atau belum, urut berdasarkan deadline atau urutan awal
            return $a['done'] ? $a['index'] <=> $b['index'] : strtotime($a['deadline']) <=> strtotime($b['deadline']);
        }
        return $a['done'] <=> $b['done']; // Prioritaskan yang belum selesai
    });

    // Tampilkan setiap tugas dalam elemen card Bootstrap
    foreach ($indexedTasks as $task) {
        $index = $task['index'];
        $doneClass = $task['done'] ? 'text-decoration-line-through text-muted' : '';
        $cardClass = $task['done'] ? 'bg-success bg-opacity-10' : '';
        $deadlineFormatted = formatTanggal($task['deadline']);
        $checked = $task['done'] ? '✅' : '⬜';
        $text = htmlspecialchars($task['text']); // Hindari XSS

        // HTML output menggunakan sintaks heredoc
        echo <<<HTML
        <div class="card shadow-sm mb-3 $cardClass">
            <div class="card-body d-flex justify-content-between align-items-center">
                <!-- Form toggle status tugas -->
                <form method="post" class="d-flex align-items-center flex-grow-1 me-3">
                    <input type="hidden" name="toggle" value="$index">
                    <button class="btn btn-outline-secondary btn-sm me-3" type="submit" title="Tandai selesai">$checked</button>
                    <div class="flex-grow-1">
                        <div class="$doneClass fw-semibold">$text</div>
                        <small class="text-secondary">Deadline: $deadlineFormatted</small>
                    </div>
                </form>
                <!-- Tombol edit dan hapus -->
                <div class="d-flex gap-2">
                    <!-- Form edit tugas -->
                    <form method="post">
                        <input type="hidden" name="edit_index" value="$index">
                        <input type="hidden" name="edit_text" value="$text">
                        <input type="hidden" name="edit_deadline" value="{$task['deadline']}">
                        <button class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil-square"></i></button>
                    </form>
                    <!-- Form hapus tugas -->
                    <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                        <input type="hidden" name="delete" value="$index">
                        <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
HTML;
    }
}

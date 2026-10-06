<?php
/**
 * nilai.php - Aplikasi Manajemen Data Nilai Siswa
 * Fitur : queue, stack, bubble/selection sort, linear/binary search, profiling
 *
 * Changelog:
 *  v1.0 (06-10-2026) Versi awal
 */
session_start();
foreach (array('data', 'antrian', 'undo') as $k) {
    if (!isset($_SESSION[$k])) $_SESSION[$k] = array();
}

/* ---------- SORTING (ascending berdasarkan $key) ---------- */
function bubbleSort($arr, $key) {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j][$key] > $arr[$j + 1][$key]) {
                $t = $arr[$j]; $arr[$j] = $arr[$j + 1]; $arr[$j + 1] = $t;
            }
        }
    }
    return $arr;
}

function selectionSort($arr, $key) {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j][$key] < $arr[$min][$key]) $min = $j;

        }
        $t = $arr[$i]; $arr[$i] = $arr[$min]; $arr[$min] = $t;
    }
    return $arr;
}

/* ---------- SEARCHING ---------- */
// Linear search: cek satu per satu (data tidak perlu urut)
function linearSearch($arr, $nama) {
    foreach ($arr as $i => $s) {
        if (strcasecmp($s['nama'], $nama) == 0) return $i;
    }
    return -1;
}

// Binary search: data HARUS sudah urut berdasarkan NIM
function binarySearch($arr, $nim) {
    $low = 0; $high = count($arr) - 1;
    while ($low <= $high) {
        $mid = (int)(($low + $high) / 2);
        if ($arr[$mid]['nim'] == $nim) return $mid;
        if ($arr[$mid]['nim'] < $nim) $low = $mid + 1;
        else $high = $mid - 1;
    }
    return -1;
}

/* ---------- PROSES FORM ---------- */
$tampil = $_SESSION['data'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $aksi = $_POST['aksi'];

    if ($aksi == 'tambah') {                       // ENQUEUE
        $_SESSION['antrian'][] = array(
            'nim'   => trim($_POST['nim']),
            'nama'  => trim($_POST['nama']),
            'nilai' => (int)$_POST['nilai']
        );
        $pesan = 'Data masuk antrian.';

    } elseif ($aksi == 'proses') {                 // DEQUEUE + PUSH ke stack
        if (count($_SESSION['antrian']) > 0) {
            $s = array_shift($_SESSION['antrian']);
            $_SESSION['data'][] = $s;
            array_push($_SESSION['undo'], $s['nim']);
            $pesan = 'Data ' . $s['nama'] . ' disimpan.';
        } else {
            $pesan = 'Antrian kosong.';
        }

    } elseif ($aksi == 'undo') {                   // POP dari stack
        if (count($_SESSION['undo']) > 0) {
            $nim = array_pop($_SESSION['undo']);
            foreach ($_SESSION['data'] as $i => $s) {
                if ($s['nim'] == $nim) {
                    array_unshift($_SESSION['antrian'], $s); // balik ke depan antrian
                    unset($_SESSION['data'][$i]);
                    break;
                }
            }
            $_SESSION['data'] = array_values($_SESSION['data']);
            $pesan = 'Input terakhir dibatalkan.';

        } else {
            $pesan = 'Tidak ada yang bisa di-undo.';
        }

    } elseif ($aksi == 'urut') {
        $hasil = ($_POST['metode'] == 'bubble')
            ? bubbleSort($_SESSION['data'], 'nilai')
            : selectionSort($_SESSION['data'], 'nilai');
        $tampil = array_reverse($hasil);           // nilai tertinggi di atas
        $pesan = 'Diurutkan dengan ' . $_POST['metode'] . ' sort.';

    } elseif ($aksi == 'cari_linear') {
        $i = linearSearch($_SESSION['data'], $_POST['kunci']);
        $pesan = ($i >= 0)
            ? 'Linear search: ditemukan di indeks ' . $i
            : 'Linear search: tidak ditemukan.';

    } elseif ($aksi == 'cari_binary') {
        $urut = bubbleSort($_SESSION['data'], 'nim');
        $i = binarySearch($urut, trim($_POST['kunci']));
        $pesan = ($i >= 0)
            ? 'Binary search: ' . $urut[$i]['nama'] . ' (nilai ' . $urut[$i]['nilai'] . ')'
            : 'Binary search: NIM tidak ditemukan.';

    } elseif ($aksi == 'profil') {                 // PROFILING
        $ulang = 1000;
        $t1 = microtime(true);
        for ($k = 0; $k < $ulang; $k++) bubbleSort($_SESSION['data'], 'nilai');
        $bubble = (microtime(true) - $t1) * 1000;

        $t2 = microtime(true);
        for ($k = 0; $k < $ulang; $k++) selectionSort($_SESSION['data'], 'nilai');

        $select = (microtime(true) - $t2) * 1000;

        $pesan = 'Profiling ' . count($_SESSION['data']) . ' data x ' . $ulang
               . ' kali: Bubble ' . round($bubble, 2) . ' ms, Selection '
               . round($select, 2) . ' ms';

    } elseif ($aksi == 'reset') {
        session_destroy();
        header('Location: nilai.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Data Nilai Siswa</title></head>
<body>
<h2>Manajemen Data Nilai Siswa</h2>

<?php if ($pesan) echo '<p><b>' . htmlspecialchars($pesan) . '</b></p>'; ?>

<h3>1. Tambah ke Antrian (Queue)</h3>
<form method="post">
    <input type="hidden" name="aksi" value="tambah">
    NIM: <input type="text" name="nim" required>
    Nama: <input type="text" name="nama" required>
    Nilai: <input type="number" name="nilai" min="0" max="100" required>
    <button>Tambah</button>
</form>
<p>Antrian: <?php
    $n = array();
    foreach ($_SESSION['antrian'] as $s) $n[] = htmlspecialchars($s['nama']);

    echo count($n) ? implode(' &rarr; ', $n) : '(kosong)';
?></p>

<form method="post" style="display:inline">
    <input type="hidden" name="aksi" value="proses"><button>Proses Antrian</button>
</form>
<form method="post" style="display:inline">
    <input type="hidden" name="aksi" value="undo"><button>Undo (Stack)</button>
</form>
<form method="post" style="display:inline">
    <input type="hidden" name="aksi" value="reset"><button>Reset</button>
</form>

<h3>2. Urutkan Nilai</h3>
<form method="post">
    <input type="hidden" name="aksi" value="urut">
    <select name="metode">
        <option value="bubble">Bubble Sort</option>
        <option value="selection">Selection Sort</option>
    </select>
    <button>Urutkan</button>
</form>

<h3>3. Cari Data</h3>
<form method="post">
    <input type="text" name="kunci" placeholder="Nama / NIM" required>
    <button name="aksi" value="cari_linear">Cari Nama (Linear)</button>
    <button name="aksi" value="cari_binary">Cari NIM (Binary)</button>
</form>

<h3>4. Profiling</h3>
<form method="post">

    <input type="hidden" name="aksi" value="profil"><button>Ukur Waktu Sorting</button>
</form>

<h3>Data Siswa</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>No</th><th>NIM</th><th>Nama</th><th>Nilai</th></tr>
    <?php foreach ($tampil as $i => $s): ?>
    <tr>
        <td><?php echo $i + 1; ?></td>
        <td><?php echo htmlspecialchars($s['nim']); ?></td>
        <td><?php echo htmlspecialchars($s['nama']); ?></td>
        <td><?php echo $s['nilai']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
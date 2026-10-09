<?php
/**
 * ============================================================
 * File      : index.php
 * Aplikasi  : Manajemen Data Nilai Mahasiswa
 * Mapping   : SubCPMK-3 (sorting, searching, traversal, stack, queue,
 *             profiling, dokumentasi, version control)
 * Penyimpanan: data/mahasiswa.json (dibuat otomatis)
 * Kompatibel: PHP 5.4 ke atas (termasuk PHP 7 dan 8)
 * Jalankan  : php -S localhost:8000   lalu buka http://localhost:8000
 *
 * CHANGELOG
 *  v1.0 - CRUD nilai (array), bubble/selection sort, linear/binary search
 *  v1.1 - Stack (undo), Queue (antrian input), traversal statistik
 *  v1.2 - Profiling waktu eksekusi algoritma pada dataset kecil
 *  v2.0 - Data utama disimpan di Stack; input lewat Queue; undo = Stack snapshot
 *  v2.1 - Perbaikan error sintaks di PHP lama (tanpa return type, ??, fn, dll)
 * ============================================================
 */
session_start();
define('DB', dirname(__FILE__) . '/data/mahasiswa.json');

/* ---------- STORAGE & UTIL ---------- */

/** Mengambil nilai array dengan default bila key tidak ada. */
function val($arr, $k, $def = '') {
    return isset($arr[$k]) ? $arr[$k] : $def;
}
/** Membaca seluruh data mahasiswa (array of array). */
function loadData() {
    if (!is_file(DB)) return array();
    $d = json_decode(file_get_contents(DB), true);
    return is_array($d) ? $d : array();
}
/** Menyimpan data ke file JSON. */
function saveData($d) {
    if (!is_dir(dirname(DB))) mkdir(dirname(DB), 0755, true);
    file_put_contents(DB, json_encode(array_values($d), JSON_PRETTY_PRINT), LOCK_EX);
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
/** Konversi nilai akhir ke huruf mutu. */
function grade($n) {
    return $n >= 85 ? 'A' : ($n >= 75 ? 'B' : ($n >= 65 ? 'C' : ($n >= 50 ? 'D' : 'E')));
}
/** Membangun satu record dari input; return array(row|null, error). */
function makeRow($p) {
    $nim = trim(val($p, 'nim')); $nama = trim(val($p, 'nama'));
    if (!ctype_digit($nim) || $nama === '') return array(null, 'NIM harus angka dan nama wajib diisi.');
    $n = array();
    foreach (array('tugas', 'uts', 'uas') as $k) {
        $v = val($p, $k);
        if (!is_numeric($v) || $v < 0 || $v > 100) return array(null, 'Nilai harus 0-100.');
        $n[$k] = (float)$v;
    }
    // Nilai akhir = 30% tugas + 30% UTS + 40% UAS
    $akhir = round(0.3 * $n['tugas'] + 0.3 * $n['uts'] + 0.4 * $n['uas'], 2);
    $row = array('nim' => $nim, 'nama' => $nama) + $n + array('akhir' => $akhir, 'grade' => grade($akhir));
    return array($row, '');
}
/** Waktu sekarang dalam milidetik. */
function nowMs() { return microtime(true) * 1000; }

/* ---------- ALGORITMA SORTING ---------- */

/** Pembanding generik: angka secara numerik, teks tanpa case. */
function cmp($a, $b, $asc) {
    if (is_numeric($a) && is_numeric($b)) $r = ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
    else $r = strcasecmp($a, $b);
    return $asc ? $r : -$r;
}
/** Bubble sort. Return array(data terurut, jumlah perbandingan). */
function bubbleSort($a, $k, $asc = true) {
    $n = count($a); $steps = 0;
    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;
        for ($j = 0; $j < $n - $i - 1; $j++) {
            $steps++;
            if (cmp($a[$j][$k], $a[$j + 1][$k], $asc) > 0) {
                $t = $a[$j]; $a[$j] = $a[$j + 1]; $a[$j + 1] = $t; $swapped = true;
            }
        }
        if (!$swapped) break; // optimasi lokal: berhenti bila sudah terurut
    }
    return array($a, $steps);
}
/** Selection sort. Return array(data terurut, jumlah perbandingan). */
function selectionSort($a, $k, $asc = true) {
    $n = count($a); $steps = 0;
    for ($i = 0; $i < $n - 1; $i++) {
        $m = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            $steps++;
            if (cmp($a[$j][$k], $a[$m][$k], $asc) < 0) $m = $j;
        }
        if ($m !== $i) { $t = $a[$i]; $a[$i] = $a[$m]; $a[$m] = $t; }
    }
    return array($a, $steps);
}

/* ---------- ALGORITMA SEARCHING ---------- */

/** Linear search (partial match, tanpa case) pada field tertentu. */
function linearSearch($a, $k, $q) {
    $found = array(); $steps = 0;
    foreach ($a as $row) { // traversal
        $steps++;
        if (stripos((string)$row[$k], $q) !== false) $found[] = $row;
    }
    return array($found, $steps);
}
/** Binary search NIM (syarat: $a sudah terurut naik berdasarkan nim). */
function binarySearch($a, $nim) {
    $lo = 0; $hi = count($a) - 1; $steps = 0;
    while ($lo <= $hi) {
        $steps++;
        $mid = (int)floor(($lo + $hi) / 2);
        $c = cmp($a[$mid]['nim'], $nim, true);
        if ($c === 0) return array(array($a[$mid]), $steps);
        if ($c < 0) $lo = $mid + 1; else $hi = $mid - 1;
    }
    return array(array(), $steps);
}

/* ---------- STRUKTUR DATA: STACK & QUEUE ---------- */

/**
 * Stack (LIFO). Dipakai sebagai penyimpan DATA UTAMA mahasiswa
 * dan sebagai riwayat Undo. Elemen terakhir yang di-push = puncak.
 */
class Stack {
    private $d;
    function __construct($d = array()) { $this->d = array_values($d); }
    function push($x) { $this->d[] = $x; }
    function pop() { return array_pop($this->d); }
    function peek() { return $this->d ? $this->d[count($this->d) - 1] : null; }
    function isEmpty() { return !$this->d; }
    function size() { return count($this->d); }
    function toArray() { return $this->d; } // dasar -> puncak (traversal)
    /** Apakah ada elemen yang cocok (traversal). */
    function contains($match) {
        foreach ($this->d as $x) if ($match($x)) return true;
        return false;
    }
    /**
     * Hapus (atau ganti dengan $new) elemen pertama yang cocok dari puncak,
     * hanya memakai pop/push dengan bantuan stack sementara.
     * Urutan stack tetap terjaga. Return true bila ditemukan.
     */
    function replaceWhere($match, $new = null) {
        $tmp = new Stack(); $ok = false;
        while (!$this->isEmpty()) {
            $x = $this->pop();
            if (!$ok && $match($x)) {
                $ok = true;
                if ($new !== null) $tmp->push($new);
                continue;
            }
            $tmp->push($x);
        }
        while (!$tmp->isEmpty()) $this->push($tmp->pop());
        return $ok;
    }
}
/** Queue (FIFO). Dipakai untuk antrian input nilai sebelum masuk ke Stack. */
class Queue {
    private $d;
    function __construct($d = array()) { $this->d = array_values($d); }
    function enqueue($x) { $this->d[] = $x; }
    function dequeue() { return array_shift($this->d); }
    function peek() { return isset($this->d[0]) ? $this->d[0] : null; }
    function isEmpty() { return !$this->d; }
    function size() { return count($this->d); }
    function toArray() { return $this->d; }
}

/* ---------- PROFILING ---------- */

/** Mengukur waktu (ms) algoritma pada dataset acak berukuran kecil. */
function profiling() {
    $out = array();
    foreach (array(100, 300, 600) as $n) {
        $nims = range(1000, 1000 + $n - 1); shuffle($nims);
        $d = array();
        foreach ($nims as $x) $d[] = array('nim' => (string)$x, 'nama' => 'M' . $x);
        $t = nowMs(); list($s1, $c1) = bubbleSort($d, 'nim'); $tb = nowMs() - $t;
        $t = nowMs(); list($tmp, $c2) = selectionSort($d, 'nim'); $ts = nowMs() - $t;
        $t = nowMs(); $c3 = 0;
        for ($i = 0; $i < 200; $i++) { $r = linearSearch($s1, 'nim', (string)$nims[$i % $n]); $c3 += $r[1]; }
        $tl = nowMs() - $t;
        $t = nowMs(); $c4 = 0;
        for ($i = 0; $i < 200; $i++) { $r = binarySearch($s1, (string)$nims[$i % $n]); $c4 += $r[1]; }
        $tbs = nowMs() - $t;
        $out[] = compact('n', 'tb', 'c1', 'ts', 'c2', 'tl', 'c3', 'tbs', 'c4');
    }
    return $out;
}

/* ---------- CONTROLLER ---------- */

$stack = new Stack(loadData());                          // data utama = Stack
$undo  = new Stack(val($_SESSION, 'undo', array()));     // riwayat snapshot = Stack
$queue = new Queue(val($_SESSION, 'queue', array()));    // antrian input = Queue
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = val($_POST, 'act');
    $old = val($_POST, 'old');
    if ($act === 'save') {
        list($row, $err) = makeRow($_POST);
        $nimBaru = $row ? $row['nim'] : '';
        $dup = false;
        if ($row && $nimBaru !== $old) {
            $dup = $stack->contains(function ($r) use ($nimBaru) { return $r['nim'] === $nimBaru; });
        }
        if ($err || $dup) $msg = $err ? $err : 'NIM sudah terdaftar.';
        else {
            $undo->push($stack->toArray());
            $diganti = false;
            if ($old !== '') {
                $diganti = $stack->replaceWhere(function ($r) use ($old) { return $r['nim'] === $old; }, $row);
            }
            if (!$diganti) $stack->push($row);
            saveData($stack->toArray()); $msg = 'Data tersimpan.';
        }
    } elseif ($act === 'delete') {
        $undo->push($stack->toArray());
        $nim = val($_POST, 'nim');
        $stack->replaceWhere(function ($r) use ($nim) { return $r['nim'] === $nim; });
        saveData($stack->toArray()); $msg = 'Data dihapus (bisa di-undo).';
    } elseif ($act === 'pop') {
        if ($stack->isEmpty()) $msg = 'Stack kosong.';
        else {
            $undo->push($stack->toArray()); $r = $stack->pop();
            saveData($stack->toArray()); $msg = 'Pop: ' . $r['nama'] . ' dikeluarkan dari puncak.';
        }
    } elseif ($act === 'undo') {
        if ($undo->isEmpty()) $msg = 'Tidak ada yang bisa di-undo.';
        else { $stack = new Stack($undo->pop()); saveData($stack->toArray()); $msg = 'Undo berhasil.'; }
    } elseif ($act === 'enqueue') {
        list($row, $err) = makeRow($_POST);
        if ($err) $msg = $err; else { $queue->enqueue($row); $msg = 'Masuk antrian.'; }
    } elseif ($act === 'process') {
        $undo->push($stack->toArray()); $n = 0;
        while (!$queue->isEmpty()) {            // FIFO: antrian -> stack
            $r = $queue->dequeue();
            $nimR = $r['nim'];
            $ada = $stack->contains(function ($x) use ($nimR) { return $x['nim'] === $nimR; });
            if (!$ada) { $stack->push($r); $n++; }
        }
        saveData($stack->toArray()); $msg = "$n data dari antrian dipush ke stack.";
    }
    $_SESSION['undo'] = $undo->toArray();
    $_SESSION['queue'] = $queue->toArray();
}
$data = $stack->toArray(); // traversal stack -> array kerja untuk sort/search

/* Sorting */
$sortKey = in_array(val($_GET, 'sort'), array('nim', 'nama', 'tugas', 'uts', 'uas', 'akhir')) ? $_GET['sort'] : 'nim';
$asc  = val($_GET, 'dir', 'asc') !== 'desc';
$algo = val($_GET, 'algo', 'bubble') === 'selection' ? 'selection' : 'bubble';
$t = nowMs();
if ($algo === 'selection') list($view, $sortSteps) = selectionSort($data, $sortKey, $asc);
else list($view, $sortSteps) = bubbleSort($data, $sortKey, $asc);
$sortMs = nowMs() - $t;

/* Searching */
$q = trim(val($_GET, 'q')); $mode = val($_GET, 'mode', 'linear'); $sInfo = '';
if ($q !== '') {
    $t = nowMs();
    if ($mode === 'binary') {
        list($sorted) = bubbleSort($data, 'nim', true);
        list($view, $st) = binarySearch($sorted, $q);
    } else {
        $field = val($_GET, 'field', 'nama') === 'nim' ? 'nim' : 'nama';
        list($view, $st) = linearSearch($data, $field, $q);
    }
    $sInfo = ucfirst($mode) . " search: " . count($view) . " hasil, $st langkah, " . round(nowMs() - $t, 4) . " ms";
}

/* Traversal statistik */
$stat = array('n' => 0, 'sum' => 0, 'max' => null, 'min' => null, 'g' => array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0));
foreach ($data as $r) {
    $stat['n']++; $stat['sum'] += $r['akhir']; $stat['g'][$r['grade']]++;
    if ($stat['max'] === null || $r['akhir'] > $stat['max']['akhir']) $stat['max'] = $r;
    if ($stat['min'] === null || $r['akhir'] < $stat['min']['akhir']) $stat['min'] = $r;
}
$edit = null;
foreach ($data as $r) if ($r['nim'] === val($_GET, 'edit')) $edit = $r;
$prof = isset($_GET['prof']) ? profiling() : null;
$top = $stack->peek(); $front = $queue->peek();
/** Link header kolom untuk sorting. */
function sl($k, $label, $algo) {
    global $sortKey, $asc;
    $d = ($sortKey === $k && $asc) ? 'desc' : 'asc';
    return "<a href='?sort=$k&dir=$d&algo=$algo'>$label" . ($sortKey === $k ? ($asc ? ' ▲' : ' ▼') : '') . "</a>";
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manajemen Nilai Mahasiswa</title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#f4f6fa;color:#1d2433}
header{background:#2b4c9b;color:#fff;padding:16px 20px}h1{margin:0;font-size:20px}
main{max-width:1000px;margin:auto;padding:16px}
.card{background:#fff;border-radius:10px;padding:14px;margin-bottom:14px;box-shadow:0 1px 3px #0002}
h2{margin:0 0 10px;font-size:16px}
input,select,button{padding:7px;margin:2px;border:1px solid #bbc;border-radius:6px;font-size:14px}
button,.btn{background:#2b4c9b;color:#fff;border:0;cursor:pointer;text-decoration:none;padding:7px 12px;border-radius:6px;font-size:14px}
.red{background:#c0392b}.gray{background:#667}
table{width:100%;border-collapse:collapse;font-size:14px}th,td{padding:6px;border-bottom:1px solid #e3e6ee;text-align:left}
th a{color:inherit;text-decoration:none}.wrap{overflow-x:auto}
.msg{background:#e8f5e9;padding:8px 12px;border-radius:6px;margin-bottom:12px}
.info{color:#556;font-size:13px;margin:6px 0}form{display:inline}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px}
</style></head><body>
<header><h1>📚 Manajemen Nilai Mahasiswa</h1></header>
<main>
<?php if ($msg): ?><div class="msg"><?= h($msg) ?></div><?php endif; ?>

<div class="card"><h2>Statistik &amp; Status Struktur Data</h2>
<div class="grid">
<div>Jumlah: <b><?= $stat['n'] ?></b></div>
<div>Rata-rata: <b><?= $stat['n'] ? round($stat['sum'] / $stat['n'], 2) : 0 ?></b></div>
<div>Tertinggi: <b><?= $stat['max'] ? h($stat['max']['nama']) . ' (' . $stat['max']['akhir'] . ')' : '-' ?></b></div>
<div>Terendah: <b><?= $stat['min'] ? h($stat['min']['nama']) . ' (' . $stat['min']['akhir'] . ')' : '-' ?></b></div>
</div>
<div class="info">Sebaran: <?php foreach ($stat['g'] as $g => $c) echo "$g=$c &nbsp;"; ?></div>
<div class="info">Puncak Stack: <b><?= $top ? h($top['nama']) : '-' ?></b> (<?= $stack->size() ?> elemen) &nbsp;|&nbsp;
Depan Queue: <b><?= $front ? h($front['nama']) : '-' ?></b> (<?= $queue->size() ?> antri)</div></div>

<div class="card"><h2><?= $edit ? 'Edit' : 'Tambah' ?> Nilai</h2>
<form method="post"><input type="hidden" name="act" value="save">
<input type="hidden" name="old" value="<?= h(val($edit, 'nim')) ?>">
<input name="nim" placeholder="NIM" required value="<?= h(val($edit, 'nim')) ?>">
<input name="nama" placeholder="Nama" required value="<?= h(val($edit, 'nama')) ?>">
<input name="tugas" type="number" step="0.01" min="0" max="100" placeholder="Tugas" required value="<?= h(val($edit, 'tugas')) ?>">
<input name="uts" type="number" step="0.01" min="0" max="100" placeholder="UTS" required value="<?= h(val($edit, 'uts')) ?>">
<input name="uas" type="number" step="0.01" min="0" max="100" placeholder="UAS" required value="<?= h(val($edit, 'uas')) ?>">
<button>Simpan</button>
<button name="act" value="enqueue" class="gray" title="Masukkan ke antrian (Queue)">+ Antrian</button>
<?php if ($edit): ?><a class="btn gray" href="./">Batal</a><?php endif; ?></form>
<div class="info">Nilai akhir = 30% Tugas + 30% UTS + 40% UAS.</div></div>

<div class="card"><h2>Antrian Input (Queue/FIFO) — <?= $queue->size() ?> menunggu</h2>
<?php foreach ($queue->toArray() as $i => $r) echo ($i + 1) . '. ' . h($r['nim']) . ' - ' . h($r['nama']) . '<br>'; ?>
<form method="post"><button name="act" value="process" <?= $queue->isEmpty() ? 'disabled' : '' ?>>Proses Antrian → Push ke Stack</button></form>
<form method="post"><button name="act" value="pop" class="gray" <?= $stack->isEmpty() ? 'disabled' : '' ?>>Pop Puncak Stack</button></form>
<form method="post"><button name="act" value="undo" class="red" <?= $undo->isEmpty() ? 'disabled' : '' ?>>↶ Undo (Stack: <?= $undo->size() ?>)</button></form></div>

<div class="card"><h2>Cari Data</h2>
<form method="get">
<input name="q" placeholder="Kata kunci / NIM" value="<?= h($q) ?>">
<select name="mode"><option value="linear" <?= $mode === 'linear' ? 'selected' : '' ?>>Linear Search</option>
<option value="binary" <?= $mode === 'binary' ? 'selected' : '' ?>>Binary Search (NIM persis)</option></select>
<select name="field"><option value="nama">Field: Nama</option><option value="nim" <?= val($_GET, 'field') === 'nim' ? 'selected' : '' ?>>Field: NIM</option></select>
<button>Cari</button><a class="btn gray" href="./">Reset</a></form>
<?php if ($sInfo): ?><div class="info"><?= h($sInfo) ?></div><?php endif; ?></div>

<div class="card"><h2>Daftar Nilai (isi Stack)</h2>
<form method="get">Algoritma sort:
<input type="hidden" name="sort" value="<?= h($sortKey) ?>"><input type="hidden" name="dir" value="<?= $asc ? 'asc' : 'desc' ?>">
<select name="algo" onchange="this.form.submit()"><option value="bubble" <?= $algo === 'bubble' ? 'selected' : '' ?>>Bubble Sort</option>
<option value="selection" <?= $algo === 'selection' ? 'selected' : '' ?>>Selection Sort</option></select></form>
<div class="info"><?= ucfirst($algo) ?> sort by <?= h($sortKey) ?>: <?= $sortSteps ?> perbandingan, <?= round($sortMs, 4) ?> ms</div>
<div class="wrap"><table><tr>
<th><?= sl('nim', 'NIM', $algo) ?></th><th><?= sl('nama', 'Nama', $algo) ?></th><th><?= sl('tugas', 'Tugas', $algo) ?></th>
<th><?= sl('uts', 'UTS', $algo) ?></th><th><?= sl('uas', 'UAS', $algo) ?></th><th><?= sl('akhir', 'Akhir', $algo) ?></th><th>Grade</th><th>Aksi</th></tr>
<?php foreach ($view as $r): ?>
<tr><td><?= h($r['nim']) ?></td><td><?= h($r['nama']) ?></td><td><?= $r['tugas'] ?></td><td><?= $r['uts'] ?></td>
<td><?= $r['uas'] ?></td><td><b><?= $r['akhir'] ?></b></td><td><?= $r['grade'] ?></td>
<td><a class="btn" href="?edit=<?= h($r['nim']) ?>">Edit</a>
<form method="post" onsubmit="return confirm('Hapus data?')"><input type="hidden" name="nim" value="<?= h($r['nim']) ?>">
<button name="act" value="delete" class="red">Hapus</button></form></td></tr>
<?php endforeach; if (!$view): ?><tr><td colspan="8">Belum ada data.</td></tr><?php endif; ?>
</table></div></div>

<div class="card"><h2>Profiling Waktu Eksekusi</h2>
<a class="btn" href="?prof=1">Jalankan Profiling</a>
<?php if ($prof): ?><div class="wrap"><table>
<tr><th>N</th><th>Bubble (ms / cmp)</th><th>Selection (ms / cmp)</th><th>Linear ×200 (ms / langkah)</th><th>Binary ×200 (ms / langkah)</th></tr>
<?php foreach ($prof as $p): ?><tr><td><?= $p['n'] ?></td>
<td><?= round($p['tb'], 3) ?> / <?= $p['c1'] ?></td><td><?= round($p['ts'], 3) ?> / <?= $p['c2'] ?></td>
<td><?= round($p['tl'], 3) ?> / <?= $p['c3'] ?></td><td><?= round($p['tbs'], 3) ?> / <?= $p['c4'] ?></td></tr><?php endforeach; ?>
</table></div>
<div class="info">Dataset acak kecil. Binary search jauh lebih sedikit langkah, tetapi data harus terurut.</div><?php endif; ?></div>
</main></body></html>

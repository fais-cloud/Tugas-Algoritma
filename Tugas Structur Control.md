# PROGRAM PENUGASAN TERSTRUKTUR : ALGORITMA DAN STRUKTUR DATA
## Studi Kasus : Sistem Transaksi & Validasi Toko Buku Modern
\---
## A. Analasis Komponen
### Identifikasi Variabel dan Tipe Data
|Nomor|Variabel|Tipe Data|Keterangan|
|-|-|-|-|
|1|`is\_member`|Boolean|Status keanggotaan pelanggan (True/False), ini yang jadi input awal|
|2|`jumlah\_buku`|Integer (bilangan bulat)|Jumlah buku yang dibeli, ini juga input awal|
|3|`total\_awal`|Real (bilangan pecahan/mengapung)|Total belanja pelanggan sebelum kena diskon, input awal juga|
|4|`persen\_diskon`|Real|Variabel bantu, dipakai buat nyimpen sementara berapa persen diskon yang berlaku (10, 15, 5, atau 0)|
|5|`nominal\_diskon`|Real|Variabel output, nyimpen berapa rupiah potongan diskonnya|
|6|`total\_bayar`|Real|Variabel output, nyimpen total akhir yang harus dibayar pelanggan|

### Identifikasi Struktrul Kontrol
|Struktur|-|
|-|-|
|**Sequence**|Alurnya: user isi data → program hitung diskon → hitung potongan harganya → hitung total yang harus dibayar → baru ditampilkan hasilnya. Semua ini dikerjakan berurutan.|
|**Selection**|Di soal ini ada percabangan besar: kalau `is\_member = True` (member) beda rumusnya sama kalau `is\_member = False` (non-member). Di dalam masing-masing cabang itu, masih ada percabangan lagi buat cek syarat tambahan diskon (misal member yang belanjanya banyak dapat diskon lebih gede).|
|**Iteration**|Di soal ini ada validasi input di awal (namanya *Validation Loop*). Programnya bakal terus minta user masukin data ulang selama `total\_awal` masih minus **atau** `jumlah\_buku` masih kurang dari 1. Baru berhenti minta input kalau datanya udah bener.|

\---

## B. Pseudocode
```
PROGRAM SistemTransaksiTokoBuku
DEKLARASI:
    is_member : boolean 
    jumlah_buku : integer 
    total_awal : real    
    persen_diskon : real    
    nominal_diskon : real  
    total_bayar : real    
ALGORITMA :
    INPUT(is_member, jumlah_buku, total_awal)

    WHILE (total_awal < 0) OR (jumlah_buku < 1) THEN
        OUTPUT("Input tidak valid. Coba masukkan ulang.")
        INPUT(jumlah_buku, total_awal)
    ENDWHILE

    IF (is_member = True) THEN
        persen_diskon ← 10
        IF (total_awal >= 200000) AND (jumlah_buku >= 3) THEN
            persen_diskon ← 15
        ENDIF
    ELSE
        IF (total_awal >= 300000) THEN
            persen_diskon ← 5
        ELSE
            persen_diskon ← 0
        ENDIF
    ENDIF

    nominal_diskon ← total_awal * (persen_diskon / 100)
    total_bayar ← total_awal - nominal_diskon

    OUTPUT(nominal_diskon, total_bayar)

END
```

## C. Trace Table
### Kasus A: `is_member = True`, `total_awal = 250000`, `jumlah_buku = 4`

Input ini dari awal sudah valid (total_awal tidak negatif, jumlah_buku tidak kurang dari 1), jadi loop validasi langsung dilewati, tidak perlu input ulang.

| Langkah | is_member | jumlah_buku | total_awal | Kondisi Loop (`<0 OR <1`) | persen_diskon | nominal_diskon | total_bayar | Keterangan |
|---|---|---|---|---|---|---|---|---|
| 1 | True | 4 | 250000 | False → langsung lanjut | - | - | - | Input awal sudah valid |
| 2 | True | 4 | 250000 | - | 10 | - | - | Member → dapat diskon dasar 10% |
| 3 | True | 4 | 250000 | - | **15** | - | - | 250000≥200000 DAN 4≥3 → keduanya terpenuhi, tambahan 5% |
| 4 | True | 4 | 250000 | - | 15 | **37500** | - | 250000 × 15% |
| 5 | True | 4 | 250000 | - | 15 | 37500 | **212500** | 250000 − 37500 |

**Output:** `nominal_diskon = 37500`, `total_bayar = 212500`

### Kasus B: `is_member = False`, `total_awal = 350000`, `jumlah_buku = 2`

Input ini juga sudah valid dari awal, jadi loop validasi dilewati.

| Langkah | is_member | jumlah_buku | total_awal | Kondisi Loop (`<0 OR <1`) | persen_diskon | nominal_diskon | total_bayar | Keterangan |
|---|---|---|---|---|---|---|---|---|
| 1 | False | 2 | 350000 | False → langsung lanjut | - | - | - | Input awal sudah valid |
| 2 | False | 2 | 350000 | - | - | - | - | Bukan member → masuk cabang ELSE |
| 3 | False | 2 | 350000 | - | **5** | - | - | 350000≥300000 → dapat diskon 5% |
| 4 | False | 2 | 350000 | - | 5 | **17500** | - | 350000 × 5% |
| 5 | False | 2 | 350000 | - | 5 | 17500 | **332500** | 350000 − 17500 |

**Output:** `nominal_diskon = 17500`, `total_bayar = 332500`

### Kasus C: Input awal `total_awal = -50000` (salah), lalu dikoreksi jadi `100000`, `is_member = False`, `jumlah_buku = 1`

Di kasus ini input pertama tidak valid karena `total_awal` negatif, jadi program masuk ke loop validasi dan minta input ulang. Setelah dikoreksi (`total_awal = 100000`, `jumlah_buku` tetap `1`), barulah program lanjut ke perhitungan diskon.

| Langkah | is_member | jumlah_buku | total_awal | Kondisi Loop (`<0 OR <1`) | persen_diskon | nominal_diskon | total_bayar | Keterangan |
|---|---|---|---|---|---|---|---|---|
| 1 | False | 1 | -50000 | **True** → ulangi | - | - | - | total_awal negatif → input awal tidak valid |
| 2 (re-input) | False | 1 | 100000 | False → keluar loop | - | - | - | Input dikoreksi, sekarang valid |
| 3 | False | 1 | 100000 | - | - | - | - | Bukan member → masuk cabang ELSE |
| 4 | False | 1 | 100000 | - | **0** | - | - | 100000≥300000 → False, jadi tidak dapat diskon |
| 5 | False | 1 | 100000 | - | 0 | **0** | - | 100000 × 0% |
| 6 | False | 1 | 100000 | - | 0 | 0 | **100000** | 100000 − 0 |

**Output:** `nominal_diskon = 0`, `total_bayar = 100000`








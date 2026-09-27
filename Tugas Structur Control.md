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
```

## C. TRACE TABLE







# PROGRAM PENUGASAN TERSTRUKTUR : ALGORITMA DAN STRUKTUR DATA
## DEKLARASI
| Variabel        | Tipe    | Keterangan                              |
|-----------------|---------|------------------------------------------|
| `is_member`     | boolean | Status keanggotaan pelanggan             |
| `jumlah_buku`   | integer | Jumlah buku yang dibeli                  |
| `total_awal`    | real    | Total belanja sebelum diskon             |
| `persen_diskon` | real    | Persentase diskon yang berlaku           |
| `nominal_diskon`| real    | Nominal potongan harga                   |
| `total_bayar`   | real    | Total yang harus dibayar setelah diskon  |

## ALGORITMA
```
1.  input(is_member, jumlah_buku, total_awal)

2.  { Validation Loop }
    WHILE (total_awal < 0) OR (jumlah_buku < 1) DO
        output("Input tidak valid. Total belanja tidak boleh negatif
                dan jumlah buku minimal 1.")
        input(jumlah_buku, total_awal)
    ENDWHILE

3.  { Logika Perhitungan Diskon }
    IF (is_member = True) THEN
        persen_diskon <- 10
        IF (total_awal >= 200000) AND (jumlah_buku >= 3) THEN
            persen_diskon <- 15
        ENDIF
    ELSE
        IF (total_awal >= 300000) THEN
            persen_diskon <- 5
        ELSE
            persen_diskon <- 0
        ENDIF
    ENDIF

4.  nominal_diskon <- total_awal * (persen_diskon / 100)
    total_bayar    <- total_awal - nominal_diskon

5.  output(nominal_diskon, total_bayar)

END
```

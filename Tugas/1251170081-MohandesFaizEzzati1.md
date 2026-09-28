# Tugas MK Algoritma

## Soal A

Input : Pilihan restoran, menu makanan, jumlah pesanan, alamat pengiriman, dan metode pembayaran.

Langkah 1 : Buka aplikasi pemesanan makanan online.

Langkah 2 : Pilih restoran yang diinginkan.

Langkah 3 : Pilih menu makanan dan jumlah porsinya.

Langkah 4 : Sistem menghitung total harga makanan dan ongkir.

Langkah 5 : Pilih menu pembayaran.

Langkah 6 : Jika pembayaran berhasil, pesanan akan dikirimkan ke restoran.

Langkah 4 : Restoran menerima dan memproses pesanan.

Output : Total pembayaran dan pesanan berhasil diterima restoran.


Definiteness : Setiap langkah memiliki instruksi yang jelas, seperti memilih restoran, memilih menu dan jumlah porsinya, menghitung harga, memilih metode pembayaran, dan mengirim pesanan.

Finiteness : Memiliki langkah yang langsung berhenti saat pesanan sudah dikirim ke restoran.

Effectiveness : Langkah-langkahnya bisa dilakukan dengan sederhana, seperti memilih menu makanan, menghitung total harga, memproses pembayaran dan mengirim data pesanan,


## Soal B

### Skenario 1 : (Fitur Undo/Redo)

Struktur data terpilih : Stack

Alasan : Karena menerapkan LIFO, yaitu data terakhir yang masuk akan menjadi data pertama yang keluar. Pada fitur Undo, tindakan terakhir pengguna harus dibatalkan terlebih dahulu. Untuk Redo, tindakan yang dibatalkan dapat disipan pada stack lain sehingga bisa dikembalikkan lagi.


### Skenario 2 : (Peta Navigasi Rute Perjalanan)

Struktur data terpilih : Graph

Alasan : Karena Graph dapat menghubungkan antara satu tempat dengan tempta lainnya. Dalam GPS, lokasi dianggap sebagai titk, sedangkan jalan dianggap sebagai penghubung. Dengan Graph, GPS dpat mencari jalan dari satu tempat ke tempat lainnya dan menentukan rute tercepat dan terpendek.


### Skenario 3 : ( Sistem Login Pengguna Berbasis Username)

Struktur data terpilih : Hash Table

Alasan : Cocok digunakan karena bisa mencari data berdasarkan username dengan cepat. Username digunakan sebagai kunci untuk menemukan data akun pengguna. Hal ini sangat membantuk jika ssitem memeiki ribuan hingga jutaan pengguna, karena sistem tidak perlu mencari akun satu per satu. 


## Soal C

Array

i. Analogi:
Rak Telur

ii. Cara Kerja:
Setiap telur memiliki tempat masing-masing di dalam rak. Kita bisa mengambil telur berdasarkan posisinya tanpa harus mengambil telur yang lain terlebih dahulu.

iii. Mengapa Sesuai dengan Array:

Mirip dengan Array yang setiap data memiliki posisi (index). Kelebihannya, data bisa dicari, atau diambil dengan cepat berdasarkan posisinya. Kekurangannya, jika ingin menambah atau mengurangi data di tengah, beberapa posisi mungkin harus diatur Kembali.

# Studi Kasus: Pos Timbangan (Weighbridge) & Penanganan Overload (ODOL)

### Latar Belakang

Anda sedang bertugas di pos timbangan (*weighbridge*) saat jam sibuk pengiriman. Sebuah truk ekspedisi selesai muat dan naik ke platform timbangan untuk penimbangan akhir (*Gross Weight*).

Setelah dikurangi berat kosong (*Tare Weight*), ditemukan bahwa total berat bersih (*Net Weight*) muatan melebihi batas maksimal (*overload*) yang ditetapkan oleh standar ekspedisi maupun regulasi jalan (ODOL).

Di sisi lain, sopir mendesak agar surat jalan segera dicetak karena mengejar jadwal kapal/bongkar, sementara sistem operasional memiliki aturan ketat bahwa dokumen **Sales Order (SO)** yang sudah diproses (`processed`) **tidak boleh di-cancel (dibatalkan)**.

---

### Setup Environment (PostgreSQL)

1. Clone repository ini:

   ```bash
   git clone https://github.com/susantimegahit-spec/weighbridge.git
   ```

   Lalu lakukan setup project Laravel seperti biasa.
2. Pastikan service **PostgreSQL** berjalan dan buat database baru:

   ```sql
   CREATE DATABASE weighbridge_test;
   ```
3. Sesuaikan konfigurasi koneksi PostgreSQL pada file `.env` Anda.
4. Jalankan migrasi, seeder, dan server lokal, lalu buka `http://127.0.0.1:8000` di browser untuk melihat **daftar soal** sekaligus menguji aplikasi.

---

### Verifikasi & Pengumpulan (Push ke Git)

1. Jalankan automated test untuk memastikan semua 4 tugas lulus:
   ```bash
   php artisan test --filter=WeighbridgeTest
   ```
2. Jika sudah selesai, **buat branch baru** dengan format **`dev_nama_kandidat`** (contoh: `dev_budi_santoso`), commit pekerjaan Anda, lalu push ke GitHub:
   ```bash
   git checkout -b dev_nama_kandidat
   git add .
   git commit -m "Selesai tes coding weighbridge"
   git push -u origin dev_nama_kandidat
   ```

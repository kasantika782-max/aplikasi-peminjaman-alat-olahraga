<?php
class m_peminjaman {
    private $db;

    // Menghubungkan model dengan koneksi database utama
    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Fungsi untuk mencatat setiap klik atau aksi penting user ke tabel log_aktivitas
    public function log_aktivitas($id_user, $aksi) {
        $sql = "INSERT INTO log_aktivitas (id_user, aksi, waktu) VALUES ('$id_user', '$aksi', NOW())";
        $query = mysqli_query($this->db, $sql);
        
        if (!$query) {
            die("Gagal simpan log: " . mysqli_error($this->db));
        }
        return $query;
    }

    // Mengambil semua data log untuk ditampilkan di dashboard admin
    public function tampil_log_aktivitas() {
        $sql = "SELECT l.*, u.username FROM log_aktivitas l 
                JOIN user u ON l.id_user = u.id_user 
                ORDER BY l.id_log DESC";
        return mysqli_query($this->db, $sql);
    }

    // Mengambil semua data peminjaman (Diperbarui untuk menyertakan no_hp user untuk fitur WhatsApp)
    public function tampil_data() {
        $sql = "SELECT p.*, 
                       u.username AS nama_peminjam, 
                       u.no_hp, 
                       a.nama_alat
                FROM peminjaman p
                JOIN user u ON p.id_user = u.id_user
                JOIN alat a ON p.id_alat = a.id_alat
                ORDER BY p.id_peminjaman DESC";
        return mysqli_query($this->db, $sql);
    }

    // --- LOGIKA KRUSIAL: Verifikasi Pinjaman (Stok Berkurang) ---
    public function verifikasi_pinjam($id) {
        // 1. Cari tahu dulu barang apa yang dipinjam dan berapa jumlahnya
        $data = mysqli_fetch_array(mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam FROM peminjaman WHERE id_peminjaman = '$id'"));
        $id_alat = $data['id_alat'];
        $jumlah = $data['jumlah_pinjam'];

        // 2. Ubah status menjadi 'dipinjam'
        mysqli_query($this->db, "UPDATE peminjaman SET status = 'dipinjam' WHERE id_peminjaman = '$id'");

        // 3. KURANGI STOK alat karena barang sudah dibawa user
        return mysqli_query($this->db, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat = '$id_alat'");
    }

    // --- LOGIKA KRUSIAL: Konfirmasi Kembali (Stok Bertambah) ---
    public function konfirmasi_kembali($id) {
        $tgl_skrg = date('Y-m-d H:i:s'); 
        
        // 1. Ambil info barang dan jumlah yang dikembalikan
        $data = mysqli_fetch_array(mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam FROM peminjaman WHERE id_peminjaman = '$id'"));
        $id_alat = $data['id_alat'];
        $jumlah = $data['jumlah_pinjam'];

        // 2. Update status jadi kembali dan catat tanggal kembalinya secara otomatis
        mysqli_query($this->db, "UPDATE peminjaman SET status = 'kembali', tgl_kembali_asli = '$tgl_skrg', kondisi_masuk = 'Baik' WHERE id_peminjaman = '$id'");

        // 3. TAMBAHKAN STOK alat kembali karena barang sudah dikembalikan ke gudang
        return mysqli_query($this->db, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = '$id_alat'");
    }

    // Mengambil data untuk admin dengan filter (sedang dipinjam atau sudah kembali)
    public function tampil_data_admin($tipe) {
        $where = ($tipe == 'kembali') ? "WHERE p.status = 'kembali'" : "WHERE p.status IN ('pending', 'dipinjam')";
        $sql = "SELECT p.*, u.username AS nama_peminjam, u.no_hp, a.nama_alat, k.nama_kategori 
                FROM peminjaman p 
                JOIN user u ON p.id_user = u.id_user 
                JOIN alat a ON p.id_alat = a.id_alat 
                JOIN kategori k ON a.id_kategori = k.id_kategori 
                $where ORDER BY p.id_peminjaman DESC";
        return mysqli_query($this->db, $sql);
    }

    // --- FUNGSI UPDATE PEMINJAMAN DENGAN PENYESUAIAN STOK ---
    public function update_pinjam($id_peminjaman, $jumlah_baru, $status_baru) {
        // 1. Ambil data transaksi lama untuk mengetahui status, jumlah, dan alat sebelumnya
        $q_lama = mysqli_query($this->db, "SELECT * FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
        $data_lama = mysqli_fetch_object($q_lama);

        if (!$data_lama) {
            return false;
        }

        $id_alat       = $data_lama->id_alat;
        $jumlah_lama   = (int)$data_lama->jumlah_pinjam;
        $status_lama   = strtolower($data_lama->status);
        $status_baru   = strtolower($status_baru);
        $jumlah_baru   = (int)$jumlah_baru;

        // Ambil stok alat saat ini
        $q_alat = mysqli_query($this->db, "SELECT stok FROM alat WHERE id_alat = '$id_alat'");
        $data_alat = mysqli_fetch_object($q_alat);
        $stok_sekarang = (int)$data_alat->stok;

        // --- LOGIKA PENYESUAIAN STOK ---

        // KASUS 1: Status berubah dari DIPINJAM -> PENDING
        if ($status_lama == 'dipinjam' && $status_baru == 'pending') {
            $stok_akhir = $stok_sekarang + $jumlah_lama;
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'");
        }

        // KASUS 2: Status berubah dari PENDING -> DIPINJAM
        elseif ($status_lama == 'pending' && $status_baru == 'dipinjam') {
            if ($stok_sekarang < $jumlah_baru) {
                return "stok_kurang"; // Stok tidak cukup
            }
            $stok_akhir = $stok_sekarang - $jumlah_baru;
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'");
        }

        // KASUS 3: Status tetap DIPINJAM, tapi JUMLAH PINJAM diubah
        elseif ($status_lama == 'dipinjam' && $status_baru == 'dipinjam') {
            $selisih = $jumlah_baru - $jumlah_lama;

            if ($selisih > 0) { // Jumlah bertambah, potong stok lagi
                if ($stok_sekarang < $selisih) {
                    return "stok_kurang";
                }
                $stok_akhir = $stok_sekarang - $selisih;
            } else { // Jumlah berkurang, kembalikan keutuhan stok
                $stok_akhir = $stok_sekarang + abs($selisih);
            }
            
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'");
        }

        // 2. Update data transaksi peminjaman
        $query_update = "UPDATE peminjaman 
                         SET jumlah_pinjam = '$jumlah_baru', 
                             status = '$status_baru' 
                         WHERE id_peminjaman = '$id_peminjaman'";

        return mysqli_query($this->db, $query_update);
    }

    // Fungsi update untuk Admin saat mengelola data pengembalian
    public function update_kembali($id, $kondisi, $tgl) {
        return mysqli_query($this->db, "UPDATE peminjaman SET kondisi_masuk = '$kondisi', tgl_kembali_asli = '$tgl', status = 'kembali' WHERE id_peminjaman = '$id'");
    }

    // --- FUNGSI HAPUS DATA (STOK OTOMATIS KEMBALI JIKA DIPINJAM) ---
    public function hapus_data($id) {
        // 1. Ambil info transaksi dulu
        $query_get = mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam, status FROM peminjaman WHERE id_peminjaman = '$id'");
        $data = mysqli_fetch_assoc($query_get);

        if ($data) {
            // 2. Jika barang sedang status 'dipinjam', kembalikan stoknya
            if ($data['status'] === 'dipinjam') {
                $id_alat = $data['id_alat'];
                $jumlah  = $data['jumlah_pinjam'];
                mysqli_query($this->db, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = '$id_alat'");
            }

            // 3. Hapus record dari database
            return mysqli_query($this->db, "DELETE FROM peminjaman WHERE id_peminjaman = '$id'");
        }

        return false;
    }

    // Menampilkan riwayat pinjam khusus untuk 1 user (yang sedang login)
    public function tampil_data_user($id_user) {
        $sql = "SELECT p.*, a.nama_alat FROM peminjaman p JOIN alat a ON p.id_alat = a.id_alat WHERE p.id_user = '$id_user' ORDER BY p.id_peminjaman DESC";
        return mysqli_query($this->db, $sql);
    }

    // Menambahkan antrian pinjaman baru dari user (Status masih Pending)
    public function tambah_pinjam($id_user, $id_alat, $jumlah, $kondisi) {
        $tgl_skrg = date('Y-m-d H:i:s');
        $sql = "INSERT INTO peminjaman (id_user, id_alat, jumlah_pinjam, tgl_pinjam, kondisi_keluar, status) VALUES ('$id_user', '$id_alat', '$jumlah', '$tgl_skrg', '$kondisi', 'pending')";
        return mysqli_query($this->db, $sql);
    }

    // --- Peminjaman Direct/Manual oleh Admin ---
    public function tambah_pinjam_admin($id_user, $id_alat, $jumlah, $kondisi = 'Baik') {
        // 1. Cek stok alat saat ini
        $q_stok = mysqli_query($this->db, "SELECT stok FROM alat WHERE id_alat = '$id_alat'");
        $stok_sekarang = mysqli_fetch_assoc($q_stok)['stok'];

        if ($jumlah > $stok_sekarang) {
            return "stok_kurang";
        }

        $tgl_skrg = date('Y-m-d H:i:s');
        
        // 2. Insert transaksi dengan status 'dipinjam'
        $sql = "INSERT INTO peminjaman (id_user, id_alat, jumlah_pinjam, tgl_pinjam, kondisi_keluar, status) 
                VALUES ('$id_user', '$id_alat', '$jumlah', '$tgl_skrg', '$kondisi', 'dipinjam')";
        $insert = mysqli_query($this->db, $sql);

        // 3. Potong stok alat
        if ($insert) {
            mysqli_query($this->db, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat = '$id_alat'");
            return true;
        }

        return false;
    }

    // Helper untuk Dropdown Form Admin
    public function get_all_peminjam() {
        return mysqli_query($this->db, "SELECT id_user, username FROM user WHERE role = 'peminjam' ORDER BY username ASC");
    }

    public function get_all_alat() {
        return mysqli_query($this->db, "SELECT id_alat, nama_alat, stok FROM alat WHERE stok > 0 ORDER BY nama_alat ASC");
    }
}
?>
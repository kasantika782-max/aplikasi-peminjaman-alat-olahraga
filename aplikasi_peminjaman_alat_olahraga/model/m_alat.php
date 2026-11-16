<?php
class m_alat {
    // Variabel untuk menampung koneksi database
    private $db;

    // Fungsi awal untuk menerima koneksi dari controller
    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // --- FUNGSI TAMPIL DATA (DENGAN RELASI) ---
    public function tampil_data() {
        // LEFT JOIN digunakan agar kita bisa mengambil 'nama_kategori' dari tabel kategori
        // berdasarkan 'id_kategori' yang ada di tabel alat
        $sql = "SELECT alat.*, kategori.nama_kategori 
                FROM alat 
                LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori";
        
        $query = mysqli_query($this->db, $sql);
        $result = [];
        
        // Mengambil data baris demi baris dan mengubahnya menjadi objek
        while ($data = mysqli_fetch_object($query)) {
            $result[] = $data;
        }
        // Mengembalikan kumpulan data alat ke controller
        return $result;
    }

    // --- FUNGSI AMBIL SATU DATA (UNTUK EDIT) ---
    public function tampil_data_by_id($id_alat) {
        $sql = "SELECT * FROM alat WHERE id_alat = ?";
        $stmt = $this->db->prepare($sql);
        // "i" berarti id_alat adalah Integer
        $stmt->bind_param("i", $id_alat);
        $stmt->execute();
        // Mengambil hasil satu baris saja sebagai objek
        return $stmt->get_result()->fetch_object();
    }

    // --- FUNGSI TAMBAH BARANG BARU ---
    public function tambah_data($nama, $kat, $stok, $foto) {
        $sql = "INSERT INTO alat (nama_alat, id_kategori, stok, foto) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        // Bind param urutan: s(string), i(integer), i(integer), s(string)
        $stmt->bind_param("siis", $nama, $kat, $stok, $foto);
        return $stmt->execute();
    }

    // --- FUNGSI UPDATE DATA BARANG ---
    public function ubah_data($id, $nama, $kat, $stok, $foto) {
        // SQL untuk memperbarui data berdasarkan id_alat
        $sql = "UPDATE alat SET nama_alat=?, id_kategori=?, stok=?, foto=? WHERE id_alat=?";
        $stmt = $this->db->prepare($sql);
        // Bind param: siisi (nama=s, kat=i, stok=i, foto=s, id=i)
        $stmt->bind_param("siisi", $nama, $kat, $stok, $foto, $id);
        return $stmt->execute();
    }

    // --- FUNGSI HAPUS BARANG ---
    public function hapus_data($id) {
        // Menghapus baris data berdasarkan ID yang dipilih
        $stmt = $this->db->prepare("DELETE FROM alat WHERE id_alat = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
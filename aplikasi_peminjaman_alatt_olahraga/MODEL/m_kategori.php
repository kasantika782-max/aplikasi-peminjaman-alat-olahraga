<?php
include_once 'm_koneksi.php';

class m_kategori {
    private $db;

    // Fungsi awal untuk menghubungkan model ini dengan koneksi database utama
    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Fungsi SQL: Ambil semua daftar kategori untuk di-looping (foreach) di halaman view
    public function tampil_data() {
        $sql = "SELECT id_kategori, nama_kategori, keterangan_kategori FROM kategori";
        $query = mysqli_query($this->db, $sql);
        $result = [];
        
        if ($query && mysqli_num_rows($query) > 0) {
            // Memasukkan hasil query baris demi baris menjadi sebuah objek
            while ($data = mysqli_fetch_object($query)) {
                $result[] = $data;
            }
        }
        return $result;   
    }

    // Fungsi SQL: Mencari satu data kategori saja berdasarkan ID (untuk proses edit)
    public function tampil_data_by_id($id_kategori) {
        $sql = "SELECT * FROM kategori WHERE id_kategori = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_kategori); // "i" artinya data ID berupa Integer (angka)
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_object();
        $stmt->close();
        return $data;
    }

    // Fungsi SQL: Menambahkan kategori baru ke dalam database
    public function tambah_data($nama, $ket) {
        $sql = "INSERT INTO kategori (nama_kategori, keterangan_kategori) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $nama, $ket); // "ss" artinya kedua data berupa String (teks)
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    // Fungsi SQL: Mengubah data kategori yang sudah tersimpan
    public function ubah_data($nama, $ket, $id) {
        $sql = "UPDATE kategori SET nama_kategori = ?, keterangan_kategori = ? WHERE id_kategori = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $nama, $ket, $id); // ssi: String, String, Integer
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    // Fungsi SQL: Menghapus satu kategori berdasarkan ID-nya
    public function hapus_data($id) {
        $sql = "DELETE FROM kategori WHERE id_kategori = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
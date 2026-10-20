<?php
class m_alat {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    public function tampil_data() {
        $sql = "SELECT alat.*, kategori.nama_kategori
                FROM alat
                LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori
                ORDER BY alat.id_alat DESC";
        $query = mysqli_query($this->db, $sql);
        $result = [];

        if ($query) {
            while ($data = mysqli_fetch_object($query)) {
                $result[] = $data;
            }
        }
        return $result;
    }

    public function tampil_data_by_id($id_alat) {
        $id_alat = (int)$id_alat;
        $stmt = $this->db->prepare("SELECT alat.*, kategori.nama_kategori
                                   FROM alat
                                   LEFT JOIN kategori ON alat.id_kategori = kategori.id_kategori
                                   WHERE alat.id_alat = ?");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id_alat);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_object();
        $stmt->close();
        return $data;
    }

    // Parameter $foto dipertahankan agar controller/view lama tetap kompatibel.
    // Database pada paket ini tidak memiliki kolom foto, jadi nilainya tidak disimpan.
    public function tambah_data($nama, $kat, $stok, $foto = '') {
        $nama = trim((string)$nama);
        $kat = (int)$kat;
        $stok = (int)$stok;

        if ($nama === '' || $kat <= 0 || $stok < 0) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO alat (nama_alat, id_kategori, stok) VALUES (?, ?, ?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param("sii", $nama, $kat, $stok);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function ubah_data($id, $nama, $kat, $stok, $foto = '') {
        $id = (int)$id;
        $nama = trim((string)$nama);
        $kat = (int)$kat;
        $stok = (int)$stok;

        if ($id <= 0 || $nama === '' || $kat <= 0 || $stok < 0) return false;

        $stmt = $this->db->prepare(
            "UPDATE alat SET nama_alat=?, id_kategori=?, stok=? WHERE id_alat=?"
        );
        if (!$stmt) return false;
        $stmt->bind_param("siii", $nama, $kat, $stok, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function hapus_data($id) {
        $id = (int)$id;
        if ($id <= 0) return false;

        $stmt = $this->db->prepare("DELETE FROM alat WHERE id_alat = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>

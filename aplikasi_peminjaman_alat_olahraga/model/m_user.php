<?php
class m_user {
    private $db; 

    // Fungsi awal untuk menerima koneksi database dari controller
    public function __construct($db_connection) {
        if (!$db_connection instanceof mysqli) {
            throw new Exception("Koneksi database tidak valid.");
        }
        $this->db = $db_connection;
    }

    // Fungsi SQL untuk mengambil SEMUA data dari tabel user
    public function tampil_data() {
        $sql = "SELECT * FROM user";
        $result = $this->db->query($sql);
        
        $data = []; 
        if ($result) {
            // Memasukkan setiap baris data ke dalam array 'data'
            while ($row = $result->fetch_object()) {
                $data[] = $row;
            }
        } 
        return $data; 
    }

    // Fungsi SQL untuk mencari satu user berdasarkan ID (Penting untuk Edit)
    public function tampil_data_by_id($id_user) {
        $sql = "SELECT * FROM user WHERE id_user = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_user); // "i" artinya ID adalah Integer (angka)
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_object();
        $stmt->close();
        
        return $data;
    }

    // Fungsi SQL untuk memasukkan data user baru ke database
    public function tambah_data($username, $password_hash, $role, $no_hp) {
        $sql = "INSERT INTO user (username, password, role, no_hp) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        // "ssss" artinya data yang masuk semuanya berbentuk String (teks)
        $stmt->bind_param("ssss", $username, $password_hash, $role, $no_hp);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } 

    // Fungsi SQL untuk memperbarui data user yang sudah ada
    public function ubah_data($id_user, $username, $password_hash = null, $no_hp = null) {
        if ($password_hash) {
            // Query jika user ingin mengganti password juga
            $sql = "UPDATE user SET username = ?, password = ?, no_hp = ? WHERE id_user = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssi", $username, $password_hash, $no_hp, $id_user);
        } else {
            // Query jika user HANYA mengganti username (password lama tetap)
            $sql = "UPDATE user SET username = ?, no_hp = ? WHERE id_user = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $username, $no_hp, $id_user);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Fungsi SQL untuk menghapus user dari database
    public function hapus_data($id_user) {
        $sql = "DELETE FROM user WHERE id_user = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_user);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}
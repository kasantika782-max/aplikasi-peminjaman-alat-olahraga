<?php 
class m_peminjaman { 
    private $db; 
 
    public function __construct($db_connection) { 
        $this->db = $db_connection; 
    } 
 
    public function log_aktivitas($id_user, $aksi) { 
        $sql = "INSERT INTO log_aktivitas (id_user, aksi, waktu) VALUES ('$id_user', '$aksi', NOW())"; 
        $query = mysqli_query($this->db, $sql); 
         
        if (!$query) { 
            die("Gagal simpan log: " . mysqli_error($this->db)); 
        } 
        return $query; 
    } 
 
    public function tampil_log_aktivitas() { 
        $sql = "SELECT l.*, u.username FROM log_aktivitas l  
                JOIN user u ON l.id_user = u.id_user  
                ORDER BY l.id_log DESC"; 
        return mysqli_query($this->db, $sql); 
    } 
 
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
 
    public function verifikasi_pinjam($id) { 
        $data = mysqli_fetch_array(mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam FROM peminjaman WHERE id_peminjaman = '$id'")); 
        $id_alat = $data['id_alat']; 
        $jumlah = $data['jumlah_pinjam']; 
 
        mysqli_query($this->db, "UPDATE peminjaman SET status = 'dipinjam' WHERE id_peminjaman = '$id'"); 
 
        return mysqli_query($this->db, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat = '$id_alat'"); 
    } 
 
    public function konfirmasi_kembali($id) { 
        $tgl_skrg = date('Y-m-d H:i:s');  
         
        $data = mysqli_fetch_array(mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam FROM peminjaman WHERE id_peminjaman = '$id'")); 
        $id_alat = $data['id_alat']; 
        $jumlah = $data['jumlah_pinjam']; 
 
        mysqli_query($this->db, "UPDATE peminjaman SET status = 'kembali', tgl_kembali_asli = '$tgl_skrg', kondisi_masuk = 'Baik' WHERE id_peminjaman = '$id'"); 
 
        return mysqli_query($this->db, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = '$id_alat'"); 
    } 
 
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
 
    public function update_pinjam($id_peminjaman, $jumlah_baru, $status_baru) { 
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
 
        $q_alat = mysqli_query($this->db, "SELECT stok FROM alat WHERE id_alat = '$id_alat'"); 
        $data_alat = mysqli_fetch_object($q_alat); 
        $stok_sekarang = (int)$data_alat->stok; 
 
        if ($status_lama == 'dipinjam' && $status_baru == 'pending') { 
            $stok_akhir = $stok_sekarang + $jumlah_lama; 
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'"); 
        } 
 
        elseif ($status_lama == 'pending' && $status_baru == 'dipinjam') { 
            if ($stok_sekarang < $jumlah_baru) { 
                return "stok_kurang"; 
            } 
            $stok_akhir = $stok_sekarang - $jumlah_baru; 
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'"); 
        } 
 
        elseif ($status_lama == 'dipinjam' && $status_baru == 'dipinjam') { 
            $selisih = $jumlah_baru - $jumlah_lama; 
 
            if ($selisih > 0) { 
                if ($stok_sekarang < $selisih) { 
                    return "stok_kurang"; 
                } 
                $stok_akhir = $stok_sekarang - $selisih; 
            } else { 
                $stok_akhir = $stok_sekarang + abs($selisih); 
            } 
             
            mysqli_query($this->db, "UPDATE alat SET stok = '$stok_akhir' WHERE id_alat = '$id_alat'"); 
        } 
 
        $query_update = "UPDATE peminjaman  
                         SET jumlah_pinjam = '$jumlah_baru',  
                             status = '$status_baru'  
                         WHERE id_peminjaman = '$id_peminjaman'"; 
 
        return mysqli_query($this->db, $query_update); 
    } 
 
    public function update_kembali($id, $kondisi, $tgl) { 
        return mysqli_query($this->db, "UPDATE peminjaman SET kondisi_masuk = '$kondisi', tgl_kembali_asli = '$tgl', status = 'kembali' WHERE id_peminjaman = '$id'"); 
    } 
 
    public function hapus_data($id) { 
        $query_get = mysqli_query($this->db, "SELECT id_alat, jumlah_pinjam, status FROM peminjaman WHERE id_peminjaman = '$id'"); 
        $data = mysqli_fetch_assoc($query_get); 
 
        if ($data) { 
            if ($data['status'] === 'dipinjam') { 
                $id_alat = $data['id_alat']; 
                $jumlah  = $data['jumlah_pinjam']; 
                mysqli_query($this->db, "UPDATE alat SET stok = stok + $jumlah WHERE id_alat = '$id_alat'"); 
            } 
 
            return mysqli_query($this->db, "DELETE FROM peminjaman WHERE id_peminjaman = '$id'"); 
        } 
 
        return false; 
    } 
 
    public function tampil_data_user($id_user) { 
        $sql = "SELECT p.*, a.nama_alat 
                FROM peminjaman p 
                JOIN alat a ON p.id_alat = a.id_alat 
                WHERE p.id_user = '$id_user' 
                ORDER BY p.id_peminjaman DESC"; 
        return mysqli_query($this->db, $sql); 
    } 
 
    public function tambah_pinjam($id_user, $id_alat, $jumlah, $kondisi) { 
        $tgl_skrg = date('Y-m-d H:i:s'); 
        $sql = "INSERT INTO peminjaman 
                (id_user, id_alat, jumlah_pinjam, tgl_pinjam, kondisi_keluar, status) 
                VALUES 
                ('$id_user', '$id_alat', '$jumlah', '$tgl_skrg', '$kondisi', 'pending')"; 
        return mysqli_query($this->db, $sql); 
    } 
 
    public function tambah_pinjam_admin($id_user, $id_alat, $jumlah, $kondisi = 'Baik', $jaminan = 'KTP') { 
        $q_stok = mysqli_query($this->db, "SELECT stok FROM alat WHERE id_alat = '$id_alat'"); 
        $stok_sekarang = mysqli_fetch_assoc($q_stok)['stok']; 
 
        if ($jumlah > $stok_sekarang) { 
            return "stok_kurang"; 
        } 
 
        $tgl_skrg = date('Y-m-d H:i:s'); 
         
        $sql = "INSERT INTO peminjaman 
                (id_user, id_alat, jumlah_pinjam, tgl_pinjam, kondisi_keluar, jaminan, status)  
                VALUES 
                ('$id_user', '$id_alat', '$jumlah', '$tgl_skrg', '$kondisi', '$jaminan', 'dipinjam')"; 
 
        $insert = mysqli_query($this->db, $sql); 
 
        if ($insert) { 
            mysqli_query($this->db, "UPDATE alat SET stok = stok - $jumlah WHERE id_alat = '$id_alat'"); 
            return true; 
        } 
 
        return false; 
    } 
 
    public function get_all_peminjam() { 
        return mysqli_query($this->db, "SELECT id_user, username FROM user WHERE role = 'peminjam' ORDER BY username ASC"); 
    } 
 
    public function get_all_alat() { 
        return mysqli_query($this->db, "SELECT id_alat, nama_alat, stok FROM alat WHERE stok > 0 ORDER BY nama_alat ASC"); 
    } 
} 
?>
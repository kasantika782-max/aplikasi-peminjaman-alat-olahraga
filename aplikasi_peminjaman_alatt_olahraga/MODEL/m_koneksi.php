<?php
class m_koneksi {
    private $host     = "localhost";
    private $username = "root";
    private $pass     = "";
    private $db       = "peminjaman_alat_olahraga"; 
    
    public $koneksi; 

    public function __construct() {
        // Melakukan koneksi ke database
        $this->koneksi = new mysqli($this->host, $this->username, $this->pass, $this->db);

        // Cek apakah ada error saat koneksi
        if ($this->koneksi->connect_error) {
            die("Koneksi database gagal: " . $this->koneksi->connect_error);
        }
    }

    // TAMBAHKAN FUNGSI INI agar bisa dipanggil oleh Controller/View
    public function hubungkan() {
        return $this->koneksi;
    }

    // Destruct sebaiknya tidak menutup koneksi terlalu cepat dalam pola MVC
    // Tapi jika ingin tetap ada, pastikan dicek dulu
    public function __destruct() {
        // Biarkan kosong atau hapus jika sering menyebabkan error "MySQL server has gone away"
    }
}





//fungsi ringing whattap
// ===================================================
// HELPER FUNCTION: FORMAT NOMOR TELEPON UNTUK WHATSAPP
// ===================================================
if (!function_exists('formatNomorWA')) {
    function formatNomorWA($nohp) {
        // Hapus semua karakter selain angka
        $nohp = preg_replace('/[^0-9]/', '', $nohp);
        
        // Jika diawali angka '0', ubah menjadi kode negara '62'
        if (substr($nohp, 0, 1) === '0') {
            $nohp = '62' . substr($nohp, 1);
        }
        
        return $nohp;
    }
}
?>
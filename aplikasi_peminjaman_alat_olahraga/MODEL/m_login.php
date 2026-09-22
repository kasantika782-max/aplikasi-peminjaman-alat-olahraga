<?php
class m_login {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Menerima 3 parameter: username, password, dan no_hp
    public function validasi_user($username, $password, $no_hp) {
        // Query menggunakan nama kolom 'no_hp'
        $sql = "SELECT * FROM user WHERE username = ? AND no_hp = ?";
        $stmt = $this->db->prepare($sql);
        
        // "ss" untuk 2 string (username & no_hp)
        $stmt->bind_param("ss", $username, $no_hp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_object();
            
            // Verifikasi password hash
            if (password_verify($password, $user->password)) {
                return $user; // Cocok semua!
            }
        }
        
        return false; // Jika username, no_hp, atau password ada yang salah
    }
}
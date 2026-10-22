<?php
class m_login {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    /**
     * Memeriksa username, nomor HP, dan password satu per satu
     * agar pesan kesalahan login lebih jelas.
     */
    public function cek_login($username, $password, $no_hp) {
        $sql = "SELECT * FROM user WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['user' => false, 'pesan' => 'Sistem tidak dapat memeriksa login. Coba lagi.'];
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['user' => false, 'pesan' => 'Username tidak ditemukan. Periksa kembali username Anda.'];
        }

        $user = $result->fetch_object();
        $stmt->close();

        if (!isset($user->no_hp) || trim((string)$user->no_hp) !== trim((string)$no_hp)) {
            return ['user' => false, 'pesan' => 'Nomor HP salah atau tidak sesuai dengan akun ini.'];
        }

        if (!password_verify($password, $user->password)) {
            return ['user' => false, 'pesan' => 'Password salah. Periksa kembali password Anda.'];
        }

        return ['user' => $user, 'pesan' => ''];
    }

    // Kompatibilitas jika fungsi lama digunakan di tempat lain.
    public function validasi_user($username, $password, $no_hp) {
        $hasil = $this->cek_login($username, $password, $no_hp);
        return $hasil['user'];
    }
}

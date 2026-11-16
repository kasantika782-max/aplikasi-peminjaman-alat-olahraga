
<?php

class m_login {

    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    /**
     * Memeriksa username dan password.
     */
    public function cek_login($username, $password) {

        $sql = "SELECT * FROM user WHERE username = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [
                'user' => false,
                'pesan' => 'Sistem tidak dapat memeriksa login. Coba lagi.'
            ];
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            $stmt->close();

            return [
                'user' => false,
                'pesan' => 'Username tidak ditemukan. Periksa kembali username Anda.'
            ];
        }

        $user = $result->fetch_object();

        $stmt->close();

        if (!password_verify($password, $user->password)) {

            return [
                'user' => false,
                'pesan' => 'Password salah. Periksa kembali password Anda.'
            ];
        }

        return [
            'user' => $user,
            'pesan' => ''
        ];
    }

    // Kompatibilitas fungsi lama.
    public function validasi_user($username, $password) {

        $hasil = $this->cek_login($username, $password);

        return $hasil['user'];
    }

}

admin
username : eka
password : 123
no hp    : 12345678

admin
username : arif
password : 123
no hp    : 11032009

petugas 
username : rizky
password : 123
no hp    : 1234567890

peminjam 
username : alfian
password : 123
no hp    : 1122334455

KEAMANAN VERSI FINAL
- Aksi yang mengubah data wajib POST + CSRF token.
- Logout wajib POST + CSRF token.
- Admin borrowing hanya untuk akun admin yang sedang login; id_user berasal dari session, jaminan dipaksa KTP, status langsung dipinjam, stok berkurang dalam transaksi.
- Registrasi publik selalu menjadi role peminjam.
- Model/database dan file sensitif diblokir oleh .htaccess.
- Semua controller tetap memeriksa role/session di server, bukan hanya menyembunyikan tombol di tampilan.

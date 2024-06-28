<?php
// Fungsi untuk membersihkan input dari potensi risiko SQL injection
function cleanInput($input)
{
    $search = array(
        '@<script[^>]*?>.*?</script>@si',   // Hapus script
        '@<[\/\!]*?[^<>]*?>@si',            // Hapus tag HTML
        '@<style[^>]*?>.*?</style>@siU',    // Hapus style tag
        '@<![\s\S]*?--[ \t\n\r]*>@'         // Hapus komentar
    );
    $output = preg_replace($search, '', $input);
    return $output;
}

session_start();

// Periksa apakah pengguna sudah login
if (isset($_SESSION['userid'])) {
    // Alihkan ke index.php jika sudah login
    header('Location: index.php');
    exit;
}

include 'konekke_local.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bersihkan input dari potensi risiko SQL injection
    $usernameOrFullname = cleanInput($_POST['usernameOrFullname']);
    $password = cleanInput($_POST['password']);

    // Query untuk mencari user berdasarkan username atau fullname
    $query = "SELECT userid, username, password, fullname, status FROM db_toko_amirul.users WHERE username = ? OR fullname = ?";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("ss", $usernameOrFullname, $usernameOrFullname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Memeriksa kecocokan password dengan hash yang disimpan
        if (password_verify($password, $row['password'])) {
            // Simpan informasi user ke dalam sesi
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['status'] = $row['status'];

            // Waktu saat ini sesuai zona waktu Asia/Jakarta
            date_default_timezone_set('Asia/Jakarta');
            $login_date = date('Y-m-d H:i:s');

            // Update login_date ke dalam database
            $updateQuery = "UPDATE db_toko_amirul.users SET login_date = ? WHERE userid = ?";
            $updateStmt = $koneklocalhost->prepare($updateQuery);
            $updateStmt->bind_param("si", $login_date, $row['userid']);
            $updateStmt->execute();

                // Menyesuaikan aliran navigasi berdasarkan peran pengguna
                if ($row['status'] == 'Admin' || $row['status'] == 'Karyawan') {
                    header('Location: index.php?page=dashboard'); // Ganti dengan alamat halaman admin jika peran adalah Admin
                } else {
                    header('Location: index.php?page=dashboard'); // Ganti dengan alamat halaman karyawan jika peran adalah Karyawan
                }
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<?php include 'title.php'; ?>

<body>
    <div class="content">
        <div class="col-md-6" align="center">
            <img src="img/e-absen.png" alt="Image" class="img-fluid" style="width:100%">
        </div>
        <div class="text">Login <span style="color:green">SI ABSENSI</span></div>
        <form action="#" method="post">
            <?php if (isset($error)) : ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="field">
                <input type="text" name="usernameOrFullname" required>
                <span class="fas fa-user"></span>
                <label>Username or Fullname</label>
            </div>
            <div class="field">
                <input type="password" name="password" required>
                <span class="fas fa-lock"></span>
                <label>Password</label>
            </div>
            <div class="forgot-pass">
                <a href="forgot_password.php?page=forgot_password">Forgot Password?</a>
            </div>
            <button type="submit">Sign in</button>
            <div class="sign-up">
                Not a member?
                <a href="signup.php?page=signup">Signup now</a>
            </div>
        </form>
    </div>
</body>

</html>

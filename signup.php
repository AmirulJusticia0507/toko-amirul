<?php
include 'konekke_local.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bersihkan input dari potensi risiko SQL injection
    $username = cleanInput($_POST['username']);
    $password = cleanInput($_POST['password']);
    $fullname = cleanInput($_POST['fullname']);
    $role = cleanInput($_POST['status']);

    // Hashing password dengan bcrypt dan salt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Waktu saat ini sesuai zona waktu Asia/Jakarta
    date_default_timezone_set('Asia/Jakarta');
    $created_at = date('Y-m-d H:i:s');

    // Query untuk menyimpan user baru ke database
    $query = "INSERT INTO db_toko_amirul.users (Username, PASSWORD, FullName, created_at, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("sssss", $username, $hashedPassword, $fullname, $created_at, $role);
    
    if ($stmt->execute()) {
        header('Location: login.php');
        exit;
    } else {
        $error = "Error creating user account";
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'title.php'; ?>
<body>
    <div class="content">
        <div class="col-md-6" align="center">
            <img src="img/e-absen.png" alt="Image" class="img-fluid" style="width:100%">
        </div>
        <div class="text">Signup <span style="color:green">SI ABSENSI</span></div>
        <form action="#" method="post" onsubmit="return validateForm()">
            <?php if (isset($error)) : ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="field">
                <input type="text" name="username" required style="width: 100%;">
                <span class="fas fa-user"></span>
                <label>Username</label>
            </div>
            <div class="field">
                <input type="password" name="password" required style="width: 100%;">
                <span class="fas fa-lock"></span>
                <label>Password</label>
            </div>
            <div class="field">
                <input type="text" name="fullname" required style="width: 100%;">
                <span class="fas fa-user"></span>
                <label>Fullname</label>
            </div>
            <div class="field">
                <label for="role">Role:</label>
                <select name="status" id="status" style="width: 100%;" class="form-select" required>
                    <option value="Admin">Admin</option>
                    <option value="Karyawan">Karyawan</option>
                </select>
            </div>
            <button type="submit">Signup</button>
            <div class="sign-up">
                Already a member?
                <a href="login.php">Sign in now</a>
            </div>
        </form>
    </div>
    <script>
        function validateForm() {
            var username = document.forms["signupForm"]["username"].value;
            var password = document.forms["signupForm"]["password"].value;
            var fullname = document.forms["signupForm"]["fullname"].value;
            if (username == "" || password == "" || fullname == "") {
                alert("Semua kolom harus diisi");
                return false;
            }
        }
    </script>
</body>
</html>

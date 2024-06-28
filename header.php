<?php
            // Mulai sesi jika belum ada
            // if (session_status() == PHP_SESSION_NONE) {
            //     session_start();
            // }
            ?>
<style>
    /* CSS Kustom untuk Header */
    .navbar-custom-menu {
        margin-right: 10px; /* Atur margin kanan sesuai kebutuhan */
    }

    .navbar-custom-menu .dropdown-menu {
        right: 0; /* Menggeser menu dropdown ke kanan */
        left: auto; /* Menonaktifkan penyesuaian ke kiri */
    }

    .user-details {
        padding: 10px;
    }
</style>

<div class="navbar-custom-menu ml-auto">
    <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
            <?php
            // Mulai sesi jika belum ada
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['userid'])) {
                // Sesi sudah ada, gunakan $_SESSION['user_id']
                $userID = $_SESSION['userid'];

                // Sisipkan koneksi.php
                // require 'koneksi_server.php';
                require 'konekke_local.php';

                $query = "SELECT username, fullname FROM db_toko_amirul.users WHERE userid = $userID";
                $result = $koneklocalhost->query($query);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $username = $row["username"];
                    $namalengkap = $row["fullname"];
                } else {
                    $username = "Nama Username";
                }

                echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
                echo '<span class="hidden-xs">' . $namalengkap . '</span>';
                echo '</a>';
            }
            ?>
            <ul class="dropdown-menu">
                <li class="user-details">
                    <p>Users: <strong><?php echo $namalengkap; ?></strong></p>
                </li>
                <li class="dropdown-footer">
                    <a href="karyawan.php?page=karyawan" class="btn btn-info">Profile</a>
                </li>
            </ul>
        </li>
    </ul>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        // Saat tombol dropdown di-klik
        $(".dropdown-toggle").click(function (e) {
            e.preventDefault(); // Mencegah tindakan default dari link
            $(this).next(".dropdown-menu").slideToggle(); // Menampilkan atau menyembunyikan dropdown menu
        });
    });
</script>

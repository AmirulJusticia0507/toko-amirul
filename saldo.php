<?php
include 'konekke_local.php';

// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    // Jika tidak ada sesi pengguna, alihkan ke halaman login
    header('Location: login.php');
    exit;
}

// Ambil user_id dari sesi
$user_id = $_SESSION['userid'];

// Ambil informasi pengguna dari database
$query = "SELECT fullname, no_hp, saldo, no_rekening FROM users WHERE userid = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullname = isset($row['fullname']) ? $row['fullname'] : '';
    $no_hp = isset($row['no_hp']) ? $row['no_hp'] : '';
    $saldo = isset($row['saldo']) ? $row['saldo'] : 0;
    $no_rekening = isset($row['no_rekening']) ? $row['no_rekening'] : '';

    // Jika no_rekening belum diatur, buat nomor rekening virtual baru
    if (empty($no_rekening)) {
        $no_rekening = '778' . $no_hp;
        $update_rekening_query = "UPDATE users SET no_rekening = ? WHERE userid = ?";
        $update_rekening_stmt = $koneklocalhost->prepare($update_rekening_query);
        $update_rekening_stmt->bind_param("si", $no_rekening, $user_id);
        $update_rekening_stmt->execute();
        $update_rekening_stmt->close();
    }
} else {
    // Handle case where user is not found (though it should not happen if authentication is correct)
    // Optionally redirect or show an error
    echo "User not found.";
    exit;
}

// Ambil daftar bank dari database
$query_banks = "SELECT kodeBank, namaBank FROM list_bank";
$stmt_banks = $koneklocalhost->prepare($query_banks);
$stmt_banks->execute();
$result_banks = $stmt_banks->get_result();


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_no_hp = $_POST['no_hp'];
    $additional_saldo = intval(str_replace('.', '', $_POST['saldo'])); // Ambil nilai tanpa tanda titik
    $new_saldo = $saldo + $additional_saldo; // Tambahkan nilai baru ke saldo yang ada

    // Update user data in the database
    $update_query = "UPDATE users SET no_hp = ?, saldo = ? WHERE userid = ?";
    $update_stmt = $koneklocalhost->prepare($update_query);
    $update_stmt->bind_param("sii", $new_no_hp, $new_saldo, $user_id);
    if ($update_stmt->execute()) {
        $message = "User data updated successfully.";
        // Update $saldo untuk menampilkan saldo yang terbaru
        $saldo = $new_saldo;
    } else {
        $message = "Error updating user data.";
    }
    $update_stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Saldo - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="img/amirulshop.png" type="image/png">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <?php include 'header.php'; ?>
        </nav>
        
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <!-- Konten Utama -->
            <main class="content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Saldo</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Informasi Saldo</h4>
                            <?php if (isset($message)): ?>
                                <div id="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                    <button id="closeAlert" type="button" class="btn-close" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="fullname" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">Nomor HP</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="saldo" class="form-label">Saldo</label>
                                    <input type="number" class="form-control" id="saldo" name="saldo" value="<?php echo htmlspecialchars($saldo); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="no_rekening" class="form-label">Nomor Rekening Virtual</label>
                                    <input type="text" class="form-control" id="no_rekening" value="<?php echo htmlspecialchars($no_rekening); ?>" disabled>
                                </div>
                                <button type="submit" class="btn btn-info"><i class="fas fa-paper-plane"></i> Simpan</button>
                            </form>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Saldo Terkini</span>
                                    <span id="saldoTerkini" class="info-box-number text-center text-muted mb-0"><?php echo "Rp " . number_format($saldo, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            <!-- <button type="button" class="btn btn-info mt-3" id="showTransferForm"><i class="fas fa-money-bill"></i> Transfer Ke Rekening</button> -->
                        </div>
                    </div>
                </div>
                
                <!-- <div class="card-body" id="transferFormWrapper" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Transfer Ke Rekening</h4>
                            <form id="transferForm">
                                <div class="mb-3">
                                    <label for="bank" class="form-label">Bank</label>
                                    <select class="form-select" id="bank" name="bank">
                                        <?php while ($bank = $result_banks->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($bank['kodeBank']); ?>"><?php echo htmlspecialchars($bank['namaBank']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="accountNumber" class="form-label">Nomor Rekening</label>
                                    <input type="text" class="form-control" id="accountNumber" name="accountNumber">
                                </div>
                                <div class="mb-3">
                                    <label for="accountHolder" class="form-label">Nama Pemegang Rekening</label>
                                    <input type="text" class="form-control" id="accountHolder" name="accountHolder" disabled>
                                </div>
                                <button type="button" class="btn btn-primary" id="checkAccount">Cek Rekening</button>
                            </form>
                        </div>
                    </div>
                </div> -->
            </main>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <!-- <script src="js/saldo.js"></script> -->
    <script>
        // Fungsi untuk memformat angka menjadi format mata uang Rupiah
        function formatRupiah(angka) {
            return numeral(angka).format('0,0'); // Format sesuai dengan kebutuhan (misalnya '0,0.00' untuk dua desimal)
        }

        // Ketika halaman dimuat, format saldo ke format Rupiah
        document.addEventListener("DOMContentLoaded", function() {
            var saldoInput = document.getElementById('saldo');
            saldoInput.value = formatRupiah(<?php echo $saldo; ?>);
            
            // Event listener untuk menghapus tanda titik saat form disubmit
            document.querySelector('form').addEventListener('submit', function() {
                saldoInput.value = saldoInput.value.replace(/\./g, "");
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#showTransferForm').click(function() {
                $('#transferFormWrapper').toggle('slow');
            });

            document.addEventListener("DOMContentLoaded", function() {
                function loadBankList() {
                    fetch('https://api-rekening.lfourr.com/listBank')
                        .then(response => response.json())
                        .then(data => {
                            console.log("Daftar Bank:", data); // Debug: Tampilkan daftar bank
                            const bankSelect = document.getElementById('bank');
                            data.forEach(bank => {
                                const option = document.createElement('option');
                                option.value = bank.code;
                                option.textContent = bank.name;
                                bankSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching bank list:', error));
                }

                function loadBankAccountInfo(bankCode, accountNumber) {
                    const url = `https://api-rekening.lfourr.com/getBankAccount?bankCode=${bankCode}&accountNumber=${accountNumber}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Informasi Rekening:", data); // Debug: Tampilkan informasi rekening
                            if (data && data.accountHolder) {
                                document.getElementById('accountHolder').value = data.accountHolder;
                            } else {
                                document.getElementById('accountHolder').value = 'Tidak ditemukan';
                            }
                        })
                        .catch(error => console.error('Error fetching bank account info:', error));
                }

                document.getElementById('checkAccount').addEventListener('click', function() {
                    const bankCode = document.getElementById('bank').value;
                    const accountNumber = document.getElementById('accountNumber').value;
                    console.log("Bank Code:", bankCode, "Account Number:", accountNumber); // Debug: Tampilkan input pengguna
                    if (bankCode && accountNumber) {
                        loadBankAccountInfo(bankCode, accountNumber);
                    }
                });

                loadBankList();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Tambahkan event click pada tombol pushmenu
            $('.nav-link[data-widget="pushmenu"]').on('click', function() {
                // Toggle class 'sidebar-collapse' pada elemen body
                $('body').toggleClass('sidebar-collapse');
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var closeAlertButton = document.getElementById('closeAlert');
            var alertMessage = document.getElementById('alertMessage');

            if (closeAlertButton && alertMessage) {
                closeAlertButton.addEventListener('click', function() {
                    alertMessage.remove(); // Menghapus elemen alert dari DOM
                });
            }
        });
    </script>

</body>
</html>

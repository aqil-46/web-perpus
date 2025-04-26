<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "user_db");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// LOGIN LOGIC
if (!isset($_SESSION['admin']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $koneksi->real_escape_string($_POST['username']);
    $password = $koneksi->real_escape_string($_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND PASSWORD='$password'";
    $result = $koneksi->query($query);

    if ($result && $result->num_rows === 1) {
        $_SESSION['admin'] = $username;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Update last_activity
if (isset($_SESSION['admin'])) {
    $admin_username = $_SESSION['admin'];
    $update_last_activity = "UPDATE user SET last_activity = NOW() WHERE user = '$admin_username'";
    $koneksi->query($update_last_activity);
}

// HAPUS USER
if (isset($_GET['hapus'])) {
    $hapus_username = $koneksi->real_escape_string($_GET['hapus']);
    if ($koneksi->query("DELETE FROM user WHERE USERNAME='$hapus_username'")) {
        $hapus_sukses = true;
    } else {
        $hapus_gagal = true;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?notif=hapus_" . ($hapus_sukses ? "sukses" : "gagal"));
    exit;
}

// UBAH PASSWORD USER
if (isset($_POST['ubah_password'])) {
    $target_username = $koneksi->real_escape_string($_POST['target_username']);
    $new_password = $koneksi->real_escape_string($_POST['new_password']);
    if ($koneksi->query("UPDATE user SET PASSWORD='$new_password' WHERE USERNAME='$target_username'")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?notif=ubah_sukses");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?notif=ubah_gagal");
    }
    exit;
}

if (!isset($_SESSION['admin'])):
?>

<!-- FORM LOGIN -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <style>
        body { font-family: Arial; background: #f4f4f9; }
        .login-box { width: 300px; margin: 100px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        input { width: 92%; padding: 10px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Admin</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username Admin" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

<?php
exit;
endif;

$admin = $_SESSION['admin'];
$search = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';

$query_total_akun = "SELECT COUNT(*) AS total FROM user";
$result_total_akun = $koneksi->query($query_total_akun);
$row_total_akun = $result_total_akun->fetch_assoc();
$total_akun = $row_total_akun['total'];

$query = "SELECT * FROM user WHERE USERNAME LIKE '%$search%' ORDER BY USERNAME ASC";
$result = $koneksi->query($query);
?>

<!-- DASHBOARD -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial; background-color: #f4f4f9; margin: 0; padding: 0; }
        .container { width: 90%; margin: 0 auto; padding: 20px; }
        header {
            background-color: #4CAF50; color: white; padding: 10px 20px;
            text-align: center; border-radius: 5px; position: relative;
        }
        .greeting {
            position: absolute; top: 10px; right: 20px;
        }
        a.logout { color: yellow; text-decoration: none; margin-left: 10px; }
        .search-form {
            display: flex; justify-content: center; margin: 20px 0;
        }
        input[type="text"], input[type="password"] {
            padding: 8px; margin-right: 10px; border-radius: 4px; border: 1px solid #ccc;
        }
        button {
            padding: 6px 10px; font-size: 0.9em; background-color: #4CAF50;
            color: white; border: none; border-radius: 5px; cursor: pointer;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .no-results { text-align: center; font-style: italic; }

        /* Email column settings */
        td.email-column {
            width: 15%;
        }

        /* Hover effect to show full email */
        td.email-column:hover {
            width: normal; /* Allow the email to wrap */
            overflow: visible;   /* Make it expand */
        }

        @media (max-width: 768px) {
            header h2 { font-size: 1.5em; }
            .container { width: 100%; padding: 10px; }
            table th, table td { font-size: 0.9em; padding: 8px; }
        }

        @media (max-width: 480px) {
            header h2 { font-size: 1.2em; }
            table th, table td { font-size: 0.8em; padding: 6px; }
            button { font-size: 0.8em; padding: 6px 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div class="greeting">
            Halo Admin, <?php echo htmlspecialchars($admin); ?>!
            <a class="logout" href="?logout=1">Logout</a>
        </div>
        <h2>DATA LOGIN USER</h2>
        <p>Total Akun: <?php echo $total_akun; ?></p>
    </header>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Cari username..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>USERNAME</th>
                <th>PASSWORD</th>
                <th class="email-column">EMAIL</th>
                <th>Tanggal Buat</th>
                <th>Terakhir Online</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                $tanggal_buat = date('d-m-Y H:i', strtotime($row['created_at']));
                $last_activity = $row['last_activity'] ? date('d-m-Y H:i', strtotime($row['last_activity'])) : '-';
                echo "<tr>
                        <td>$no</td>
                        <td>" . htmlspecialchars($row['USERNAME']) . "</td>
                        <td>" . htmlspecialchars($row['PASSWORD']) . "</td>
                        <td class='email-column'>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($tanggal_buat) . "</td>
                        <td>" . htmlspecialchars($last_activity) . "</td>
                        <td>
                            <form method='POST' style='display:inline-block; margin-bottom:5px;'>
                                <input type='hidden' name='target_username' value='" . htmlspecialchars($row['USERNAME']) . "'>
                                <input style='width: 40%;' type='password' name='new_password' placeholder='Password Baru' required>
                                <button type='submit' name='ubah_password'>Ubah</button>
                            </form>
                            <a href='?hapus=" . urlencode($row['USERNAME']) . "' onclick=\"return confirm('Yakin ingin menghapus user ini?')\">
                                <button style='background-color:red; margin-left:-60px; height:100%; color:white; padding:8px 12px; border:none; border-radius:5px;'>Hapus</button>
                            </a>
                        </td>
                    </tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='7' class='no-results'>Data tidak ditemukan</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php if (isset($_GET['notif'])): ?>
<script>
    let notif = "<?php echo $_GET['notif']; ?>";
    if (notif === "ubah_sukses") {
        Swal.fire('Berhasil!', 'Password berhasil diubah!', 'success');
    } else if (notif === "ubah_gagal") {
        Swal.fire('Gagal!', 'Password gagal diubah!', 'error');
    } else if (notif === "hapus_sukses") {
        Swal.fire('Berhasil!', 'User berhasil dihapus!', 'success');
    } else if (notif === "hapus_gagal") {
        Swal.fire('Gagal!', 'User gagal dihapus!', 'error');
    }
</script>
<?php endif; ?>
</body>
</html>

<?php $koneksi->close(); ?>

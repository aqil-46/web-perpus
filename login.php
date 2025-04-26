<?php
session_start();
include 'connect.php';

// if (isset($_SESSION['username'])) {
//     header("Location: dash.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT password, role FROM users WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // NON-HASH PASSWORD CHECK
        if ($password === $row['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                $redirect = 'admin_revisi.php';
            } else {
                $redirect = 'dash.php';
            }

            $message = ['type' => 'success', 'text' => 'Login berhasil!'];
        } else {
            $message = ['type' => 'error', 'text' => 'Password salah!'];
        }
    } else {
        $message = ['type' => 'error', 'text' => 'Username tidak ditemukan!'];
    }
    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LOGIN WEB</title>
  <link rel="stylesheet" href="login.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
  <style>
    .toggle-password {
      margin-top: -10px;
      font-size: 12px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="login">
      <form action="" method="POST">
        <h1>Login</h1>
        <hr><br>
        <p>PERPUSTAKAAN</p>

        <label for="username">Username</label>
        <input type="text" name="username" placeholder="Username" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>

        <button type="submit">Login</button>
        <p>Belum punya akun? <a href="register.php">Daftar</a></p>
      </form>
    </div>
    <div class="right">
      <img class="gambar" src="perp.png" width="300px">
    </div>
  </div>

<?php if (!empty($message)) : ?>
  <script>
    Swal.fire({
      icon: '<?= $message['type'] ?>',
      title: '<?= $message['type'] === "success" ? "Sukses!" : "Gagal!" ?>',
      text: '<?= $message['text'] ?>',
      showConfirmButton: false,
      timer: 1500,
      didClose: () => {
        <?php if (isset($redirect)) : ?>
          window.location = '<?= $redirect ?>';
        <?php endif; ?>
      }
    });
  </script>
<?php endif; ?>
</body>
</html>

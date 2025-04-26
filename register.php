<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Tidak di-hash

    $check_email = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = ['type' => 'error', 'text' => 'Email sudah terdaftar!'];
    } else {
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Registrasi berhasil!'];
            $redirect = 'login.php';
        } else {
            $message = ['type' => 'error', 'text' => 'Terjadi kesalahan!'];
        }
    }
    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <link rel="stylesheet" href="login.css" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="login">
      <form action="" method="POST">
        <h1>Register</h1>
        <hr />
        <p>PERPUSTAKAAN</p>

        <label for="username">Username</label>
        <input type="text" name="username" placeholder="Username" required />

        <label for="password">Password</label>
        <input type="password" name="password" placeholder="Password" required />

        <label for="email">Email</label>
        <input type="email" name="email" placeholder="example@gmail.com" required />

        <button type="submit">Register</button>
        <p>Sudah punya akun? <a href="index.php">Login</a></p>
      </form>
    </div>

    <div class="right">
      <img class="gambar" src="perp.png" alt="Perpustakaan" />
    </div>
  </div>

  <?php if (!empty($message)) : ?>
    <script>
      Swal.fire({
        icon: '<?= $message['type'] ?>',
        title: '<?= $message['type'] === "success" ? "Sukses!" : "Gagal!" ?>',
        text: '<?= $message['text'] ?>',
        confirmButtonText: 'OK'
      }).then(() => {
        <?php if (isset($redirect)) : ?>
          window.location = '<?= $redirect ?>';
        <?php endif; ?>
      });
    </script>
  <?php endif; ?>
</body>
</html>

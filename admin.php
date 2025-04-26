<?php
session_start();
include 'connect.php';

// // Cek login
// if (!isset($_SESSION['username'])) {
//     header("Location: login.php");
//     exit();
// }

// Tambah buku
if (isset($_POST['add_book'])) {
    $judul_buku = $_POST['judul_buku'];
    $nama_pengarang = $_POST['nama_pengarang'];
    $penerbit = $_POST['penerbit'];
    $id_kategori = $_POST['id_kategori'];

    $query = "INSERT INTO buku (judul_buku, nama_pengarang, penerbit, id_kategori) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $judul_buku, $nama_pengarang, $penerbit, $id_kategori);
    $stmt->execute();
    $stmt->close();

    $message = ['type' => 'success', 'text' => 'Buku berhasil ditambahkan!'];
}

// Hapus buku
if (isset($_POST['delete'])) {
    $id_buku = $_POST['no_urut'];
    $query = "DELETE FROM buku WHERE no_urut = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_buku);
    $stmt->execute();
    $stmt->close();

    $message = ['type' => 'success', 'text' => 'Buku berhasil dihapus!'];
}

// Ambil data buku
$query = "SELECT * FROM buku";
$result = $conn->query($query);

if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"], select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #303f9f;
        }
        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #3f51b5;
            color: white;
        }
        td a {
            color: #007bff;
            text-decoration: none;
        }
        td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Admin Perpustakaan</h1>
        <h2>Tambah Buku</h2>
        <form method="POST">
            <label for="judul_buku">Judul Buku</label>
            <input type="text" name="judul_buku" required>

            <label for="nama_pengarang">Nama Pengarang</label>
            <input type="text" name="nama_pengarang" required>

            <label for="penerbit">Penerbit</label>
            <input type="text" name="penerbit" required>

            <label for="id_kategori">ID Kategori</label>
            <input type="number" name="id_kategori" required>

            <label for="no_urut">No Urut</label>
            <input type="number" name="no_urut" required>

            <button type="submit" name="add_book">Tambah Buku</button>
        </form>

        <h2>Daftar Buku</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Nama Pengarang</th>
                    <th>Penerbit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_kategori']) ?></td>
                            <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                            <td><?= htmlspecialchars($row['nama_pengarang']) ?></td>
                            <td><?= htmlspecialchars($row['penerbit']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_kategori" value="<?= htmlspecialchars($row['id_kategori']) ?>">
                                    <button type="submit" name="delete" onclick="return confirm('Yakin ingin menghapus buku ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Belum ada data buku.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($message)) : ?>
        <script>
            Swal.fire({
                icon: '<?= $message['type'] ?>',
                title: '<?= $message['type'] === "success" ? "Sukses!" : "Gagal!" ?>',
                text: '<?= $message['text'] ?>',
                showConfirmButton: false,
                timer: 1500,
                didClose: () => {
                    location.href = window.location.pathname;
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>

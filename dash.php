<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "user_db");

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$username = $_SESSION['username'] ?? 'Guest';

// Tangkap input pencarian
$search = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';

// Query pencarian      
$query = "SELECT * FROM buku WHERE judul_buku LIKE '%$search%' OR nama_pengarang LIKE '%$search%' ORDER BY penerbit ASC";
$result = $koneksi->query($query);

// **Tambahkan pengecekan error**
if (!$result) {
    die("Query error: " . $koneksi->error);
}

// Logic Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php'); // Redirect ke halaman login setelah logout
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perpustakaan</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Menambahkan SweetAlert CDN -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
        }

        .greeting {
            float: right;
            font-size: 1.1em;
            margin-top: 10px;
        }

        h2 {
            color: white;
        }

        form {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 1em;
            width: 70%;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .no-results {
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div class="greeting">
            <!-- Menambahkan tombol logout yang bisa diklik -->
            <span style="cursor: pointer;" id="logout-button"><div class="greeting">Halo, <?php echo htmlspecialchars($username); ?>!</div></span>
        </div>
        <h2>Perpustakaan Nasional</h2>
    </header>

    <form method="GET">
        <input type="text" name="search" placeholder="Cari buku..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Buku</th>
                <th>Penulis</th>
                <th>Penerbit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['JUDUL_BUKU']}</td>
                            <td>{$row['NAMA_PENGARANG']}</td>
                            <td>{$row['PENERBIT']}</td>
                          </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='4' class='no-results'>Buku tidak ditemukan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    // Menambahkan SweetAlert untuk konfirmasi logout saat klik nama
    document.getElementById('logout-button').addEventListener('click', function () {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan logout dari akun ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, logout',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika konfirmasi logout, redirect ke URL logout
                window.location.href = "?logout=true";
            }
        });
    });
</script>

</body>
</html>

<?php $koneksi->close(); ?>

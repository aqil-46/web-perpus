<?php
session_start();
include 'connect.php';

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Variabel awal
$edit_mode = false;
$edit_data = [
    'KODE_BUKU' => '',
    'JUDUL_BUKU' => '',
    'NAMA_PENGARANG' => '',
    'PENERBIT' => '',
    'ID_KATEGORI' => ''
];

// Tambah buku
if (isset($_POST['add_book'])) {
    $judul_buku = $_POST['judul_buku'];
    $nama_pengarang = $_POST['nama_pengarang'];
    $penerbit = $_POST['penerbit'];
    $id_kategori = strtoupper($_POST['id_kategori']);

    // Cari NO_URUT terakhir berdasarkan ID_KATEGORI
    $query = "SELECT MAX(NO_URUT) AS max_urut FROM buku WHERE ID_KATEGORI = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $no_urut_baru = ($row['max_urut'] ?? 0) + 1;

    // Generate KODE_BUKU baru
    $kode_buku = $id_kategori . str_pad($no_urut_baru, 4, '0', STR_PAD_LEFT);

    // Simpan ke database
    $query = "INSERT INTO buku (KODE_BUKU, JUDUL_BUKU, NAMA_PENGARANG, PENERBIT, ID_KATEGORI, NO_URUT) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $kode_buku, $judul_buku, $nama_pengarang, $penerbit, $id_kategori, $no_urut_baru);
    $stmt->execute();
    $stmt->close();

    $message = ['type' => 'success', 'text' => 'Buku berhasil ditambahkan!'];
}

// Edit: Ambil data
if (isset($_POST['edit']) && isset($_POST['kode_buku'])) {
    $kode_buku = $_POST['kode_buku'];

    $query = "SELECT * FROM buku WHERE KODE_BUKU = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $kode_buku);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        $message = ['type' => 'error', 'text' => 'Data tidak ditemukan!'];
    }
    $stmt->close();
}

// Update buku
if (isset($_POST['update'])) {
    $kode_buku = $_POST['kode_buku'];
    $judul_buku = $_POST['judul_buku'];
    $nama_pengarang = $_POST['nama_pengarang'];
    $penerbit = $_POST['penerbit'];
    $id_kategori = strtoupper($_POST['id_kategori']);

    $query = "UPDATE buku SET JUDUL_BUKU = ?, NAMA_PENGARANG = ?, PENERBIT = ?, ID_KATEGORI = ? WHERE KODE_BUKU = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $judul_buku, $nama_pengarang, $penerbit, $id_kategori, $kode_buku);
    $stmt->execute();
    $stmt->close();

    $message = ['type' => 'success', 'text' => 'Buku berhasil diupdate!'];
}

// Hapus buku
if (isset($_POST['delete']) && isset($_POST['kode_buku'])) {
    $kode_buku = $_POST['kode_buku'];

    $query = "DELETE FROM buku WHERE KODE_BUKU = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $kode_buku);
    $stmt->execute();
    $stmt->close();

    $message = ['type' => 'success', 'text' => 'Buku berhasil dihapus!'];
}

// Ambil semua data buku
$query = "SELECT * FROM buku ORDER BY ID_KATEGORI ASC, NO_URUT ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body style="background: #f8f9fa;">
<div class="container mt-5">
    <h2 class="text-center mb-4">📚 Dashboard Admin - Data Buku</h2>

    <!-- Form Buku -->
    <div class="card shadow p-4 mb-5 bg-white rounded">
        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="kode_buku" value="<?= htmlspecialchars($edit_data['KODE_BUKU']) ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="judul_buku" class="form-control" placeholder="Judul Buku" required value="<?= htmlspecialchars($edit_data['JUDUL_BUKU'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nama_pengarang" class="form-control" placeholder="Pengarang" required value="<?= htmlspecialchars($edit_data['NAMA_PENGARANG'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="penerbit" class="form-control" placeholder="Penerbit" required value="<?= htmlspecialchars($edit_data['PENERBIT'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="id_kategori" class="form-control" placeholder="ID Kategori" required value="<?= htmlspecialchars($edit_data['ID_KATEGORI'] ?? '') ?>">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="<?= $edit_mode ? 'update' : 'add_book' ?>" class="btn btn-<?= $edit_mode ? 'warning' : 'primary' ?>">
                        <?= $edit_mode ? 'Update Buku' : 'Tambah Buku' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Buku -->
    <div class="card shadow p-4">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr class="text-center">
                    <th>No</th>
                    <th>Kode Buku</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>ID Kategori</th>
                    <th>No Urut</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['KODE_BUKU']) ?></td>
                    <td><?= htmlspecialchars($row['JUDUL_BUKU']) ?></td>
                    <td><?= htmlspecialchars($row['NAMA_PENGARANG']) ?></td>
                    <td><?= htmlspecialchars($row['PENERBIT']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['ID_KATEGORI']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['NO_URUT']) ?></td>
                    <td class="text-center">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="kode_buku" value="<?= htmlspecialchars($row['KODE_BUKU']) ?>">
                            <button type="submit" name="edit" class="btn btn-sm btn-warning">Edit</button>
                        </form>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="kode_buku" value="<?= htmlspecialchars($row['KODE_BUKU']) ?>">
                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Yakin mau hapus buku ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isset($message)): ?>
<script>
Swal.fire({
    icon: '<?= $message['type'] ?>',
    title: '<?= $message['text'] ?>',
    showConfirmButton: false,
    timer: 2000
});
</script>
<?php endif; ?>

</body>
</html>

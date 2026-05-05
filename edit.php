<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];

    // Ambil ID dari GET
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: index.php?error=" . urlencode("ID tidak valid"));
        exit();
    }
    
    $id = (int)$_GET['id']; // ambil id dari URL

    // Retrieve data berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?"); // ambil data lama
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt->close();
        header("Location: index.php?error=" . urlencode("Data tidak ditemukan"));
        exit();
    }

    $data = $result->fetch_assoc();
    $stmt->close();

    // Set default value (pre-fill)
    $kode = $data['kode_kategori'];
    $nama = $data['nama_kategori'];
    $deskripsi = $data['deskripsi'];
    $status = $data['status'];

    // Jika POST, proses update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Ambil & sanitasi
        $kode = htmlspecialchars(trim($_POST['kode']));
        $nama = htmlspecialchars(trim($_POST['nama']));
        $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
        $status = isset($_POST['status']) ? $_POST['status'] : 'Aktif';

       // cek input lagi
        if (empty($kode)) {
            $errors[] = "Kode kategori wajib diisi";
        } elseif (strlen($kode) < 4 || strlen($kode) > 10) {
            $errors[] = "Kode harus 4-10 karakter";
        } elseif (!preg_match('/^KAT-/', $kode)) {
            $errors[] = "Kode harus diawali KAT-";
        }

        // Validasi nama
        if (empty($nama)) {
            $errors[] = "Nama kategori wajib diisi";
        } elseif (strlen($nama) < 3) {
            $errors[] = "Nama minimal 3 karakter";
        } elseif (strlen($nama) > 50) {
            $errors[] = "Nama maksimal 50 karakter";
        }

        // Validasi deskripsi
        if (!empty($deskripsi) && strlen($deskripsi) > 200) {
            $errors[] = "Deskripsi maksimal 200 karakter";
        }

        // Validasi status
        if ($status != 'Aktif' && $status != 'Nonaktif') {
            $errors[] = "Status tidak valid";
        }

        // Cek duplikasi (exclude diri sendiri)
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id_kategori FROM kategori WHERE kode_kategori = ? AND id_kategori != ?");
            $stmt->bind_param("si", $kode, $id);
            $stmt->execute();
            $cek = $stmt->get_result();

            if ($cek->num_rows > 0) {
                $errors[] = "Kode kategori sudah digunakan";
            }
            $stmt->close();
        }

        // Jika tidak ada error → update
        if (count($errors) == 0) {
            $stmt = $conn->prepare("UPDATE kategori SET kode_kategori=?, nama_kategori=?, deskripsi=?, status=? WHERE id_kategori=?"); // update data
            $stmt->bind_param("ssssi", $kode, $nama, $deskripsi, $status, $id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?success=" . urlencode("Data berhasil diupdate"));
                exit();
            } else {
                $errors[] = "Gagal update: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Kategori</h4>
                    </div>
                    <div class="card-body">

                        <!-- Form dengan data pre-filled -->
                        <?php if (count($errors) > 0): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $e): ?>
                                        <li><?= $e; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Kode Kategori</label>
                                <input type="text" name="kode" class="form-control" value="<?= $kode; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Kategori</label>
                                <input type="text" name="nama" class="form-control" value="<?= $nama; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control"><?= $deskripsi; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label><br>
                                <input type="radio" name="status" value="Aktif" <?= ($status == 'Aktif') ? 'checked' : ''; ?>> Aktif
                                <input type="radio" name="status" value="Nonaktif" <?= ($status == 'Nonaktif') ? 'checked' : ''; ?>> Nonaktif
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">Update</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
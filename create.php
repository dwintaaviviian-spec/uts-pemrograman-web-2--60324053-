<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];
    $kode = '';
    $nama = '';
    $deskripsi = '';
    $status = 'Aktif';
    
    // ambil data dari form
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        
        $kode = htmlspecialchars(trim($_POST['kode'])); // Ambil dan sanitasi data dari form
        $nama = htmlspecialchars(trim($_POST['nama']));
        $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
        $status = isset($_POST['status']) ? $_POST['status'] : 'Aktif';

        // cek inputan
        if (empty($kode)) {
            $errors[] = "Kode kategori wajib diisi";
        } elseif (strlen($kode) < 4 || strlen($kode) > 10) {
            $errors[] = "Kode kategori harus 4-10 karakter";
        } elseif (!preg_match('/^KAT-/', $kode)) {
            $errors[] = "Kode harus diawali dengan 'KAT-'";
        }

        // Validasi nama kategori
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

        // Cek duplikasi kode
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id_kategori FROM kategori WHERE kode_kategori = ?");  // cek kode sudah dipakai atau belum
            $stmt->bind_param("s", $kode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Kode kategori sudah digunakan";
            }
            $stmt->close();
        }

        // Jika tidak ada error, insert data
        if (count($errors) == 0) {
            $stmt = $conn->prepare("INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi, status) VALUES (?, ?, ?, ?)"); // simpan ke database
            $stmt->bind_param("ssss", $kode, $nama, $deskripsi, $status);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?success=" . urlencode("Kategori berhasil ditambahkan"));
                exit();
            } else {
                $errors[] = "Gagal menyimpan data: " . $stmt->error;
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
                        <h4>Tambah Kategori Baru</h4>
                    </div>
                    <div class="card-body">

                        <!-- Tampilkan error jika ada -->
                        <?php if (count($errors) > 0): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <!-- Form fields -->
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
                                <button type="submit" class="btn btn-primary">Simpan</button>
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
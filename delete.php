<?php
require_once 'config/database.php';
 
// Validasi ID dari GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=" . urlencode("ID tidak valid"));
    exit();
}

$id = (int)$_GET['id']; // ambil id
 
// Cek keberadaan data
$stmt = $conn->prepare("SELECT id_kategori FROM kategori WHERE id_kategori = ?"); // cek datanya ada atau nggak
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    header("Location: index.php?error=" . urlencode("Data tidak ditemukan"));
    exit();
}
$stmt->close();
 
// Delete data
$stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?"); // hapus data
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        header("Location: index.php?success=" . urlencode("Data berhasil dihapus"));
        exit();
    } else {
        $stmt->close();
        header("Location: index.php?error=" . urlencode("Gagal menghapus data"));
        exit();
    }
} else {
    $error = $stmt->error;
    $stmt->close();
    header("Location: index.php?error=" . urlencode("Error: $error"));
    exit();
}
?>
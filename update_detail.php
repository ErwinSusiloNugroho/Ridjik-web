<?php
require 'koneksi.php'; // sesuaikan koneksi DB-mu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_detail_transaksi'] ?? null;
    $produk = $_POST['produk'] ?? null;
    $jumlah = $_POST['jumlah'] ?? null;
    $harga = $_POST['harga'] ?? null;

    if ($id && $produk && $jumlah && $harga) {
        $id = (int)$id;
        $jumlah = (int)$jumlah;
        $harga = (int)$harga;
        $produk = $conn->real_escape_string($produk);

        $sql = "UPDATE detail_transaksi SET produk='$produk', jumlah=$jumlah, harga=$harga WHERE id_detail_transaksi=$id";
        $conn->query($sql);
    }

    // Redirect ke halaman transaksi dengan opsi tanggal jika ada supaya reload
    $redirectUrl = 'transaksi.php';
    if (!empty($_GET['tanggal'])) {
        $redirectUrl .= '?tanggal=' . urlencode($_GET['tanggal']);
    }
    header("Location: $redirectUrl");
    exit;
}
?>

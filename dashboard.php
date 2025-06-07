<?php
include 'koneksi.php';

// Total Produk
$total_produk_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk");
$total_produk = mysqli_fetch_assoc($total_produk_result)['total'];

// Total Stok
$stok_result = mysqli_query($conn, "SELECT SUM(stok_produk) AS total_stok FROM produk");
$total_stok = mysqli_fetch_assoc($stok_result)['total_stok'];

// Total Transaksi
$transaksi_result = mysqli_query($conn, "SELECT COUNT(*) AS total_transaksi FROM transaksi");
$total_transaksi = mysqli_fetch_assoc($transaksi_result)['total_transaksi'];

// Produk Terbaru
$produk_terbaru_result = mysqli_query($conn, "SELECT nama_produk, kode_produk, stok_produk FROM produk ORDER BY id_produk DESC LIMIT 5");

// Transaksi Terbaru
$query = "
    SELECT 
    t.id_transaksi, 
    t.tanggal, 
    td.total,
    GROUP_CONCAT(CONCAT(dt.produk, ' ', dt.jumlah, ' item') SEPARATOR ', ') AS daftar_produk
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN (
        SELECT id_transaksi, SUM(jumlah * harga) AS total
        FROM detail_transaksi
        GROUP BY id_transaksi
    ) td ON t.id_transaksi = td.id_transaksi
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal DESC
    LIMIT 5;
";
$transaksi_terbaru_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | RIDJIK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="flex">

    <!-- Sidebar -->
    <aside class="sidebar bg-gray-800 text-white p-6 w-64 min-h-screen overflow-hidden">
        <h1 class="text-2xl font-bold mb-10">RIDJIK</h1>
        <nav class="flex flex-col space-y-5">
            <a href="dashboard.php" class="nav-link">
                <span>🏠</span> 
                <span>Dashboard</span>
            </a>
            <a href="produk.php" class="nav-link"><span>📦</span> <span>Produk</span></a>
            <a href="bahan.php" class="nav-link">
                <span>🗃️</span> 
                <span>Bahan</span>
            </a>
            <a href="transaksi.php" class="nav-link">
                <span>📈</span> 
                <span>Transaksi</span>
            </a>
            <a href="resep.php" class="nav-link">
                <span>📋</span>
                <span>Resep</span>
            </a>
            <a href="#" class="nav-link">
                <span>📑</span> 
                <span>Laporan</span>
            </a>
            <a href="login.php" class="nav-link">
                <span>🚪</span> 
                <span>Log Out</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content p-10 w-full">
        <h2 class="text-3xl font-bold mb-8">Dashboard</h2>
        <!-- Cards -->
       <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <a href="produk.php" class="group">
        <div class="bg-white p-6 rounded-lg shadow text-center transition duration-300 group-hover:bg-gray-100 cursor-pointer">
            <div class="text-blue-500 text-4xl mb-2">🛍️</div>
            <h3 class="text-lg font-semibold">Total Produk</h3>
            <p class="text-2xl font-bold mt-2"><?= $total_produk ?></p>
        </div>
        </a>

            <div class="bg-white p-6 rounded-lg shadow text-center hover:bg-gray-100 transition duration-300 cursor-pointer">
                <div class="text-green-500 text-4xl mb-2">📦</div>
                <h3 class="text-lg font-semibold">Stok Tersedia</h3>
                <p class="text-2xl font-bold mt-2"><?= number_format($total_stok) ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow text-center hover:bg-gray-100 transition duration-300 cursor-pointer">
                <div class="text-orange-500 text-4xl mb-2">📈</div>
                <h3 class="text-lg font-semibold">Total Transaksi</h3>
                <p class="text-2xl font-bold mt-2"><?= $total_transaksi ?></p>
            </div>
        </div>


        <!-- Produk dan Transaksi Terbaru -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Produk Terbaru -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Produk Terbaru</h3>
                <?php if (mysqli_num_rows($produk_terbaru_result) > 0): ?>
                    <ul class="space-y-2">
                        <?php while($produk = mysqli_fetch_assoc($produk_terbaru_result)): ?>
                            <li class="border-b pb-2">
                                <strong><?= htmlspecialchars($produk['nama_produk']) ?></strong><br>
                                Kode: <?= htmlspecialchars($produk['kode_produk']) ?> | Stok: <?= $produk['stok_produk'] ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">Belum ada data produk</p>
                <?php endif; ?>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Transaksi Terkini</h3>
                <?php if (mysqli_num_rows($transaksi_terbaru_result) > 0): ?>
                    <ul class="space-y-4">
                        <?php while($trx = mysqli_fetch_assoc($transaksi_terbaru_result)): ?>
                            <li class="border-b pb-2">
                                <strong><?= $trx['daftar_produk'] ?> </strong> <br>
                                Tanggal: <?= date('d/m/Y', strtotime($trx['tanggal'])) ?><br>
                                Total: Rp<?= number_format($trx['total'], 0, ',', '.') ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">Belum ada data transaksi</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</div>
</body>
</html>

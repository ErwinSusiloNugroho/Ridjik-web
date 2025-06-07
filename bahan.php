<?php
include 'koneksi.php';

$tampilkan_form = false; // default form tambah bahan tidak muncul

// Ambil bahan dari database
$query = "SELECT * FROM bahan";
$result = mysqli_query($conn, $query);

// Proses simpan laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tanggal']) && isset($_POST['stok_laporan'])) {
        $tanggal = $_POST['tanggal'];
        $stok_laporan = $_POST['stok_laporan'];

        foreach ($stok_laporan as $id_bahan => $stok) {
            $tanggal_esc = mysqli_real_escape_string($conn, $tanggal);
            $id_bahan_esc = (int)$id_bahan;
            $stok_esc = (int)$stok;

            $sql = "INSERT INTO laporan_stok (id_bahan, tanggal, stok)
                    VALUES ('$id_bahan_esc', '$tanggal_esc', '$stok_esc')";
            mysqli_query($conn, $sql);
        }
        $pesan = "Data berhasil disimpan ke laporan!";
    }

    // Proses tambah bahan baru
    if (isset($_POST['nama_bahan'], $_POST['stok_bahan'], $_POST['nama_suppliyer'], $_POST['harga'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
        $stok = (int)$_POST['stok_bahan'];
        $suppliyer = mysqli_real_escape_string($conn, $_POST['nama_suppliyer']);
        $harga = (int)$_POST['harga'];
        
        // Generate kode_bahan yang unik
        do {
            $kode_bahan = rand(10000, 99999);
            $check_query = "SELECT COUNT(*) as count FROM bahan WHERE kode_bahan = '$kode_bahan'";
            $check_result = mysqli_query($conn, $check_query);
            $check_row = mysqli_fetch_assoc($check_result);
        } while ($check_row['count'] > 0);

        // Generate ID bahan manual karena tidak ada AUTO_INCREMENT
        $max_id_query = "SELECT COALESCE(MAX(id_bahan), 0) + 1 as next_id FROM bahan";
        $max_id_result = mysqli_query($conn, $max_id_query);
        $max_id_row = mysqli_fetch_assoc($max_id_result);
        $next_id = $max_id_row['next_id'];

        // Insert dengan id_bahan yang sudah dihitung
        $sql = "INSERT INTO bahan (id_bahan, kode_bahan, nama_bahan, nama_suppliyer, stok_bahan, harga)
                VALUES ('$next_id', '$kode_bahan', '$nama', '$suppliyer', '$stok', '$harga')";
        
        if (mysqli_query($conn, $sql)) {
            $pesan = "Bahan baru berhasil ditambahkan!";
            $tampilkan_form = true;
            $result = mysqli_query($conn, $query);
        } else {
            $pesan = "Gagal menambahkan bahan baru: " . mysqli_error($conn);
            $tampilkan_form = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Bahan | RIDJIK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="style.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 h-screen">
<div class="flex h-full overflow-auto">
    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-gray-800 text-white p-6">
        <button id="toggleSidebar" class="toggle-button">☰</button>
        <h1 class="text-2xl font-bold mb-10">RIDJIK</h1>
        <nav class="flex flex-col space-y-5">
            <a href="dashboard.php" class="nav-link">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="produk.php" class="nav-link">
                <span>📦</span>
                <span>Produk</span>
            </a>
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

    <main class="flex-1 p-10 space-y-6 overflow-y-auto">
        <?php if (isset($pesan)) : ?>
            <div class="bg-green-100 text-green-700 p-4 rounded"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-3xl font-bold">Daftar stok bahan</h2>
        </div>
        <!-- tambah bahan -->
        <form method="post" id="formTambah" class="max-w-xl mt-6 bg-white p-6 rounded shadow <?= $tampilkan_form ? '' : 'hidden' ?>">
            <h2 class="text-2xl font-bold mb-4">Tambah Bahan Baru</h2>

            <label class="block mb-2">Nama Bahan</label>
            <input name="nama_bahan" class="border w-full mb-4 p-2 rounded" required>

            <label class="block mb-2">Stok</label>
            <input name="stok_bahan" type="number" class="border w-full mb-4 p-2 rounded" required>

            <label class="block mb-2">Nama Suppliyer</label>
            <input name="nama_suppliyer" class="border w-full mb-4 p-2 rounded" required>

            <label class="block mb-2">Harga</label>
            <input name="harga" type="number" class="border w-full mb-4 p-2 rounded" required>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow">
                Simpan Bahan
            </button>
        </form>
        <button onclick="toggleForm()" id="btnToggle" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow">
                + Tambah Bahan Baru
        </button>
        <form method="post">
            <table class="w-full bg-white rounded-lg shadow">
                <thead class="bg-gray-300">
                    <tr>
                        <th class="p-3 text-left">Kode Bahan</th>
                        <th class="p-3 text-left">Nama Bahan</th>
                        <th class="p-3 text-left">Stok</th>
                        <th class="p-3 text-left">Nama Suppliyer</th>
                        <th class="p-3 text-left">Harga</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset pointer untuk membaca ulang hasil query
                    mysqli_data_seek($result, 0);
                    while ($item = mysqli_fetch_assoc($result)) : ?>
                    <tr class="border-t">
                        <td class="p-3"><?= htmlspecialchars($item['kode_bahan']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($item['nama_bahan']) ?></td>
                        <td class="p-3">
                            <?= (int)$item['stok_bahan'] ?>
                            <input type="hidden" name="stok_laporan[<?= (int)$item['id_bahan'] ?>]" value="<?= (int)$item['stok_bahan'] ?>">
                        </td>
                        <td class="p-3"><?= htmlspecialchars($item['nama_suppliyer']) ?></td>
                        <td class="p-3">Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?></td>
                        <td class="p-3">
                            <a href="edit_bahan.php?id=<?= (int)$item['id_bahan'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded text-sm hover:bg-yellow-600">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="mt-6 flex items-center gap-4">
                <label for="tanggal" class="font-medium">Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal" class="border px-4 py-2 rounded" required>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">Simpan ke Laporan</button>
            </div>
        </form>
    </main>
</div>

<script>
    const form = document.getElementById('formTambah');
    const button = document.getElementById('btnToggle');

    function toggleForm() {
        form.classList.toggle('hidden');
        if (form.classList.contains('hidden')) {
            button.textContent = "+ Tambah Bahan Baru";
        } else {
            button.textContent = "Tutup Form Tambah";
        }
    }

    // Otomatis buka form jika baru saja menambahkan bahan
    <?php if ($tampilkan_form): ?>
        document.addEventListener('DOMContentLoaded', () => {
            form.classList.remove('hidden');
            button.textContent = "Tutup Form Tambah";
        });
    <?php endif; ?>
</script>
</body>
</html>
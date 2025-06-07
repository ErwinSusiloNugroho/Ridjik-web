<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpanTransaksi'])) {
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    $produk = $_POST['produk'];
    $jumlah = $_POST['jumlah'];
    $harga = $_POST['harga'];

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);

    $gambarName = '';
    // Upload nota hanya sekali
    if (isset($_FILES['nota_umum']) && $_FILES['nota_umum']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['nota_umum']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;
        if (move_uploaded_file($_FILES['nota_umum']['tmp_name'], $targetFile)) {
            $gambarName = $targetFile;
        }
    }

    // Simpan ke database transaksi
    $stmt = $conn->prepare("INSERT INTO transaksi (jenis, tanggal, nota) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $jenis, $tanggal, $gambarName);
    if ($stmt->execute()) {
        $idTransaksi = $stmt->insert_id;

        // Simpan detail_transaksi
        $stmtDetail = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, produk, jumlah, harga) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($produk); $i++) {
            $p = $produk[$i];
            $j = $jumlah[$i];
            $h = $harga[$i];
            $stmtDetail->bind_param("isii", $idTransaksi, $p, $j, $h);
            $stmtDetail->execute();
        }
        $stmtDetail->close();

        // Simpan juga ke laporan.txt (opsional)
        $data = "Tanggal: $tanggal\nJenis: $jenis\n";
        foreach ($produk as $i => $p) {
            $data .= "- $p | Jumlah: $jumlah[$i] | Harga: Rp" . number_format($harga[$i]) . " | Nota: $gambarName\n";
        }
        $data .= "\n";
        file_put_contents('laporan.txt', $data, FILE_APPEND);

        $pesan = "Transaksi berhasil disimpan ke database dan laporan!";
    } else {
        $pesan = "Gagal menyimpan transaksi ke database.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi | RIDJIK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-gray-100 h-screen">
<div class="flex h-full overflow-auto">
    <!-- Sidebar -->
   <aside class="w-64 min-h-screen bg-gray-800 text-white p-6">
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

    <!-- Main Content -->
    <main class="flex-1 p-10 space-y-6 overflow-y-auto">
        <?php if (isset($pesan)) : ?>
            <div class="bg-green-100 text-green-700 p-4 rounded"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <h2 class="text-3xl font-bold mb-4">Transaksi</h2>

        <form method="POST" action="transaksi.php" enctype="multipart/form-data">
            <label class="font-semibold mr-4">Jenis Transaksi:</label>
            <select name="jenis" id="jenis" class="border p-2 rounded" onchange="toggleForm()" required>
                <option value="">-- Pilih --</option>
                <option value="pembelian">Pembelian</option>
                <option value="penjualan">Penjualan</option>
            </select>

            <div id="formTransaksi" class="mt-6">
                <table class="w-full bg-white rounded-lg shadow" id="tabelTransaksi">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left">Nama Produk</th>
                            <th class="p-3 text-left">Jumlah</th>
                            <th class="p-3 text-left">Harga (Rp)</th>
                            <th class="p-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-3"><input type="text" name="produk[]" class="input" required></td>
                            <td class="p-3"><input type="number" name="jumlah[]" class="input" required></td>
                            <td class="p-3"><input type="number" name="harga[]" class="input" required></td>
                            <td class="p-3"><button type="button" onclick="hapusBaris(this)" class="text-red-500">Hapus</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" onclick="tambahBaris()" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded">+ Tambah Baris</button>

                <div class="my-4">
                    <label class="font-semibold block mb-2">Upload Nota:</label>
                    <input type="file" name="nota_umum" accept="image/*" class="border p-2 bg-white">
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <label for="tanggal" class="font-medium">Tanggal:</label>
                    <input type="date" name="tanggal" id="tanggal" class="border px-4 py-2 rounded" required>
                    <button type="submit" name="simpanTransaksi" class="bg-green-500 text-white px-4 py-2 rounded">Simpan Transaksi</button>
                </div>
            </div>
        </form>
        
        <!-- rekap perhari-->
       <h3 class="text-xl font-bold mt-12 mb-4">Rekap Transaksi Detail Per Hari</h3>
        <form method="GET" class="mb-4 flex items-center gap-2">
            <label for="tanggal" class="font-semibold">Pilih Tanggal:</label>
            <input 
                type="date" 
                name="tanggal" 
                id="tanggal" 
                value="<?= isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : '' ?>" 
                class="border rounded p-1" 
            />
            <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">Tampilkan</button>
        </form>

        <?php
        $tanggalFilter = isset($_GET['tanggal']) && $_GET['tanggal'] !== '' ? $_GET['tanggal'] : null;

        if ($tanggalFilter) {
            // Query filter berdasar tanggal yang dipilih
            $sql = "SELECT 
                t.tanggal,
                dt.id_detail_transaksi,
                dt.produk,
                t.jenis,
                dt.jumlah,
                dt.harga,
                (dt.jumlah * dt.harga) AS subtotal
            FROM transaksi t
            JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
            WHERE t.tanggal = ?
            ORDER BY t.tanggal DESC, t.id_transaksi, dt.id_detail_transaksi";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $tanggalFilter);
            $stmt->execute();
            $result = $stmt->get_result();

        } else {
            // Jika belum pilih tanggal, tampilkan semua data
            $sql = "SELECT 
                t.tanggal,
                dt.id_detail_transaksi,
                dt.produk,
                t.jenis,
                dt.jumlah,
                dt.harga,
                (dt.jumlah * dt.harga) AS subtotal
            FROM transaksi t
            JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
            ORDER BY t.tanggal DESC, t.id_transaksi, dt.id_detail_transaksi";

            $result = $conn->query($sql);
        }

        if ($result && $result->num_rows > 0) {
    echo "<table class='w-full bg-white rounded shadow'>";
    echo "<thead class='bg-gray-100'><tr>
        <th class='p-3'>Tanggal</th>
        <th class='p-3'>Produk</th>
        <th class='p-3'>Jenis</th>
        <th class='p-3'>Jumlah</th>
        <th class='p-3'>Harga (Rp)</th>
        <th class='p-3'>Subtotal (Rp)</th>
        <th class='p-3'>Aksi</th>
    </tr></thead><tbody>";

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_detail_transaksi'];
        $warnaJenis = ($row['jenis'] == 'penjualan') ? 'text-green-600' : 'text-red-600';

        echo "<tr class='text-center'>";
        echo "<td class='p-3'>{$row['tanggal']}</td>";
        echo "<td class='p-3'>" . htmlspecialchars($row['produk']) . "</td>";
        echo "<td class='p-3 capitalize $warnaJenis'>" . htmlspecialchars($row['jenis']) . "</td>";
        echo "<td class='p-3'>{$row['jumlah']}</td>";
        echo "<td class='p-3'>" . number_format($row['harga']) . "</td>";
        echo "<td class='p-3'>" . number_format($row['subtotal']) . "</td>";
        echo "<td class='p-3'><button onclick='toggleEdit($id)' class='edit'>Edit</button></td>";
        echo "</tr>";

        // Baris form edit (disembunyikan awalnya), dibungkus flex dan justify-center supaya form edit di tengah
        echo "<tr id='edit-row-$id' class='hidden bg-gray-50'>";
        echo "<td colspan='7'>
            <div class='flex justify-center'>
                <form method='post' action='update_detail.php' onsubmit='return confirm(\"Simpan perubahan?\")' class='flex gap-2 items-center'>
                    <input type='hidden' name='id_detail_transaksi' value='$id'>
                    <input type='text' name='produk' value='" . htmlspecialchars($row['produk']) . "' class='border p-1 rounded' required>
                    <input type='number' name='jumlah' value='{$row['jumlah']}' class='border p-1 rounded' required>
                    <input type='number' name='harga' value='{$row['harga']}' class='border p-1 rounded' required>
                    <button type='submit' class='bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600'>Simpan</button>
                    <button type='button' onclick='toggleEdit($id)' class='bg-gray-400 text-white px-3 py-1 rounded hover:bg-gray-500'>Batal</button>
                </form>
            </div>
        </td>";
        echo "</tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<div class='text-gray-500'>Belum ada data transaksi.</div>";
        }
        ?>

        <script>
        function toggleEdit(id) {
            const row = document.getElementById('edit-row-' + id);
            row.classList.toggle('hidden');
        }
        </script>


        <!-- Filter Rentang Tanggal -->
        <h3 class="text-xl font-bold mt-12 mb-4">Rekap Transaksi Berdasarkan Rentang Tanggal</h3>
        <form method="GET" class="flex gap-4 items-center mb-6">
            <label for="dari">Dari:</label>
            <input type="date" name="dari" id="dari" required class="border p-2 rounded" value="<?= htmlspecialchars($_GET['dari'] ?? '') ?>">
            <label for="sampai">Sampai:</label>
            <input type="date" name="sampai" id="sampai" required class="border p-2 rounded" value="<?= htmlspecialchars($_GET['sampai'] ?? '') ?>">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Tampilkan
            </button>
        </form>

        <?php
        if (isset($_GET['dari']) && isset($_GET['sampai'])) {
            $dari = $_GET['dari'];
            $sampai = $_GET['sampai'];

            $queryRentang = "SELECT 
                t.id_transaksi, t.tanggal, t.jenis,
                GROUP_CONCAT(dt.produk SEPARATOR ', ') AS daftar_produk,
                SUM(dt. jumlah) AS jumlah_produk,
                SUM(dt.jumlah * dt.harga) AS total_harga
            FROM transaksi t
            JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
            WHERE t.tanggal BETWEEN '$dari' AND '$sampai'
            GROUP BY t.id_transaksi
            ORDER BY t.tanggal DESC";

            $resultRentang = $conn->query($queryRentang);

            echo "<table class='w-full bg-white rounded shadow'>";
            echo "<thead class='bg-gray-300'><tr><th class='p-3'>Tanggal</th><th class='p-3'>Jenis</th><th class='p-3'>Produk</th><th class='p-3'>Jumlah</th><th class='p-3'>Total (Rp)</th></tr></thead><tbody>";
            if ($resultRentang && $resultRentang->num_rows > 0) {
                while ($row = $resultRentang->fetch_assoc()) {
                    echo "<tr class='text-center'>";
                    echo "<td class='p-3'>{$row['tanggal']}</td>";
                    echo "<td class='p-3 capitalize " . ($row['jenis'] == 'penjualan' ? 'text-green-600' : 'text-red-600') . "'>" . htmlspecialchars($row['jenis']) . "</td>";
                    echo "<td class='p-3'>" . htmlspecialchars($row['daftar_produk']) . "</td>";
                    echo "<td class='p-3'>{$row['jumlah_produk']}</td>";
                    echo "<td class='p-3'>Rp " . number_format($row['total_harga']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center text-gray-500 p-3'>Tidak ada data dalam rentang ini.</td></tr>";
            }
            echo "</tbody></table>";
        }
        ?>

        <!-- Rekap Total Transaksi Per Hari -->
        <h3 class="text-xl font-bold mt-12 mb-4">Rekap Total Transaksi Per Hari</h3>
        <?php
        $sqlPerHari = "SELECT 
            tanggal,
            COUNT(DISTINCT t.id_transaksi) AS jumlah_transaksi,
            SUM(dt.jumlah * dt.harga) AS total_harian
        FROM transaksi t
        JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
        GROUP BY tanggal
        ORDER BY tanggal DESC";

        $resultHari = $conn->query($sqlPerHari);

        echo "<table class='w-full bg-white rounded shadow'>";
        echo "<thead class='bg-gray-300'><tr><th class='p-3'>Tanggal</th><th class='p-3'>Jumlah Transaksi</th><th class='p-3'>Total (Rp)</th></tr></thead><tbody>";
        if ($resultHari && $resultHari->num_rows > 0) {
            while ($row = $resultHari->fetch_assoc()) {
                echo "<tr class='text-center'>";
                echo "<td class='p-3 text-center'>{$row['tanggal']}</td>";
                echo "<td class='p-3 text-center'>{$row['jumlah_transaksi']}</td>";
                echo "<td class='p-3'>Rp " . number_format($row['total_harian']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3' class='text-center text-gray-500 p-3'>Belum ada data transaksi.</td></tr>";
        }
        echo "</tbody></table>";
        ?>

    </main>
</div>

<script>
// Tampilkan form input transaksi jika sudah pilih jenis
function toggleForm() {
    const jenis = document.getElementById('jenis').value;
    document.getElementById('formTransaksi').style.display = jenis ? 'block' : 'none';
}

// Tambah baris produk baru
function tambahBaris() {
    const tbody = document.querySelector('#tabelTransaksi tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-3"><input type="text" name="produk[]" class="input" required></td>
        <td class="p-3"><input type="number" name="jumlah[]" class="input" required></td>
        <td class="p-3"><input type="number" name="harga[]" class="input" required></td>
        <td class="p-3"><button type="button" onclick="hapusBaris(this)" class="text-red-500">Hapus</button></td>
    `;
    tbody.appendChild(tr);
}

// Hapus baris produk
function hapusBaris(btn) {
    const tr = btn.closest('tr');
    tr.remove();
}
</script>

</body>
</html>

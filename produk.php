<?php
include 'koneksi.php';

$tampilkan_form = false;

// Ambil data produk dari database
$query = "SELECT * FROM produk";
$result = mysqli_query($conn, $query);

// Proses tambah produk baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah produk baru
    if (isset($_POST['nama_produk'], $_POST['kode_produk'], $_POST['stok_produk'], $_POST['harga_produk'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
        $kode = mysqli_real_escape_string($conn, $_POST['kode_produk']);
        $stok = (int)$_POST['stok_produk'];
        $harga = (float)$_POST['harga_produk'];

        $sql = "INSERT INTO produk (nama_produk, kode_produk, stok_produk, harga)
                VALUES ('$nama', '$kode', $stok, $harga)";
        if (mysqli_query($conn, $sql)) {
            $pesan = "Produk baru berhasil ditambahkan!";
            $tampilkan_form = true;

            $result = mysqli_query($conn, $query);
        } else {
            $pesan = "Gagal menambahkan produk baru: " . mysqli_error($conn);
            $tampilkan_form = true;
        }
    }

    // Proses Edit Produk (inline)
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah produk baru
    if (isset($_POST['nama_produk'], $_POST['kode_produk'], $_POST['stok_produk'], $_POST['harga_produk'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
        $kode = mysqli_real_escape_string($conn, $_POST['kode_produk']);
        $stok = (int)$_POST['stok_produk'];
        $harga = (float)$_POST['harga_produk'];

        $sql = "INSERT INTO produk (nama_produk, kode_produk, stok_produk, harga)
                VALUES ('$nama', '$kode', $stok, $harga)";
        if (mysqli_query($conn, $sql)) {
            $pesan = "Produk baru berhasil ditambahkan!";
            $tampilkan_form = true;
        } else {
            $pesan = "Gagal menambahkan produk baru: " . mysqli_error($conn);
            $tampilkan_form = true;
        }
    }

    // Edit produk (pastikan edit_harga disertakan!)
    if (isset($_POST['edit_id'], $_POST['edit_nama'], $_POST['edit_kode'], $_POST['edit_stok'], $_POST['edit_harga'])) {
        $id = (int)$_POST['edit_id'];
        $nama = mysqli_real_escape_string($conn, $_POST['edit_nama']);
        $kode = mysqli_real_escape_string($conn, $_POST['edit_kode']);
        $stok = (int)$_POST['edit_stok'];
        $harga = (float)$_POST['edit_harga'];

        $sql = "UPDATE produk SET nama_produk = '$nama', kode_produk = '$kode', stok_produk = $stok, harga = $harga WHERE id_produk = $id";
        if (mysqli_query($conn, $sql)) {
            $pesan = "Produk berhasil diperbarui!";
        } else {
            $pesan = "Gagal mengedit produk: " . mysqli_error($conn);
        }
    }

    // Refresh data
    $result = mysqli_query($conn, $query);
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Produk | RIDJIK</title>
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
      <main class="flex-1 p-10 space-y-6 overflow-y-auto">
        <h2 class="text-3xl font-bold mb-4">Daftar Produk</h2>
        
        <!-- Form tambah produk -->
        <form method="post" id="formTambah" class="max-w-xl mt-6 bg-white p-6 rounded shadow <?= $tampilkan_form ? '' : 'hidden' ?>">
            <h2 class="text-2xl font-bold mb-4">Tambah Produk Baru</h2>
            <?php if (isset($pesan)) : ?>
                <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?= $pesan ?></div>
            <?php endif; ?>
            <label class="block mb-2">Kode Produk</label>
            <input name="kode_produk" type="text" class="border w-full mb-4 p-2 rounded" required />

            <label class="block mb-2">Nama Produk</label>
            <input name="nama_produk" type="text" class="border w-full mb-4 p-2 rounded" required />

            <label class="block mb-2">Stok</label>
            <input name="stok_produk" type="number" class="border w-full mb-4 p-2 rounded" required />

            <label class="block mb-2">Harga Produk</label>
            <input name="harga_produk" type="number" step="0.01" min="0" class="border w-full mb-4 p-2 rounded" required />

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded shadow">
                Simpan Produk
            </button>
        </form>

        <button onclick="toggleForm()" id="btnToggle" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow">
            + Tambah Produk Baru
        </button>

        <form method="post" id="produkListForm">
            <table class="w-full bg-white rounded-lg shadow mt-4">
                <thead class="bg-gray-300">
                    <tr>
                        <th class="p-3 text-left">Nama Produk</th>
                        <th class="p-3 text-left">Kode Produk</th>
                        <th class="p-3 text-left">Stok</th>
                        <th class="p-3 text-left">Harga/pcs</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($item = mysqli_fetch_assoc($result)) : ?>
                    <tr class="border-t" data-id="<?= $item['id_produk'] ?>">
                        <td class="p-3 nama"><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td class="p-3 kode"><?= htmlspecialchars($item['kode_produk']) ?></td>
                        <td class="p-3 stok"><?= (int)$item['stok_produk'] ?></td>
                        <td class="p-3 harga" data-harga="<?= $item['harga'] ?>">
                        <?= number_format($item['harga'], 2, ',', '.') ?>
                        </td>

                        <td class="p-3">
                          <button type="button" class="edit">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <!-- Baris form edit akan disisipkan di bawah baris ini -->
                <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    </main>
</div>

<script>
    const formTambah = document.getElementById('formTambah');
    const btnToggle = document.getElementById('btnToggle');

    function toggleForm() {
        formTambah.classList.toggle('hidden');
        if (formTambah.classList.contains('hidden')) {
            btnToggle.textContent = "+ Tambah Produk Baru";
        } else {
            btnToggle.textContent = "Tutup Form ";
        }
    }

    // Handle edit button click untuk tampilkan form edit inline
    document.querySelectorAll('.edit').forEach(button => {
    button.addEventListener('click', function() {
        const tr = this.closest('tr');
        const id = tr.getAttribute('data-id');
        const nama = tr.querySelector('.nama').textContent.trim();
        const kode = tr.querySelector('.kode').textContent.trim();
        const stok = tr.querySelector('.stok').textContent.trim();
        const harga = tr.querySelector('.harga').dataset.harga; // ambil dari data-harga

        const existingForm = document.querySelector('.edit-form-row');
        if (existingForm) existingForm.remove();

        const formRow = document.createElement('tr');
        formRow.classList.add('edit-form-row', 'bg-gray-100');

                    formRow.innerHTML = `
                <tr class="edit-form-row bg-gray-100">
                    <td colspan="5" class="p-3">
                    <form method="post" class="grid grid-cols-5 gap-2 items-center">
                        <input type="text" name="edit_nama" value="${nama}" class="border p-2 rounded w-full" required />
                        <input type="text" name="edit_kode" value="${kode}" class="border p-2 rounded w-full" required />
                        <input type="number" name="edit_stok" value="${stok}" class="border p-2 rounded w-full" required />
                        <input type="number" name="edit_harga" value="${harga}" class="border p-2 rounded w-full" required />
                        <div class="flex gap-2">
                        <input type="hidden" name="edit_id" value="${id}" />
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">Simpan</button>
                        <button type="button" class="cancel-btn bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded text-sm">Batal</button>
                        </div>
                    </form>
                    </td>
                </tr>
                `;


        tr.after(formRow);

        // Tombol batal
        formRow.querySelector('.cancel-btn').addEventListener('click', () => {
            formRow.remove();
        });
    });
});

</script>

</body>
</html>


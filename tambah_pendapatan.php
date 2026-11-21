<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah_pendapatan = floatval($_POST['jumlah_pendapatan']);
    
    // Validasi input
    if (empty($deskripsi)) {
        $error = "Deskripsi tidak boleh kosong!";
    } elseif ($jumlah_pendapatan <= 0) {
        $error = "Jumlah pendapatan harus lebih dari 0!";
    } else {
        // Hitung otomatis 40% untuk tabungan dan 60% untuk digunakan
        $jumlah_tabungan = $jumlah_pendapatan * 0.4;
        $jumlah_digunakan = $jumlah_pendapatan * 0.6;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, deskripsi, jumlah_pendapatan, jumlah_tabungan, jumlah_digunakan) 
                                  VALUES (:tanggal, :deskripsi, :jumlah_pendapatan, :jumlah_tabungan, :jumlah_digunakan)");
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':jumlah_pendapatan', $jumlah_pendapatan);
            $stmt->bindParam(':jumlah_tabungan', $jumlah_tabungan);
            $stmt->bindParam(':jumlah_digunakan', $jumlah_digunakan);
            $stmt->execute();
            
            $success = "Pendapatan berhasil ditambahkan! Tabungan: Rp " . number_format($jumlah_tabungan, 0, ',', '.');
        } catch(PDOException $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pendapatan - Sistem Manajemen Keuangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Sistem Manajemen Keuangan</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="tambah_pendapatan.php" class="active">Tambah Pendapatan</a>
                <a href="riwayat_transaksi.php">Riwayat Transaksi</a>
                <a href="laporan.php">Laporan</a>
            </nav>
        </header>

        <main>
            <section class="form-section">
                <h2>Tambah Pendapatan Baru</h2>
                
                <?php if ($success): ?>
                    <div class="alert success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi:</label>
                        <input type="text" id="deskripsi" name="deskripsi" placeholder="Contoh: Gaji bulanan, Bonus, dll" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_pendapatan">Jumlah Pendapatan (Rp):</label>
                        <input type="number" id="jumlah_pendapatan" name="jumlah_pendapatan" placeholder="Masukkan jumlah" min="1" step="0.01" required>
                    </div>
                    
                    <div class="info-box">
                        <h4>💡 Informasi Pembagian Otomatis:</h4>
                        <ul>
                            <li><strong>40% Tabungan:</strong> Akan otomatis dihitung dan disimpan</li>
                            <li><strong>60% Digunakan:</strong> Untuk kebutuhan sehari-hari</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn-primary">Simpan Pendapatan</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
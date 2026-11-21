<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

// Ambil data transaksi berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    header("Location: riwayat_transaksi.php");
    exit();
}

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
            $stmt = $pdo->prepare("UPDATE transaksi 
                                  SET tanggal = :tanggal, 
                                      deskripsi = :deskripsi, 
                                      jumlah_pendapatan = :jumlah_pendapatan, 
                                      jumlah_tabungan = :jumlah_tabungan, 
                                      jumlah_digunakan = :jumlah_digunakan 
                                  WHERE id = :id");
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':jumlah_pendapatan', $jumlah_pendapatan);
            $stmt->bindParam(':jumlah_tabungan', $jumlah_tabungan);
            $stmt->bindParam(':jumlah_digunakan', $jumlah_digunakan);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $success = "Transaksi berhasil diupdate!";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $error = "Gagal mengupdate data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - Sistem Manajemen Keuangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Sistem Manajemen Keuangan</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="tambah_pendapatan.php">Tambah Pendapatan</a>
                <a href="riwayat_transaksi.php" class="active">Riwayat Transaksi</a>
                <a href="laporan.php">Laporan</a>
            </nav>
        </header>

        <main>
            <section class="form-section">
                <h2>Edit Transaksi</h2>
                
                <?php if ($success): ?>
                    <div class="alert success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($transaksi['tanggal']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi:</label>
                        <input type="text" id="deskripsi" name="deskripsi" value="<?= htmlspecialchars($transaksi['deskripsi']) ?>" placeholder="Contoh: Gaji bulanan, Bonus, dll" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_pendapatan">Jumlah Pendapatan (Rp):</label>
                        <input type="number" id="jumlah_pendapatan" name="jumlah_pendapatan" value="<?= htmlspecialchars($transaksi['jumlah_pendapatan']) ?>" min="1" step="0.01" required>
                    </div>
                    
                    <div class="info-box">
                        <h4>💡 Informasi Pembagian Otomatis:</h4>
                        <ul>
                            <li><strong>40% Tabungan:</strong> Rp <?= number_format($transaksi['jumlah_pendapatan'] * 0.4, 0, ',', '.') ?></li>
                            <li><strong>60% Digunakan:</strong> Rp <?= number_format($transaksi['jumlah_pendapatan'] * 0.6, 0, ',', '.') ?></li>
                        </ul>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-primary">Update Transaksi</button>
                        <a href="riwayat_transaksi.php" class="btn-secondary">Batal</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
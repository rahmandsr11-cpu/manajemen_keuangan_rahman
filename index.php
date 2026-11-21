<?php
require_once 'config.php';





// Ambil data ringkasan bulan ini
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(jumlah_pendapatan), 0) as total_pendapatan,
    COALESCE(SUM(jumlah_tabungan), 0) as total_tabungan,
    COALESCE(SUM(jumlah_digunakan), 0) as total_digunakan
    FROM transaksi 
    WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) 
    AND YEAR(tanggal) = YEAR(CURRENT_DATE())");
$stmt->execute();
$ringkasan = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil 5 transaksi terakhir
$stmt = $pdo->prepare("SELECT * FROM transaksi ORDER BY tanggal DESC, created_at DESC LIMIT 5");
$stmt->execute();
$transaksi_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Sistem Manajemen Keuangan</h1>
            <nav>
                <a href="index.php" class="active">Dashboard</a>
                <a href="tambah_pendapatan.php">Tambah Pendapatan</a>
                <a href="riwayat_transaksi.php">Riwayat Transaksi</a>
                <a href="laporan.php">Laporan</a>
            </nav>
        </header>

        <main>
            <section class="summary-cards">
                <div class="card income">
                    <h3>Total Pendapatan Bulan Ini</h3>
                    <p class="amount">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.') ?></p>
                </div>
                <div class="card savings">
                    <h3>Total Tabungan (40%)</h3>
                    <p class="amount">Rp <?= number_format($ringkasan['total_tabungan'], 0, ',', '.') ?></p>
                </div>
                <div class="card spending">
                    <h3>Total Digunakan (60%)</h3>
                    <p class="amount">Rp <?= number_format($ringkasan['total_digunakan'], 0, ',', '.') ?></p>
                </div>
            </section>

            <section class="recent-transactions">
                <h2>Transaksi Terbaru</h2>
                <?php if (count($transaksi_terbaru) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Pendapatan</th>
                                <th>Tabungan (40%)</th>
                                <th>Digunakan (60%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaksi_terbaru as $transaksi): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($transaksi['deskripsi']) ?></td>
                                <td>Rp <?= number_format($transaksi['jumlah_pendapatan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($transaksi['jumlah_tabungan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($transaksi['jumlah_digunakan'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Belum ada transaksi. <a href="tambah_pendapatan.php">Tambahkan sekarang!</a></p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
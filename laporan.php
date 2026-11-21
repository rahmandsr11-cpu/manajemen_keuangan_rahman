<?php
require_once 'config.php';

// Ambil data per bulan untuk tahun ini
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

$stmt = $pdo->prepare("SELECT 
    MONTH(tanggal) as bulan,
    SUM(jumlah_pendapatan) as total_pendapatan,
    SUM(jumlah_tabungan) as total_tabungan,
    SUM(jumlah_digunakan) as total_digunakan
    FROM transaksi 
    WHERE YEAR(tanggal) = :tahun
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal)");
$stmt->bindParam(':tahun', $tahun);
$stmt->execute();
$laporan_bulanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total tahunan
$total_tahunan = [
    'pendapatan' => 0,
    'tabungan' => 0,
    'digunakan' => 0
];

foreach ($laporan_bulanan as $data) {
    $total_tahunan['pendapatan'] += $data['total_pendapatan'];
    $total_tahunan['tabungan'] += $data['total_tabungan'];
    $total_tahunan['digunakan'] += $data['total_digunakan'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Manajemen Keuangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Sistem Manajemen Keuangan</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="tambah_pendapatan.php">Tambah Pendapatan</a>
                <a href="riwayat_transaksi.php">Riwayat Transaksi</a>
                <a href="laporan.php" class="active">Laporan</a>
            </nav>
        </header>

        <main>
            <section class="report-section">
                <h2>Laporan Tahun <?= $tahun ?></h2>
                
                <form method="GET" action="" style="margin-bottom: 20px;">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>Pilih Tahun:</label>
                            <select name="tahun" onchange="this.form.submit()">
                                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </form>
                
                <div class="annual-summary">
                    <h3>Ringkasan Tahunan</h3>
                    <div class="summary-cards">
                        <div class="card">
                            <h4>Total Pendapatan</h4>
                            <p class="amount">Rp <?= number_format($total_tahunan['pendapatan'], 0, ',', '.') ?></p>
                        </div>
                        <div class="card">
                            <h4>Total Tabungan (40%)</h4>
                            <p class="amount">Rp <?= number_format($total_tahunan['tabungan'], 0, ',', '.') ?></p>
                        </div>
                        <div class="card">
                            <h4>Total Digunakan (60%)</h4>
                            <p class="amount">Rp <?= number_format($total_tahunan['digunakan'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                
                <h3>Laporan Bulanan</h3>
                <?php if (count($laporan_bulanan) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Pendapatan</th>
                                <th>Tabungan (40%)</th>
                                <th>Digunakan (60%)</th>
                                <th>% Tabungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_bulanan as $data): ?>
                            <tr>
                                <td><?= date('F', mktime(0, 0, 0, $data['bulan'], 1)) ?></td>
                                <td>Rp <?= number_format($data['total_pendapatan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($data['total_tabungan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($data['total_digunakan'], 0, ',', '.') ?></td>
                                <td>
                                    <?php 
                                    $persentase = $data['total_pendapatan'] > 0 
                                        ? round(($data['total_tabungan'] / $data['total_pendapatan']) * 100, 2) 
                                        : 0;
                                    echo $persentase . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Belum ada data untuk tahun <?= $tahun ?>.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
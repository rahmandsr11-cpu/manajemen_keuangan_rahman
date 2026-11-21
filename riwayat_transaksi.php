<?php
require_once 'config.php';

// Filter berdasarkan bulan dan tahun
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Pesan sukses/hapus
$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = '<div class="alert success">Transaksi berhasil dihapus!</div>';
    } elseif ($_GET['msg'] == 'error') {
        $message = '<div class="alert error">Gagal menghapus transaksi!</div>';
    }
}

// Ambil semua transaksi sesuai filter
$stmt = $pdo->prepare("SELECT * FROM transaksi 
                      WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun 
                      ORDER BY tanggal DESC, created_at DESC");
$stmt->bindParam(':bulan', $bulan);
$stmt->bindParam(':tahun', $tahun);
$stmt->execute();
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total
$total_pendapatan = array_sum(array_column($transaksi, 'jumlah_pendapatan'));
$total_tabungan = array_sum(array_column($transaksi, 'jumlah_tabungan'));
$total_digunakan = array_sum(array_column($transaksi, 'jumlah_digunakan'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Sistem Manajemen Keuangan</title>
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
            <section class="filter-section">
                <h2>Filter Transaksi</h2>
                <form method="GET" action="">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>Bulan:</label>
                            <select name="bulan">
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($i == $bulan) ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Tahun:</label>
                            <select name="tahun">
                                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-primary">Filter</button>
                    </div>
                </form>
            </section>

            <section class="transactions-list">
                <h2>Riwayat Transaksi - <?= date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) ?></h2>
                
                <?= $message ?>
                
                <div class="summary-box">
                    <div class="summary-item">
                        <span>Total Pendapatan:</span>
                        <strong>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Tabungan (40%):</span>
                        <strong>Rp <?= number_format($total_tabungan, 0, ',', '.') ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Digunakan (60%):</span>
                        <strong>Rp <?= number_format($total_digunakan, 0, ',', '.') ?></strong>
                    </div>
                </div>
                
                <?php if (count($transaksi) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Pendapatan</th>
                                <th>Tabungan (40%)</th>
                                <th>Digunakan (60%)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($transaksi as $t): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($t['deskripsi']) ?></td>
                                <td>Rp <?= number_format($t['jumlah_pendapatan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($t['jumlah_tabungan'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($t['jumlah_digunakan'], 0, ',', '.') ?></td>
                                <td class="action-buttons">
                                    <a href="edit_transaksi.php?id=<?= $t['id'] ?>" class="btn-edit" title="Edit">✏️</a>
                                    <a href="hapus_transaksi.php?id=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')" title="Hapus">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Tidak ada transaksi pada periode ini.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
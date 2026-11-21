<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header("Location: riwayat_transaksi.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        header("Location: riwayat_transaksi.php?msg=error");
        exit();
    }
} else {
    header("Location: riwayat_transaksi.php");
    exit();
}
?>
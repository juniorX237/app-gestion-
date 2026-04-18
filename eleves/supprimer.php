<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id == 0) { header('Location: liste.php'); exit; }

// Supprimer la photo si elle existe
$stmt = $pdo->prepare("SELECT photo FROM eleves WHERE id = ?");
$stmt->execute([$id]);
$eleve = $stmt->fetch();

if ($eleve && $eleve['photo']) {
    $photo_path = __DIR__ . '/../uploads/photos/' . $eleve['photo'];
    if (file_exists($photo_path)) {
        unlink($photo_path);
    }
}

// Supprimer l'élève (les notes seront supprimées automatiquement via CASCADE)
$stmt = $pdo->prepare("DELETE FROM eleves WHERE id = ?");
$stmt->execute([$id]);

header('Location: liste.php?success=' . urlencode('Élève supprimé avec succès.'));
exit;

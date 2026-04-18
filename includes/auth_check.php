<?php
// Vérifier si l'utilisateur est connecté
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['enseignant_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

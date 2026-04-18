<?php
// Rediriger vers le tableau de bord ou la page de connexion
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['enseignant_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;

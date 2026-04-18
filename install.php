<?php
// ========================================
// Script d'installation de la base de données
// ========================================

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connexion sans base de données
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/sql/gestion_scolaire.sql');

    // Exécuter le script SQL
    $pdo->exec($sql);

    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Lycée Kaisen</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #1e293b; border-radius: 16px; padding: 48px; max-width: 500px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.4); }
        .icon { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #34d399; margin-bottom: 16px; font-size: 24px; }
        p { color: #94a3b8; margin-bottom: 12px; line-height: 1.6; }
        .credentials { background: #0f172a; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: left; }
        .credentials strong { color: #38bdf8; }
        a { display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: transform 0.2s; }
        a:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h1>Installation réussie !</h1>
        <p>La base de données <strong>gestion_scolaire</strong> a été créée avec succès.</p>
        <div class="credentials">
            <p><strong>Email :</strong> admin@ecole.cm</p>
            <p><strong>Mot de passe :</strong> admin123</p>
        </div>
        <p>Utilisez ces identifiants pour vous connecter.</p>
        <a href="auth/login.php">Se connecter →</a>
    </div>
</body>
</html>';

} catch (PDOException $e) {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur d\'installation</title>
    <style>
        body { font-family: "Segoe UI", sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #1e293b; border-radius: 16px; padding: 48px; max-width: 500px; text-align: center; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #ef4444; margin-bottom: 16px; }
        p { color: #94a3b8; }
        .error { background: #450a0a; border: 1px solid #dc2626; border-radius: 8px; padding: 16px; margin-top: 16px; text-align: left; font-size: 14px; color: #fca5a5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">❌</div>
        <h1>Erreur d\'installation</h1>
        <p>Une erreur est survenue lors de la création de la base de données.</p>
        <div class="error">' . htmlspecialchars($e->getMessage()) . '</div>
    </div>
</body>
</html>';
}

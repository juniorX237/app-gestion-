<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Si déjà connecté, rediriger
if (isset($_SESSION['enseignant_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM enseignants WHERE email = ?");
        $stmt->execute([$email]);
        $enseignant = $stmt->fetch();

        if ($enseignant && password_verify($password, $enseignant['mot_de_passe'])) {
            $_SESSION['enseignant_id'] = $enseignant['id'];
            $_SESSION['nom'] = $enseignant['nom'];
            $_SESSION['prenom'] = $enseignant['prenom'];
            $_SESSION['email'] = $enseignant['email'];
            $_SESSION['est_admin'] = $enseignant['est_admin'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo NOM_ETABLISSEMENT; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🏫</div>
                <h1><?php echo NOM_ETABLISSEMENT; ?></h1>
                <p>Système de Gestion Scolaire</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="votre@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
                    🔐 Se connecter
                </button>
            </form>

            <div class="auth-footer">
                Pas encore de compte ? <a href="register.php">Créer un compte</a>
            </div>
        </div>
    </div>
</body>
</html>

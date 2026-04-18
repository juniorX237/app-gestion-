<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Si déjà connecté, rediriger
if (isset($_SESSION['enseignant_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $password = $_POST['mot_de_passe'] ?? '';
    $password_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM enseignants WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO enseignants (nom, prenom, email, mot_de_passe, telephone, specialite) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hash, $telephone, $specialite]);

            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - <?php echo NOM_ETABLISSEMENT; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width:520px;">
            <div class="auth-logo">
                <div class="logo-icon">🏫</div>
                <h1><?php echo NOM_ETABLISSEMENT; ?></h1>
                <p>Création de compte enseignant</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" placeholder="manga" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Joyeux" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="jean.dupont@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" placeholder="6XX XX XX XX" value="<?php echo htmlspecialchars($telephone ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="specialite">Spécialité</label>
                        <input type="text" id="specialite" name="specialite" class="form-control" placeholder="Mathématiques" value="<?php echo htmlspecialchars($specialite ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">Mot de passe *</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="Min. 6 caractères" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe_confirm">Confirmer *</label>
                        <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" class="form-control" placeholder="Confirmez" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
                    ✨ Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$page_title = "Modifier un élève";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id == 0) { header('Location: liste.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM eleves WHERE id = ?");
$stmt->execute([$id]);
$eleve = $stmt->fetch();
if (!$eleve) { header('Location: liste.php'); exit; }

$error = '';
$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = strtoupper(trim($_POST['nom'] ?? ''));
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $lieu_naissance = trim($_POST['lieu_naissance'] ?? '');
    $sexe = $_POST['sexe'] ?? '';
    $classe_id = (int)($_POST['classe_id'] ?? 0);
    $nom_parent = trim($_POST['nom_parent'] ?? '');
    $telephone_parent = trim($_POST['telephone_parent'] ?? '');
    $date_admission = $_POST['date_admission'] ?? $eleve['date_admission'];

    if (empty($nom) || empty($prenom) || empty($date_naissance) || empty($lieu_naissance) || empty($sexe) || $classe_id == 0) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        // Gestion de la photo
        $photo_name = $eleve['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/photos/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_name = $eleve['matricule'] . '.' . strtolower($ext);
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name);
        }

        $stmt = $pdo->prepare("UPDATE eleves SET nom=?, prenom=?, date_naissance=?, lieu_naissance=?, sexe=?, classe_id=?, nom_parent=?, telephone_parent=?, photo=?, date_admission=? WHERE id=?");
        $stmt->execute([$nom, $prenom, $date_naissance, $lieu_naissance, $sexe, $classe_id, $nom_parent, $telephone_parent, $photo_name, $date_admission, $id]);

        header('Location: liste.php?success=' . urlencode('Élève modifié avec succès !'));
        exit;
    }
} else {
    // Pré-remplir les champs
    $nom = $eleve['nom'];
    $prenom = $eleve['prenom'];
    $date_naissance = $eleve['date_naissance'];
    $lieu_naissance = $eleve['lieu_naissance'];
    $sexe = $eleve['sexe'];
    $classe_id = $eleve['classe_id'];
    $nom_parent = $eleve['nom_parent'];
    $telephone_parent = $eleve['telephone_parent'];
    $date_admission = $eleve['date_admission'];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width:800px;">
    <div class="card-header">
        <h3 class="card-title">✏️ Modifier l'élève : <?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></h3>
        <a href="liste.php" class="btn btn-outline btn-sm">← Retour</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Matricule</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($eleve['matricule']); ?>" disabled>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($nom); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo htmlspecialchars($prenom); ?>" required>
            </div>
        </div>

        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label" for="date_naissance">Date de naissance *</label>
                <input type="date" id="date_naissance" name="date_naissance" class="form-control" value="<?php echo htmlspecialchars($date_naissance); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="lieu_naissance">Lieu de naissance *</label>
                <input type="text" id="lieu_naissance" name="lieu_naissance" class="form-control" value="<?php echo htmlspecialchars($lieu_naissance); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="sexe">Sexe *</label>
                <select id="sexe" name="sexe" class="form-control" required>
                    <option value="M" <?php echo $sexe == 'M' ? 'selected' : ''; ?>>Masculin</option>
                    <option value="F" <?php echo $sexe == 'F' ? 'selected' : ''; ?>>Féminin</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="classe_id">Classe *</label>
                <select id="classe_id" name="classe_id" class="form-control" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $classe_id == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="date_admission">Date d'admission</label>
                <input type="date" id="date_admission" name="date_admission" class="form-control" value="<?php echo htmlspecialchars($date_admission); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nom_parent">Nom du parent/tuteur</label>
                <input type="text" id="nom_parent" name="nom_parent" class="form-control" value="<?php echo htmlspecialchars($nom_parent); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="telephone_parent">Téléphone du parent</label>
                <input type="text" id="telephone_parent" name="telephone_parent" class="form-control" value="<?php echo htmlspecialchars($telephone_parent); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="photo">Photo (optionnel)</label>
            <?php if ($eleve['photo']): ?>
                <div class="mb-1">
                    <img src="<?php echo BASE_URL; ?>/uploads/photos/<?php echo htmlspecialchars($eleve['photo']); ?>" alt="Photo actuelle" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);">
                </div>
            <?php endif; ?>
            <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
            <img id="photoPreview" src="" alt="Aperçu" style="display:none; margin-top:10px; width:80px; height:80px; border-radius:50%; object-fit:cover;">
        </div>

        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            <a href="liste.php" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

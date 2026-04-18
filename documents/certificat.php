<?php
$page_title = "Certificat de scolarité";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$eleve_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$classe_filter = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;
$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();
$eleves_liste = [];

if ($classe_filter > 0) {
    $eleves_liste = $pdo->query("SELECT * FROM eleves WHERE classe_id = $classe_filter ORDER BY nom, prenom")->fetchAll();
}

$eleve = null;
if ($eleve_id > 0) {
    $stmt = $pdo->prepare("
        SELECT e.*, c.nom AS classe_nom
        FROM eleves e JOIN classes c ON e.classe_id = c.id
        WHERE e.id = ?
    ");
    $stmt->execute([$eleve_id]);
    $eleve = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Sélection -->
<?php if (!$eleve): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🎓 Générer un certificat de scolarité</h3>
    </div>
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Classe</label>
                <select name="classe_id" class="form-control filter-auto">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $classe_filter == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Élève</label>
                <select name="id" class="form-control">
                    <option value="">-- Choisir un élève --</option>
                    <?php foreach ($eleves_liste as $el): ?>
                        <option value="<?php echo $el['id']; ?>"><?php echo htmlspecialchars($el['nom'] . ' ' . $el['prenom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">📄 Générer</button>
    </form>
</div>
<?php else: ?>

<div class="no-print mb-2">
    <button onclick="printPage()" class="btn btn-success">🖨️ Imprimer</button>
    <a href="certificat.php" class="btn btn-outline">← Nouveau certificat</a>
</div>

<!-- Certificat -->
<div class="card" style="max-width:800px; margin:0 auto;">
    <!-- En-tête du certificat -->
    <div style="text-align:center; padding:20px 0; border-bottom:3px double var(--border);">
        <p style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">RÉPUBLIQUE DU CAMEROUN</p>
        <p style="font-size:11px; color:var(--text-muted); margin-bottom:16px;">Paix - Travail - Patrie</p>
        <p style="font-size:11px; color:var(--text-muted);">MINISTÈRE DES ENSEIGNEMENTS SECONDAIRES</p>
        <h2 style="font-size:22px; font-weight:800; margin:12px 0; background:linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
            <?php echo NOM_ETABLISSEMENT; ?>
        </h2>
        <p style="font-size:12px; color:var(--text-muted);">Année scolaire : <?php echo ANNEE_SCOLAIRE; ?></p>
    </div>

    <!-- Titre du document -->
    <div style="text-align:center; padding:30px 0;">
        <h1 style="font-size:28px; font-weight:800; text-transform:uppercase; letter-spacing:3px; border:2px solid var(--primary); display:inline-block; padding:12px 40px; border-radius:var(--radius-md);">
            Certificat de Scolarité
        </h1>
    </div>

    <!-- Corps du certificat -->
    <div style="padding:10px 40px 40px; font-size:15px; line-height:2.2;">
        <p>Le Proviseur du <strong><?php echo NOM_ETABLISSEMENT; ?></strong> soussigné, certifie que :</p>

        <div style="padding:20px; margin:20px 0; background:var(--bg-input); border-radius:var(--radius-md); border-left:4px solid var(--primary);">
            <p><strong>L'élève :</strong> <span style="font-size:17px; font-weight:700; text-transform:uppercase;"><?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></span></p>
            <p><strong>Matricule :</strong> <?php echo htmlspecialchars($eleve['matricule']); ?></p>
            <p><strong>Né(e) le :</strong> <?php echo date('d/m/Y', strtotime($eleve['date_naissance'])); ?> à <?php echo htmlspecialchars($eleve['lieu_naissance']); ?></p>
            <p><strong>Sexe :</strong> <?php echo $eleve['sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></p>
        </div>

        <p>est régulièrement inscrit(e) en classe de <strong style="font-size:17px;"><?php echo htmlspecialchars($eleve['classe_nom']); ?></strong> dans notre établissement pour l'année scolaire <strong><?php echo ANNEE_SCOLAIRE; ?></strong>.</p>

        <p>En foi de quoi, le présent certificat lui est délivré pour servir et valoir ce que de droit.</p>

        <!-- Signature -->
        <div style="text-align:right; margin-top:50px;">
            <p>Fait à ________________, le <?php echo date('d/m/Y'); ?></p>
            <p style="margin-top:8px;"><strong>Le Proviseur</strong></p>
            <div style="height:80px;"></div>
            <p><em>(Signature et cachet)</em></p>
        </div>
    </div>

    <!-- Numéro de référence -->
    <div style="text-align:center; padding:16px; border-top:1px solid var(--border); font-size:11px; color:var(--text-muted);">
        Réf. N° <?php echo 'CS-' . date('Y') . '-' . str_pad($eleve['id'], 4, '0', STR_PAD_LEFT); ?>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

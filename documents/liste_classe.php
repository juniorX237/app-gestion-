<?php
$page_title = "Liste par classe";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;
$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();

$eleves = [];
$classe_info = null;

if ($classe_id > 0) {
    $stmt = $pdo->prepare("
        SELECT c.*, CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal
        FROM classes c 
        LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
        WHERE c.id = ?
    ");
    $stmt->execute([$classe_id]);
    $classe_info = $stmt->fetch();

    $eleves = $pdo->query("SELECT * FROM eleves WHERE classe_id = $classe_id ORDER BY nom, prenom")->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Sélection de la classe -->
<div class="card mb-3 no-print">
    <div class="card-header">
        <h3 class="card-title">📋 Sélectionner une classe</h3>
    </div>
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Classe</label>
                <select name="classe_id" class="form-control filter-auto">
                    <option value="">-- Choisir une classe --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $classe_id == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary">📋 Afficher</button>
            </div>
        </div>
    </form>
</div>

<?php if ($classe_info): ?>

<div class="no-print mb-2">
    <button onclick="printPage()" class="btn btn-success">🖨️ Imprimer</button>
</div>

<!-- En-tête imprimable -->
<div class="print-only print-header">
    <p>RÉPUBLIQUE DU CAMEROUN - Paix - Travail - Patrie</p>
    <h1><?php echo NOM_ETABLISSEMENT; ?></h1>
    <h2>LISTE DES ÉLÈVES - <?php echo htmlspecialchars($classe_info['nom']); ?></h2>
    <p>Année scolaire : <?php echo ANNEE_SCOLAIRE; ?></p>
    <?php if ($classe_info['prof_principal']): ?>
        <p>Professeur principal : <?php echo htmlspecialchars($classe_info['prof_principal']); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 <?php echo htmlspecialchars($classe_info['nom']); ?> - Effectif : <?php echo count($eleves); ?></h3>
        <?php if ($classe_info['prof_principal']): ?>
            <span class="badge badge-success">PP: <?php echo htmlspecialchars($classe_info['prof_principal']); ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($eleves)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucun élève dans cette classe</h3>
        </div>
    <?php else: ?>
        <div class="table-container" style="border:none;">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Sexe</th>
                        <th>Date de naissance</th>
                        <th>Lieu de naissance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $num = 0; foreach ($eleves as $e): $num++; ?>
                    <tr>
                        <td><?php echo $num; ?></td>
                        <td><?php echo htmlspecialchars($e['matricule']); ?></td>
                        <td><strong><?php echo htmlspecialchars($e['nom']); ?></strong></td>
                        <td><?php echo htmlspecialchars($e['prenom']); ?></td>
                        <td><?php echo $e['sexe']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($e['date_naissance'])); ?></td>
                        <td><?php echo htmlspecialchars($e['lieu_naissance']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Récapitulatif -->
        <div style="padding:16px; display:flex; gap:20px; border-top:1px solid var(--border);">
            <?php
            $garcons = count(array_filter($eleves, function($e) { return $e['sexe'] == 'M'; }));
            $filles = count($eleves) - $garcons;
            ?>
            <span class="badge badge-info">Total: <?php echo count($eleves); ?></span>
            <span class="badge badge-primary">Garçons: <?php echo $garcons; ?></span>
            <span class="badge badge-warning">Filles: <?php echo $filles; ?></span>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

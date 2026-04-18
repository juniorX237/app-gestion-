<?php
$page_title = "Tableau de bord";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Statistiques
$nb_eleves = $pdo->query("SELECT COUNT(*) FROM eleves")->fetchColumn();
$nb_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$nb_enseignants = $pdo->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();
$nb_notes = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();

// Élèves récents
$eleves_recents = $pdo->query("SELECT e.*, c.nom AS classe_nom FROM eleves e JOIN classes c ON e.classe_id = c.id ORDER BY e.date_inscription DESC LIMIT 5")->fetchAll();

// Classes avec effectifs
$classes_effectifs = $pdo->query("
    SELECT c.nom, c.cycle, c.serie, COUNT(e.id) AS effectif,
           CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal
    FROM classes c 
    LEFT JOIN eleves e ON e.classe_id = c.id
    LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
    GROUP BY c.id ORDER BY c.cycle, c.nom
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">👨‍🎓</div>
        <div class="stat-info">
            <div class="stat-label">Total Élèves</div>
            <div class="stat-value"><?php echo $nb_eleves; ?></div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">🏛️</div>
        <div class="stat-info">
            <div class="stat-label">Classes</div>
            <div class="stat-value"><?php echo $nb_classes; ?></div>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-info">
            <div class="stat-label">Enseignants</div>
            <div class="stat-value"><?php echo $nb_enseignants; ?></div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
            <div class="stat-label">Notes saisies</div>
            <div class="stat-value"><?php echo $nb_notes; ?></div>
        </div>
    </div>
</div>

<!-- Classes et effectifs -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📊 Effectifs par classe</h3>
        </div>
        <div class="table-container" style="border:none;">
            <table>
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Cycle</th>
                        <th>Effectif</th>
                        <th>Prof. Principal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes_effectifs as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['nom']); ?></strong></td>
                        <td><span class="badge <?php echo $c['cycle'] == 'premier' ? 'badge-info' : 'badge-primary'; ?>"><?php echo $c['cycle'] == 'premier' ? '1er cycle' : '2nd cycle'; ?></span></td>
                        <td><?php echo $c['effectif']; ?></td>
                        <td><?php echo $c['prof_principal'] ? htmlspecialchars($c['prof_principal']) : '<span class="text-muted">Non assigné</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🆕 Derniers élèves inscrits</h3>
            <a href="<?php echo BASE_URL; ?>/eleves/ajouter.php" class="btn btn-primary btn-sm">+ Ajouter</a>
        </div>
        <?php if (empty($eleves_recents)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>Aucun élève inscrit</h3>
                <p>Commencez par inscrire des élèves pour voir les données ici.</p>
            </div>
        <?php else: ?>
            <div class="table-container" style="border:none;">
                <table>
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>Classe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eleves_recents as $e): ?>
                        <tr>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($e['matricule']); ?></span></td>
                            <td><a href="<?php echo BASE_URL; ?>/eleves/profil.php?id=<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom']); ?></a></td>
                            <td><?php echo htmlspecialchars($e['classe_nom']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

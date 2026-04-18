<?php
$page_title = "Liste des élèves";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Filtre par classe
$classe_filter = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;

// Récupérer les classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();

// Récupérer les élèves
$sql = "SELECT e.*, c.nom AS classe_nom FROM eleves e JOIN classes c ON e.classe_id = c.id";
if ($classe_filter > 0) {
    $sql .= " WHERE e.classe_id = " . $classe_filter;
}
$sql .= " ORDER BY c.nom, e.nom, e.prenom";
$eleves = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Barre d'actions -->
<div class="flex-between mb-3">
    <form method="GET" class="flex gap-2" style="align-items:center;">
        <select name="classe_id" class="form-control filter-auto" style="width:auto; min-width:200px;">
            <option value="0">Toutes les classes</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo ($classe_filter == $c['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['nom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="ajouter.php" class="btn btn-primary">+ Nouvel élève</a>
</div>

<!-- Message de succès -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<!-- Tableau des élèves -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">👨‍🎓 <?php echo $classe_filter ? 'Élèves de la classe' : 'Tous les élèves'; ?> (<?php echo count($eleves); ?>)</h3>
    </div>

    <?php if (empty($eleves)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucun élève trouvé</h3>
            <p>Ajoutez des élèves pour les voir apparaître ici.</p>
        </div>
    <?php else: ?>
        <div class="table-container" style="border:none;">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Sexe</th>
                        <th>Classe</th>
                        <th>Date de naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eleves as $e): ?>
                    <tr>
                        <td>
                            <?php if ($e['photo']): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/photos/<?php echo htmlspecialchars($e['photo']); ?>" alt="Photo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white;">
                                    <?php echo strtoupper(substr($e['nom'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($e['matricule']); ?></span></td>
                        <td>
                            <a href="profil.php?id=<?php echo $e['id']; ?>" style="font-weight:600;">
                                <?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom']); ?>
                            </a>
                        </td>
                        <td><span class="badge <?php echo $e['sexe'] == 'M' ? 'badge-info' : 'badge-warning'; ?>"><?php echo $e['sexe']; ?></span></td>
                        <td><?php echo htmlspecialchars($e['classe_nom']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($e['date_naissance'])); ?></td>
                        <td>
                            <div class="flex gap-1">
                                <a href="profil.php?id=<?php echo $e['id']; ?>" class="btn btn-outline btn-sm" title="Profil">👁️</a>
                                <a href="modifier.php?id=<?php echo $e['id']; ?>" class="btn btn-outline btn-sm" title="Modifier">✏️</a>
                                <a href="supprimer.php?id=<?php echo $e['id']; ?>" class="btn btn-danger btn-sm confirm-delete" title="Supprimer">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

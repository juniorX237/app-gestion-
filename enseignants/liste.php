<?php
$page_title = "Gestion des enseignants";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Réservé aux admins
if (!$_SESSION['est_admin']) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$success = '';

// Attribution de professeur principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'attribuer_pp') {
        $classe_id = (int)$_POST['classe_id'];
        $enseignant_id = (int)$_POST['enseignant_id'];

        if ($enseignant_id == 0) {
            $stmt = $pdo->prepare("UPDATE classes SET prof_principal_id = NULL WHERE id = ?");
            $stmt->execute([$classe_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE classes SET prof_principal_id = ? WHERE id = ?");
            $stmt->execute([$enseignant_id, $classe_id]);
        }
        $success = 'Professeur principal mis à jour avec succès !';
    } elseif ($_POST['action'] === 'supprimer') {
        $id = (int)$_POST['enseignant_id'];
        if ($id != $_SESSION['enseignant_id']) {
            $stmt = $pdo->prepare("DELETE FROM enseignants WHERE id = ? AND est_admin = 0");
            $stmt->execute([$id]);
            $success = 'Enseignant supprimé.';
        }
    } elseif ($_POST['action'] === 'toggle_admin') {
        $id = (int)$_POST['enseignant_id'];
        $stmt = $pdo->prepare("UPDATE enseignants SET est_admin = NOT est_admin WHERE id = ? AND id != ?");
        $stmt->execute([$id, $_SESSION['enseignant_id']]);
        $success = 'Rôle mis à jour.';
    }
}

// Récupérer tous les enseignants
$enseignants = $pdo->query("SELECT * FROM enseignants ORDER BY nom, prenom")->fetchAll();

// Récupérer les classes avec PP
$classes = $pdo->query("
    SELECT c.*, CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal_nom
    FROM classes c
    LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
    ORDER BY c.cycle, c.nom
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Liste des enseignants -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">👨‍🏫 Enseignants (<?php echo count($enseignants); ?>)</h3>
    </div>
    <div class="table-container" style="border:none;">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Spécialité</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignants as $e): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($e['nom']); ?></strong></td>
                    <td><?php echo htmlspecialchars($e['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($e['email']); ?></td>
                    <td><?php echo htmlspecialchars($e['telephone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($e['specialite'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $e['est_admin'] ? 'badge-warning' : 'badge-info'; ?>">
                            <?php echo $e['est_admin'] ? 'Admin' : 'Enseignant'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($e['id'] != $_SESSION['enseignant_id']): ?>
                        <div class="flex gap-1">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_admin">
                                <input type="hidden" name="enseignant_id" value="<?php echo $e['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Changer le rôle">🔄</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet enseignant ?');">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="enseignant_id" value="<?php echo $e['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </div>
                        <?php else: ?>
                            <span class="text-muted">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attribution des professeurs principaux -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🏛️ Professeurs principaux par classe</h3>
    </div>
    <div class="table-container" style="border:none;">
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Cycle</th>
                    <th>Prof. Principal actuel</th>
                    <th>Attribuer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $c): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['nom']); ?></strong></td>
                    <td><span class="badge <?php echo $c['cycle'] == 'premier' ? 'badge-info' : 'badge-primary'; ?>">
                        <?php echo $c['cycle'] == 'premier' ? '1er cycle' : '2nd cycle'; ?>
                    </span></td>
                    <td>
                        <?php if ($c['prof_principal_nom']): ?>
                            <span class="badge badge-success"><?php echo htmlspecialchars($c['prof_principal_nom']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Non assigné</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="flex gap-1" style="align-items:center;">
                            <input type="hidden" name="action" value="attribuer_pp">
                            <input type="hidden" name="classe_id" value="<?php echo $c['id']; ?>">
                            <select name="enseignant_id" class="form-control" style="width:auto; min-width:200px;">
                                <option value="0">-- Aucun --</option>
                                <?php foreach ($enseignants as $e): ?>
                                    <option value="<?php echo $e['id']; ?>" <?php echo $c['prof_principal_id'] == $e['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($e['prenom'] . ' ' . $e['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-success btn-sm">✅</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

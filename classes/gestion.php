<?php
$page_title = "Gestion des classes";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Récupérer les classes avec effectifs et PP
$classes = $pdo->query("
    SELECT c.*, 
           COUNT(e.id) AS effectif,
           CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal
    FROM classes c 
    LEFT JOIN eleves e ON e.classe_id = c.id
    LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
    GROUP BY c.id
    ORDER BY c.cycle, c.nom
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">🏛️ Classes (<?php echo count($classes); ?>)</h3>
    </div>

    <!-- Premier cycle -->
    <h4 style="padding:16px 16px 8px; color:var(--info); font-size:14px; text-transform:uppercase; letter-spacing:1px;">📘 Premier Cycle</h4>
    <div class="table-container" style="border:none; margin-bottom:24px;">
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Code</th>
                    <th>Effectif</th>
                    <th>Prof. Principal</th>
                    <th>Matières</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $c): if ($c['cycle'] != 'premier') continue; ?>
                <?php
                    $nb_matieres = $pdo->query("SELECT COUNT(*) FROM matieres_classes WHERE classe_id = {$c['id']}")->fetchColumn();
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['nom']); ?></strong></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($c['code']); ?></span></td>
                    <td><?php echo $c['effectif']; ?> / <?php echo $c['effectif_max']; ?></td>
                    <td>
                        <?php if ($c['prof_principal']): ?>
                            <span class="badge badge-success"><?php echo htmlspecialchars($c['prof_principal']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Non assigné</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $nb_matieres; ?> matières</td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/eleves/liste.php?classe_id=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">👁️ Voir</a>
                        <a href="<?php echo BASE_URL; ?>/documents/liste_classe.php?classe_id=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">📋 Liste</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Second cycle -->
    <h4 style="padding:16px 16px 8px; color:var(--primary-light); font-size:14px; text-transform:uppercase; letter-spacing:1px;">📗 Second Cycle</h4>
    <div class="table-container" style="border:none;">
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Code</th>
                    <th>Série</th>
                    <th>Effectif</th>
                    <th>Prof. Principal</th>
                    <th>Matières</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $c): if ($c['cycle'] != 'second') continue; ?>
                <?php
                    $nb_matieres = $pdo->query("SELECT COUNT(*) FROM matieres_classes WHERE classe_id = {$c['id']}")->fetchColumn();
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['nom']); ?></strong></td>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($c['code']); ?></span></td>
                    <td><span class="badge badge-warning">Série <?php echo htmlspecialchars($c['serie']); ?></span></td>
                    <td><?php echo $c['effectif']; ?> / <?php echo $c['effectif_max']; ?></td>
                    <td>
                        <?php if ($c['prof_principal']): ?>
                            <span class="badge badge-success"><?php echo htmlspecialchars($c['prof_principal']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Non assigné</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $nb_matieres; ?> matières</td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/eleves/liste.php?classe_id=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">👁️ Voir</a>
                        <a href="<?php echo BASE_URL; ?>/documents/liste_classe.php?classe_id=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">📋 Liste</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

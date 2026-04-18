<?php
$page_title = "Relevé de notes";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$eleve_id = isset($_GET['eleve_id']) ? (int)$_GET['eleve_id'] : 0;
$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();
$classe_filter = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;
$eleves_liste = [];

if ($classe_filter > 0) {
    $eleves_liste = $pdo->query("SELECT * FROM eleves WHERE classe_id = $classe_filter ORDER BY nom, prenom")->fetchAll();
}

$eleve = null;
$notes_data = [];

if ($eleve_id > 0) {
    // Récupérer l'élève
    $stmt = $pdo->prepare("
        SELECT e.*, c.nom AS classe_nom, c.cycle, c.serie,
               CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal
        FROM eleves e 
        JOIN classes c ON e.classe_id = c.id
        LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
        WHERE e.id = ?
    ");
    $stmt->execute([$eleve_id]);
    $eleve = $stmt->fetch();

    if ($eleve) {
        // Récupérer toutes les matières de la classe avec leurs notes
        $matieres = $pdo->query("
            SELECT mc.id AS mc_id, m.nom AS matiere_nom, mc.coefficient
            FROM matieres_classes mc
            JOIN matieres m ON mc.matiere_id = m.id
            WHERE mc.classe_id = {$eleve['classe_id']}
            ORDER BY m.nom
        ")->fetchAll();

        foreach ($matieres as $mat) {
            $stmt = $pdo->prepare("SELECT sequence_num, note FROM notes WHERE eleve_id = ? AND matiere_classe_id = ? ORDER BY sequence_num");
            $stmt->execute([$eleve_id, $mat['mc_id']]);
            $seq_notes = [];
            while ($row = $stmt->fetch()) {
                $seq_notes[$row['sequence_num']] = $row['note'];
            }
            $notes_data[] = [
                'matiere' => $mat['matiere_nom'],
                'coef' => $mat['coefficient'],
                'notes' => $seq_notes
            ];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Sélection de l'élève -->
<div class="card mb-3 no-print">
    <div class="card-header">
        <h3 class="card-title">🔍 Sélectionner un élève</h3>
    </div>
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Classe</label>
                <select name="classe_id" class="form-control filter-auto">
                    <option value="">-- Choisir une classe --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $classe_filter == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Élève</label>
                <select name="eleve_id" class="form-control">
                    <option value="">-- Choisir un élève --</option>
                    <?php foreach ($eleves_liste as $el): ?>
                        <option value="<?php echo $el['id']; ?>" <?php echo $eleve_id == $el['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($el['nom'] . ' ' . $el['prenom'] . ' (' . $el['matricule'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">📄 Afficher le relevé</button>
    </form>
</div>

<!-- Relevé de notes -->
<?php if ($eleve): ?>

    <div class="no-print mb-2">
        <button onclick="printPage()" class="btn btn-success">🖨️ Imprimer</button>
    </div>

    <!-- En-tête imprimable -->
    <div class="print-only print-header">
        <p>RÉPUBLIQUE DU CAMEROUN</p>
        <p>Paix - Travail - Patrie</p>
        <h1><?php echo NOM_ETABLISSEMENT; ?></h1>
        <h2>RELEVÉ DE NOTES</h2>
        <p>Année scolaire : <?php echo ANNEE_SCOLAIRE; ?></p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📄 Relevé de notes - <?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></h3>
        </div>

        <!-- Infos élève -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; padding:16px; background:var(--bg-input); border-radius:var(--radius-md);">
            <div><strong>Nom :</strong> <?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></div>
            <div><strong>Matricule :</strong> <?php echo htmlspecialchars($eleve['matricule']); ?></div>
            <div><strong>Classe :</strong> <?php echo htmlspecialchars($eleve['classe_nom']); ?></div>
            <div><strong>Né(e) le :</strong> <?php echo date('d/m/Y', strtotime($eleve['date_naissance'])); ?> à <?php echo htmlspecialchars($eleve['lieu_naissance']); ?></div>
        </div>

        <!-- Tableau des notes -->
        <div class="table-container" style="border:none;">
            <table>
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Coef</th>
                        <th>Séq 1</th>
                        <th>Séq 2</th>
                        <th>Moy T1</th>
                        <th>Séq 3</th>
                        <th>Séq 4</th>
                        <th>Moy T2</th>
                        <th>Séq 5</th>
                        <th>Séq 6</th>
                        <th>Moy T3</th>
                        <th>Moy An.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_coef_gen = 0;
                    $total_moy_gen = 0;
                    $has_notes = false;

                    foreach ($notes_data as $nd):
                        $n = $nd['notes'];
                        $coef = $nd['coef'];

                        // Moyennes trimestrielles
                        $t1 = (isset($n[1]) && isset($n[2])) ? round(($n[1] + $n[2]) / 2, 2) : (isset($n[1]) ? $n[1] : (isset($n[2]) ? $n[2] : null));
                        $t2 = (isset($n[3]) && isset($n[4])) ? round(($n[3] + $n[4]) / 2, 2) : (isset($n[3]) ? $n[3] : (isset($n[4]) ? $n[4] : null));
                        $t3 = (isset($n[5]) && isset($n[6])) ? round(($n[5] + $n[6]) / 2, 2) : (isset($n[5]) ? $n[5] : (isset($n[6]) ? $n[6] : null));

                        // Moyenne annuelle
                        $trims = array_filter([$t1, $t2, $t3], function($v) { return $v !== null; });
                        $moy_an = !empty($trims) ? round(array_sum($trims) / count($trims), 2) : null;

                        if ($moy_an !== null) {
                            $total_moy_gen += $moy_an * $coef;
                            $total_coef_gen += $coef;
                            $has_notes = true;
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($nd['matiere']); ?></strong></td>
                        <td><?php echo $coef; ?></td>
                        <td class="<?php echo isset($n[1]) ? ($n[1] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[1] ?? '-'; ?></td>
                        <td class="<?php echo isset($n[2]) ? ($n[2] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[2] ?? '-'; ?></td>
                        <td><strong class="<?php echo $t1 !== null ? ($t1 >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $t1 ?? '-'; ?></strong></td>
                        <td class="<?php echo isset($n[3]) ? ($n[3] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[3] ?? '-'; ?></td>
                        <td class="<?php echo isset($n[4]) ? ($n[4] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[4] ?? '-'; ?></td>
                        <td><strong class="<?php echo $t2 !== null ? ($t2 >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $t2 ?? '-'; ?></strong></td>
                        <td class="<?php echo isset($n[5]) ? ($n[5] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[5] ?? '-'; ?></td>
                        <td class="<?php echo isset($n[6]) ? ($n[6] >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $n[6] ?? '-'; ?></td>
                        <td><strong class="<?php echo $t3 !== null ? ($t3 >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $t3 ?? '-'; ?></strong></td>
                        <td><strong class="<?php echo $moy_an !== null ? ($moy_an >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $moy_an ?? '-'; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($has_notes): 
                    $moy_gen = round($total_moy_gen / $total_coef_gen, 2);
                ?>
                <tfoot>
                    <tr style="background:rgba(59,130,246,0.1);">
                        <td colspan="11" class="text-right"><strong>MOYENNE GÉNÉRALE</strong></td>
                        <td>
                            <strong style="font-size:16px;" class="<?php echo $moy_gen >= 10 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $moy_gen; ?>/20
                            </strong>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

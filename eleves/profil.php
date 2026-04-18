<?php
$page_title = "Profil de l'élève";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id == 0) { header('Location: liste.php'); exit; }

// Récupérer l'élève avec sa classe
$stmt = $pdo->prepare("
    SELECT e.*, c.nom AS classe_nom, c.cycle, c.serie,
           CONCAT(ens.prenom, ' ', ens.nom) AS prof_principal
    FROM eleves e 
    JOIN classes c ON e.classe_id = c.id
    LEFT JOIN enseignants ens ON c.prof_principal_id = ens.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$eleve = $stmt->fetch();
if (!$eleve) { header('Location: liste.php'); exit; }

// Récupérer les notes de l'élève
$notes = $pdo->query("
    SELECT n.*, m.nom AS matiere_nom, mc.coefficient
    FROM notes n
    JOIN matieres_classes mc ON n.matiere_classe_id = mc.id
    JOIN matieres m ON mc.matiere_id = m.id
    WHERE n.eleve_id = $id
    ORDER BY m.nom, n.sequence_num
")->fetchAll();

// Calculer la moyenne générale
$moyennes = [];
foreach ($notes as $n) {
    $key = $n['matiere_nom'];
    if (!isset($moyennes[$key])) {
        $moyennes[$key] = ['total' => 0, 'count' => 0, 'coef' => $n['coefficient']];
    }
    $moyennes[$key]['total'] += $n['note'];
    $moyennes[$key]['count']++;
}

$moyenne_generale = 0;
$total_coef = 0;
foreach ($moyennes as $m) {
    $moy_matiere = $m['total'] / $m['count'];
    $moyenne_generale += $moy_matiere * $m['coef'];
    $total_coef += $m['coef'];
}
if ($total_coef > 0) {
    $moyenne_generale = round($moyenne_generale / $total_coef, 2);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="mb-3 no-print">
    <a href="liste.php" class="btn btn-outline btn-sm">← Retour à la liste</a>
    <a href="modifier.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">✏️ Modifier</a>
    <a href="<?php echo BASE_URL; ?>/documents/certificat.php?id=<?php echo $id; ?>" class="btn btn-success btn-sm">🎓 Certificat</a>
    <a href="<?php echo BASE_URL; ?>/notes/releve.php?eleve_id=<?php echo $id; ?>" class="btn btn-outline btn-sm">📄 Relevé de notes</a>
</div>

<!-- En-tête du profil -->
<div class="profile-header">
    <?php if ($eleve['photo']): ?>
        <img src="<?php echo BASE_URL; ?>/uploads/photos/<?php echo htmlspecialchars($eleve['photo']); ?>" alt="Photo" class="profile-photo">
    <?php else: ?>
        <div class="profile-photo-placeholder">
            <?php echo strtoupper(substr($eleve['nom'], 0, 1) . substr($eleve['prenom'], 0, 1)); ?>
        </div>
    <?php endif; ?>
    <div class="profile-info">
        <h2><?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></h2>
        <p><span class="badge badge-primary"><?php echo htmlspecialchars($eleve['matricule']); ?></span></p>
        <p style="margin-top:8px;">
            <span class="badge <?php echo $eleve['cycle'] == 'premier' ? 'badge-info' : 'badge-primary'; ?>" style="font-size:13px;">
                <?php echo htmlspecialchars($eleve['classe_nom']); ?>
            </span>
            <?php if ($total_coef > 0): ?>
                <span class="badge <?php echo $moyenne_generale >= 10 ? 'badge-success' : 'badge-danger'; ?>" style="font-size:13px; margin-left:8px;">
                    Moy: <?php echo $moyenne_generale; ?>/20
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Informations détaillées -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">📋 Informations personnelles</h3>
    </div>
    <div class="profile-details">
        <div class="detail-item">
            <div class="detail-icon">📅</div>
            <div>
                <div class="detail-label">Date de naissance</div>
                <div class="detail-value"><?php echo date('d/m/Y', strtotime($eleve['date_naissance'])); ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">📍</div>
            <div>
                <div class="detail-label">Lieu de naissance</div>
                <div class="detail-value"><?php echo htmlspecialchars($eleve['lieu_naissance']); ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">⚧</div>
            <div>
                <div class="detail-label">Sexe</div>
                <div class="detail-value"><?php echo $eleve['sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">🏛️</div>
            <div>
                <div class="detail-label">Classe</div>
                <div class="detail-value"><?php echo htmlspecialchars($eleve['classe_nom']); ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">📆</div>
            <div>
                <div class="detail-label">Date d'admission</div>
                <div class="detail-value"><?php echo $eleve['date_admission'] ? date('d/m/Y', strtotime($eleve['date_admission'])) : 'Non renseignée'; ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">✍️</div>
            <div>
                <div class="detail-label">Date d'inscription</div>
                <div class="detail-value"><?php echo date('d/m/Y à H:i', strtotime($eleve['date_inscription'])); ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">👤</div>
            <div>
                <div class="detail-label">Parent / Tuteur</div>
                <div class="detail-value"><?php echo $eleve['nom_parent'] ? htmlspecialchars($eleve['nom_parent']) : 'Non renseigné'; ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">📞</div>
            <div>
                <div class="detail-label">Téléphone parent</div>
                <div class="detail-value"><?php echo $eleve['telephone_parent'] ? htmlspecialchars($eleve['telephone_parent']) : 'Non renseigné'; ?></div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">👨‍🏫</div>
            <div>
                <div class="detail-label">Professeur principal</div>
                <div class="detail-value"><?php echo $eleve['prof_principal'] ? htmlspecialchars($eleve['prof_principal']) : 'Non assigné'; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Notes récentes -->
<?php if (!empty($notes)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📝 Notes</h3>
    </div>
    <div class="table-container" style="border:none;">
        <table>
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Coef</th>
                    <th>Séq 1</th>
                    <th>Séq 2</th>
                    <th>Séq 3</th>
                    <th>Séq 4</th>
                    <th>Séq 5</th>
                    <th>Séq 6</th>
                    <th>Moyenne</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $notes_par_matiere = [];
                foreach ($notes as $n) {
                    $notes_par_matiere[$n['matiere_nom']]['coef'] = $n['coefficient'];
                    $notes_par_matiere[$n['matiere_nom']]['seq'][$n['sequence_num']] = $n['note'];
                }
                foreach ($notes_par_matiere as $matiere => $data):
                    $total = array_sum($data['seq']);
                    $count = count($data['seq']);
                    $moy = $count > 0 ? round($total / $count, 2) : '-';
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($matiere); ?></strong></td>
                    <td><?php echo $data['coef']; ?></td>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <td class="<?php echo isset($data['seq'][$i]) ? ($data['seq'][$i] >= 10 ? 'text-success' : 'text-danger') : ''; ?>">
                            <?php echo isset($data['seq'][$i]) ? $data['seq'][$i] : '-'; ?>
                        </td>
                    <?php endfor; ?>
                    <td><strong class="<?php echo is_numeric($moy) ? ($moy >= 10 ? 'text-success' : 'text-danger') : ''; ?>"><?php echo $moy; ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

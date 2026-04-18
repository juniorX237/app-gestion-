<?php
$page_title = "Saisie des notes";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$classes = $pdo->query("SELECT * FROM classes ORDER BY cycle, nom")->fetchAll();

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;
$matiere_classe_id = isset($_GET['matiere_classe_id']) ? (int)$_GET['matiere_classe_id'] : 0;
$sequence = isset($_GET['sequence']) ? (int)$_GET['sequence'] : 1;

$matieres = [];
$eleves = [];
$notes_existantes = [];
$success = '';
$error = '';

// Charger les matières de la classe sélectionnée
if ($classe_id > 0) {
    $matieres = $pdo->query("
        SELECT mc.id, m.nom AS matiere_nom, mc.coefficient
        FROM matieres_classes mc
        JOIN matieres m ON mc.matiere_id = m.id
        WHERE mc.classe_id = $classe_id
        ORDER BY m.nom
    ")->fetchAll();
}

// Charger les élèves et notes existantes
if ($classe_id > 0 && $matiere_classe_id > 0) {
    $eleves = $pdo->query("SELECT * FROM eleves WHERE classe_id = $classe_id ORDER BY nom, prenom")->fetchAll();

    // Notes existantes pour cette matière/séquence
    $stmt = $pdo->prepare("SELECT eleve_id, note FROM notes WHERE matiere_classe_id = ? AND sequence_num = ?");
    $stmt->execute([$matiere_classe_id, $sequence]);
    while ($row = $stmt->fetch()) {
        $notes_existantes[$row['eleve_id']] = $row['note'];
    }
}

// Traitement du formulaire de saisie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notes'])) {
    $mc_id = (int)$_POST['matiere_classe_id'];
    $seq = (int)$_POST['sequence'];
    $enseignant_id = $_SESSION['enseignant_id'];
    $count = 0;

    foreach ($_POST['notes'] as $eleve_id => $note) {
        $note = trim($note);
        if ($note === '') continue;
        $note = (float)$note;
        if ($note < 0 || $note > 20) continue;

        $eleve_id = (int)$eleve_id;

        // Upsert : insérer ou mettre à jour
        $stmt = $pdo->prepare("
            INSERT INTO notes (eleve_id, matiere_classe_id, sequence_num, note, enseignant_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE note = VALUES(note), date_saisie = NOW()
        ");
        $stmt->execute([$eleve_id, $mc_id, $seq, $note, $enseignant_id]);
        $count++;
    }

    $success = "$count note(s) enregistrée(s) avec succès !";

    // Recharger les notes
    $stmt = $pdo->prepare("SELECT eleve_id, note FROM notes WHERE matiere_classe_id = ? AND sequence_num = ?");
    $stmt->execute([$mc_id, $seq]);
    $notes_existantes = [];
    while ($row = $stmt->fetch()) {
        $notes_existantes[$row['eleve_id']] = $row['note'];
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">🔍 Sélectionner la classe, la matière et la séquence</h3>
    </div>
    <form method="GET" action="">
        <div class="form-row-3">
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
            <div class="form-group">
                <label class="form-label">Matière</label>
                <select name="matiere_classe_id" class="form-control" <?php echo empty($matieres) ? 'disabled' : ''; ?>>
                    <option value="">-- Choisir une matière --</option>
                    <?php foreach ($matieres as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $matiere_classe_id == $m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['matiere_nom']); ?> (Coef. <?php echo $m['coefficient']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Séquence</label>
                <select name="sequence" class="form-control">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $sequence == $i ? 'selected' : ''; ?>>
                            Séquence <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">📋 Charger</button>
    </form>
</div>

<!-- Formulaire de saisie de notes -->
<?php if (!empty($eleves) && $matiere_classe_id > 0): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📝 Saisie des notes - Séquence <?php echo $sequence; ?></h3>
        <span class="badge badge-primary"><?php echo count($eleves); ?> élèves</span>
    </div>
    <form method="POST" action="">
        <input type="hidden" name="matiere_classe_id" value="<?php echo $matiere_classe_id; ?>">
        <input type="hidden" name="sequence" value="<?php echo $sequence; ?>">

        <div class="table-container" style="border:none;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Note /20</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $num = 0; foreach ($eleves as $e): $num++; ?>
                    <tr>
                        <td><?php echo $num; ?></td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($e['matricule']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($e['nom'] . ' ' . $e['prenom']); ?></strong></td>
                        <td>
                            <input type="number" name="notes[<?php echo $e['id']; ?>]" 
                                   class="form-control" style="width:100px;" 
                                   min="0" max="20" step="0.25"
                                   value="<?php echo isset($notes_existantes[$e['id']]) ? $notes_existantes[$e['id']] : ''; ?>"
                                   placeholder="0-20">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success btn-lg">💾 Enregistrer les notes</button>
        </div>
    </form>
</div>
<?php elseif ($classe_id > 0 && $matiere_classe_id > 0 && empty($eleves)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucun élève dans cette classe</h3>
            <p>Ajoutez d'abord des élèves dans cette classe pour pouvoir saisir des notes.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

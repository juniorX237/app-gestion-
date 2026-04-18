<?php
// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="school-name">🏫 <?php echo NOM_ETABLISSEMENT; ?></div>
        <div class="school-year"><?php echo ANNEE_SCOLAIRE; ?></div>
    </div>

    <nav class="sidebar-nav">
        <!-- Menu principal -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Principal</div>
            <a href="<?php echo BASE_URL; ?>/dashboard.php" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span>Tableau de bord</span>
            </a>
        </div>

        <!-- Gestion scolaire -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Gestion Scolaire</div>
            <a href="<?php echo BASE_URL; ?>/eleves/liste.php" class="sidebar-link <?php echo ($current_dir == 'eleves') ? 'active' : ''; ?>">
                <span class="icon">👨‍🎓</span>
                <span>Élèves</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/classes/gestion.php" class="sidebar-link <?php echo ($current_dir == 'classes') ? 'active' : ''; ?>">
                <span class="icon">🏛️</span>
                <span>Classes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/notes/saisie.php" class="sidebar-link <?php echo ($current_dir == 'notes') ? 'active' : ''; ?>">
                <span class="icon">📝</span>
                <span>Notes</span>
            </a>
        </div>

        <!-- Documents -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Documents</div>
            <a href="<?php echo BASE_URL; ?>/documents/liste_classe.php" class="sidebar-link <?php echo ($current_page == 'liste_classe.php') ? 'active' : ''; ?>">
                <span class="icon">📋</span>
                <span>Liste par classe</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/notes/releve.php" class="sidebar-link <?php echo ($current_page == 'releve.php') ? 'active' : ''; ?>">
                <span class="icon">📄</span>
                <span>Relevé de notes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/documents/certificat.php" class="sidebar-link <?php echo ($current_page == 'certificat.php') ? 'active' : ''; ?>">
                <span class="icon">🎓</span>
                <span>Certificat de scolarité</span>
            </a>
        </div>

        <?php if (isset($_SESSION['est_admin']) && $_SESSION['est_admin']): ?>
        <!-- Administration -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Administration</div>
            <a href="<?php echo BASE_URL; ?>/enseignants/liste.php" class="sidebar-link <?php echo ($current_dir == 'enseignants') ? 'active' : ''; ?>">
                <span class="icon">👨‍🏫</span>
                <span>Enseignants</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- User info -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?php echo strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')); ?></div>
                <div class="sidebar-user-role"><?php echo ($_SESSION['est_admin'] ?? 0) ? 'Administrateur' : 'Enseignant'; ?></div>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="sidebar-link mt-1" style="color: #ef4444;">
            <span class="icon">🚪</span>
            <span>Déconnexion</span>
        </a>
    </div>
</aside>

<!-- Main content area -->
<div class="main-content">
    <!-- Top bar -->
    <div class="top-bar">
        <button id="menuToggle" class="btn btn-icon btn-outline" style="display:none;">☰</button>
        <h1 class="top-bar-title"><?php echo isset($page_title) ? $page_title : 'Tableau de bord'; ?></h1>
        <div class="top-bar-actions">
            <span class="text-muted" style="font-size:13px;"><?php echo date('d/m/Y'); ?></span>
        </div>
    </div>
    <div class="page-content">

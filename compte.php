<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$user = getCurrentUser();

// Annulation d'une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_id'])) {
    $rid = (int)$_POST['annuler_id'];
    $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulée' WHERE id = ? AND user_id = ? AND statut = 'en_attente'");
    $stmt->execute([$rid, $user['id']]);
    setFlash('success', 'Réservation annulée.');
    header('Location: /rentit/compte.php');
    exit;
}

// Récupère les réservations de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.*, p.nom AS produit_nom, p.categorie, p.prix_jour
    FROM reservations r
    JOIN produits p ON r.produit_id = p.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();

// Stats
$stats = ['en_attente' => 0, 'confirmée' => 0, 'en_cours' => 0, 'terminée' => 0, 'annulée' => 0];
foreach ($reservations as $r) { $stats[$r['statut']]++; }

$icons = ['PC portable' => '💻', 'Écran' => '🖥️', 'Accessoire' => '🖱️', 'Serveur' => '🗄️', 'Autre' => '📦'];

$pageTitle = 'Mes réservations — RentIT';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1>Mes réservations</h1>
        <p>Bonjour <?= htmlspecialchars($user['nom']) ?> — retrouvez toutes vos demandes de location</p>
    </div>
    <a href="/rentit/index.php" class="btn btn-primary">Voir le catalogue</a>
</div>

<!-- STATS -->
<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; margin-bottom:2rem;">
    <?php $kpis = [
        ['label' => 'En attente',  'value' => $stats['en_attente'], 'color' => '#c68b2a', 'bg' => '#fef3e2'],
        ['label' => 'Confirmées',  'value' => $stats['confirmée'],  'color' => '#2d6a4f', 'bg' => '#e8f5ee'],
        ['label' => 'En cours',    'value' => $stats['en_cours'],   'color' => '#1a4a6a', 'bg' => '#e8f0f8'],
        ['label' => 'Terminées',   'value' => $stats['terminée'],   'color' => '#7a7870', 'bg' => '#f5f3ee'],
    ];
    foreach ($kpis as $k): ?>
    <div class="card" style="border-top:3px solid <?= $k['color'] ?>; background:<?= $k['bg'] ?>;">
        <div style="font-family:'Clash Display',sans-serif; font-weight:700; font-size:2rem; color:<?= $k['color'] ?>;"><?= $k['value'] ?></div>
        <div style="color:var(--muted); font-size:0.8rem; margin-top:0.2rem;"><?= $k['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($reservations)): ?>
    <div class="card" style="text-align:center; padding:3rem; color:var(--muted);">
        <div style="font-size:3rem; margin-bottom:1rem;">📋</div>
        <p>Vous n'avez pas encore de réservation.</p>
        <a href="/rentit/index.php" class="btn btn-dark" style="margin-top:1rem;">Parcourir le catalogue</a>
    </div>
<?php else: ?>
    <div style="display:flex; flex-direction:column; gap:1rem;">
        <?php foreach ($reservations as $r):
            $icon = $icons[$r['categorie']] ?? '📦';
            $nb_jours = (new DateTime($r['date_debut']))->diff(new DateTime($r['date_fin']))->days;
        ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="display:grid; grid-template-columns:80px 1fr auto; align-items:center;">

                <!-- Icône -->
                <div style="background:var(--surface); height:100%; display:flex; align-items:center; justify-content:center; padding:1.5rem; font-size:2rem;">
                    <?= $icon ?>
                </div>

                <!-- Infos -->
                <div style="padding:1.25rem 1.5rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.4rem;">
                        <span style="font-family:'Clash Display',sans-serif; font-weight:600; font-size:1rem;"><?= htmlspecialchars($r['produit_nom']) ?></span>
                        <span class="badge badge-<?= $r['statut'] ?>"><?= str_replace('_', ' ', $r['statut']) ?></span>
                    </div>
                    <div style="display:flex; gap:1.5rem; font-size:0.85rem; color:var(--muted); flex-wrap:wrap;">
                        <span>📅 Du <strong style="color:var(--text);"><?= date('d/m/Y', strtotime($r['date_debut'])) ?></strong> au <strong style="color:var(--text);"><?= date('d/m/Y', strtotime($r['date_fin'])) ?></strong></span>
                        <span>⏱ <?= $r['nb_jours'] ?> jour<?= $r['nb_jours'] > 1 ? 's' : '' ?></span>
                        <span>💰 <strong style="color:var(--cta);"><?= number_format($r['prix_total'], 2, ',', ' ') ?>€</strong></span>
                    </div>
                    <?php if ($r['message']): ?>
                        <div style="margin-top:0.5rem; font-size:0.82rem; color:var(--muted); font-style:italic;">"<?= htmlspecialchars($r['message']) ?>"</div>
                    <?php endif; ?>
                    <div style="margin-top:0.4rem; font-size:0.75rem; color:var(--muted);">Demande faite le <?= date('d/m/Y à H:i', strtotime($r['created_at'])) ?></div>
                </div>

                <!-- Actions -->
                <div style="padding:1.25rem; display:flex; flex-direction:column; gap:0.5rem; align-items:flex-end;">
                    <a href="/rentit/produit.php?id=<?= $r['produit_id'] ?>" class="btn btn-secondary btn-sm">Voir le produit</a>
                    <?php if ($r['statut'] === 'en_attente'): ?>
                    <form method="POST" onsubmit="return confirm('Annuler cette réservation ?')">
                        <input type="hidden" name="annuler_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#fdf0ee; color:var(--danger); border:1px solid #f5c6c3;">Annuler</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

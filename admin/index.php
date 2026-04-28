<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
requireAdmin();

// Stats
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(statut = 'en_attente') AS en_attente,
        SUM(statut = 'confirmée')  AS confirmees,
        SUM(statut = 'en_cours')   AS en_cours,
        SUM(statut = 'terminée')   AS terminees,
        SUM(prix_total)            AS chiffre_affaires
    FROM reservations
")->fetch();

$nb_produits = $pdo->query("SELECT COUNT(*) FROM produits WHERE disponible = 1")->fetchColumn();
$nb_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Dernières réservations
$dernières = $pdo->query("
    SELECT r.*, u.nom AS client, p.nom AS produit
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN produits p ON r.produit_id = p.id
    ORDER BY r.created_at DESC LIMIT 10
")->fetchAll();

$pageTitle = 'Administration — RentIT';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Tableau de bord</h1>
    <p>Gestion des réservations et du catalogue</p>
</div>

<!-- KPIs -->
<div style="display:grid; grid-template-columns:repeat(6,1fr); gap:1rem; margin-bottom:2rem;">
    <?php $kpis = [
        ['label'=>'Total réservations', 'value'=>$stats['total'],          'color'=>'var(--text)'],
        ['label'=>'En attente',         'value'=>$stats['en_attente'],     'color'=>'#c68b2a'],
        ['label'=>'Confirmées',         'value'=>$stats['confirmees'],     'color'=>'#2d6a4f'],
        ['label'=>'En cours',           'value'=>$stats['en_cours'],       'color'=>'#1a4a6a'],
        ['label'=>'Produits actifs',    'value'=>$nb_produits,             'color'=>'var(--accent)'],
        ['label'=>'Clients',            'value'=>$nb_users,                'color'=>'var(--muted)'],
    ];
    foreach ($kpis as $k): ?>
    <div class="card" style="border-top:3px solid <?= $k['color'] ?>;">
        <div style="font-family:'Clash Display',sans-serif; font-weight:700; font-size:1.8rem; color:<?= $k['color'] ?>;"><?= $k['value'] ?></div>
        <div style="color:var(--muted); font-size:0.78rem; margin-top:0.2rem;"><?= $k['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($stats['chiffre_affaires']): ?>
<div class="card" style="margin-bottom:2rem; border-left:4px solid var(--cta); background:linear-gradient(135deg, #fff 60%, #fef3e2);">
    <div style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); font-weight:600;">Chiffre d'affaires total (toutes réservations)</div>
    <div style="font-family:'Clash Display',sans-serif; font-size:2.5rem; font-weight:700; color:var(--cta); margin-top:0.25rem;"><?= number_format($stats['chiffre_affaires'], 2, ',', ' ') ?> €</div>
</div>
<?php endif; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:1rem;">
    <h2 style="font-family:'Clash Display',sans-serif; font-weight:700;">Dernières réservations</h2>
    <div style="display:flex; gap:0.75rem;">
        <a href="/rentit/admin/reservations.php" class="btn btn-secondary btn-sm">Toutes les réservations</a>
        <a href="/rentit/admin/produits.php" class="btn btn-dark btn-sm">Gérer les produits</a>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Produit</th>
                <th>Période</th>
                <th>Durée</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dernières as $r): ?>
            <tr>
                <td style="color:var(--muted); font-size:0.8rem;">#<?= $r['id'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($r['client']) ?></td>
                <td style="font-size:0.85rem; color:var(--muted);"><?= htmlspecialchars(substr($r['produit'], 0, 30)) ?>...</td>
                <td style="font-size:0.82rem;"><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                <td style="font-size:0.85rem;"><?= $r['nb_jours'] ?>j</td>
                <td style="font-weight:600; color:var(--cta);"><?= number_format($r['prix_total'], 2, ',', ' ') ?>€</td>
                <td><span class="badge badge-<?= $r['statut'] ?>"><?= str_replace('_', ' ', $r['statut']) ?></span></td>
                <td><a href="/rentit/admin/reservations.php?id=<?= $r['id'] ?>" class="btn btn-secondary btn-sm">Gérer</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

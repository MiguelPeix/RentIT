<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
requireAdmin();

// Changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statut'], $_POST['reservation_id'])) {
    $statuts_valides = ['en_attente', 'confirmée', 'en_cours', 'terminée', 'annulée'];
    $statut = $_POST['statut'];
    $rid    = (int)$_POST['reservation_id'];
    if (in_array($statut, $statuts_valides)) {
        $pdo->prepare('UPDATE reservations SET statut = ? WHERE id = ?')->execute([$statut, $rid]);
        setFlash('success', 'Statut mis à jour.');
    }
    header('Location: /rentit/admin/reservations.php');
    exit;
}

// Filtre par statut
$filtre = $_GET['statut'] ?? '';
$where  = [];
$params = [];
if ($filtre) { $where[] = 'r.statut = ?'; $params[] = $filtre; }
$sql = 'SELECT r.*, u.nom AS client, u.email, p.nom AS produit FROM reservations r JOIN users u ON r.user_id = u.id JOIN produits p ON r.produit_id = p.id' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$statuts = ['en_attente', 'confirmée', 'en_cours', 'terminée', 'annulée'];

$pageTitle = 'Réservations — RentIT Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="margin-bottom:0.5rem;"><a href="/rentit/admin/index.php" style="color:var(--muted); text-decoration:none; font-size:0.85rem;">← Tableau de bord</a></div>
        <h1 style="font-family:'Clash Display',sans-serif; font-size:2rem; font-weight:700;">Réservations</h1>
    </div>
</div>

<!-- Filtres statut -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <a href="/rentit/admin/reservations.php" class="btn btn-sm <?= !$filtre ? 'btn-dark' : 'btn-secondary' ?>">Toutes</a>
    <?php foreach ($statuts as $s): ?>
    <a href="/rentit/admin/reservations.php?statut=<?= urlencode($s) ?>" class="btn btn-sm <?= $filtre === $s ? 'btn-dark' : 'btn-secondary' ?>">
        <?= ucfirst(str_replace('_', ' ', $s)) ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <?php if (empty($reservations)): ?>
        <div style="text-align:center; padding:3rem; color:var(--muted);">Aucune réservation trouvée.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Produit</th>
                <th>Dates</th>
                <th>Durée</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Changer statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td style="color:var(--muted); font-size:0.8rem;">#<?= $r['id'] ?></td>
                <td>
                    <div style="font-weight:600; font-size:0.88rem;"><?= htmlspecialchars($r['client']) ?></div>
                    <div style="font-size:0.78rem; color:var(--muted);"><?= htmlspecialchars($r['email']) ?></div>
                </td>
                <td style="font-size:0.85rem; max-width:180px;"><?= htmlspecialchars($r['produit']) ?></td>
                <td style="font-size:0.82rem;">
                    <?= date('d/m/Y', strtotime($r['date_debut'])) ?><br>
                    <span style="color:var(--muted);">→ <?= date('d/m/Y', strtotime($r['date_fin'])) ?></span>
                </td>
                <td><?= $r['nb_jours'] ?> j</td>
                <td style="font-weight:700; color:var(--cta);"><?= number_format($r['prix_total'], 2, ',', ' ') ?>€</td>
                <td><span class="badge badge-<?= $r['statut'] ?>"><?= str_replace('_', ' ', $r['statut']) ?></span></td>
                <td>
                    <form method="POST" style="display:flex; gap:0.4rem; align-items:center;">
                        <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                        <select name="statut" style="font-size:0.8rem; padding:0.3rem 0.5rem; border-radius:5px; border:1px solid var(--border); background:var(--bg);">
                            <?php foreach ($statuts as $s): ?>
                            <option value="<?= $s ?>" <?= $r['statut'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-dark btn-sm">OK</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

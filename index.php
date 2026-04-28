<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

// Filtres
$categorie = $_GET['categorie'] ?? '';
$search    = trim($_GET['search'] ?? '');

$where  = ['p.disponible = 1'];
$params = [];

if ($categorie) {
    $where[]  = 'p.categorie = ?';
    $params[] = $categorie;
}
if ($search) {
    $where[]  = '(p.nom LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = 'SELECT p.*, 
    (SELECT COUNT(*) FROM reservations r 
     WHERE r.produit_id = p.id 
     AND r.statut IN ("confirmée","en_cours")
     AND r.date_fin >= CURDATE()) AS reservations_actives
    FROM produits p
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY p.categorie, p.nom';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

$categories = ['PC portable', 'Écran', 'Accessoire', 'Serveur', 'Autre'];

$pageTitle = 'Catalogue — RentIT';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<div style="background: var(--accent); margin: -2.5rem -1.5rem 2.5rem; padding: 3rem 2rem; border-radius: 0 0 16px 16px;">
    <div style="max-width: 600px;">
        <h1 style="font-family:'Clash Display',sans-serif; font-weight:700; font-size:2.2rem; color:#fff; letter-spacing:-0.5px; line-height:1.2;">
            Location de matériel<br><span style="color:#7ec8a4;">informatique pro</span>
        </h1>
        <p style="color:rgba(255,255,255,0.65); margin-top:0.75rem; font-size:0.95rem;">PC portables, écrans, accessoires — disponibles à la journée ou à la semaine.</p>

        <form method="GET" style="display:flex; gap:0.75rem; margin-top:1.5rem; flex-wrap:wrap;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher du matériel..." style="flex:1; min-width:220px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:6px; padding:0.7rem 1rem; font-family:'Bricolage Grotesque',sans-serif; font-size:0.9rem; outline:none;">
            <input type="hidden" name="categorie" value="<?= htmlspecialchars($categorie) ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
</div>

<div style="display:flex; gap:2rem; align-items:flex-start;">

    <!-- Sidebar filtres -->
    <div style="width:200px; flex-shrink:0;">
        <div class="card">
            <div style="font-family:'Clash Display',sans-serif; font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); margin-bottom:0.85rem;">Catégories</div>
            <div style="display:flex; flex-direction:column; gap:0.25rem;">
                <a href="/rentit/index.php<?= $search ? '?search='.urlencode($search) : '' ?>"
                   style="padding:0.45rem 0.75rem; border-radius:6px; text-decoration:none; font-size:0.88rem; font-weight:500; <?= !$categorie ? 'background:var(--accent); color:#fff;' : 'color:var(--text);' ?>">
                    Tous (<?= count($produits) ?>)
                </a>
                <?php foreach ($categories as $cat):
                    $countCat = count(array_filter($produits, fn($p) => $p['categorie'] === $cat));
                    if (!$countCat && $categorie !== $cat) continue;
                ?>
                <a href="/rentit/index.php?categorie=<?= urlencode($cat) ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                   style="padding:0.45rem 0.75rem; border-radius:6px; text-decoration:none; font-size:0.88rem; font-weight:500; <?= $categorie === $cat ? 'background:var(--accent); color:#fff;' : 'color:var(--text);' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Grille produits -->
    <div style="flex:1;">
        <?php if (empty($produits)): ?>
            <div class="card" style="text-align:center; padding:3rem; color:var(--muted);">
                <div style="font-size:3rem; margin-bottom:1rem;">📦</div>
                <p>Aucun produit trouvé pour votre recherche.</p>
                <a href="/rentit/index.php" class="btn btn-dark" style="margin-top:1rem;">Voir tout le catalogue</a>
            </div>
        <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:1.25rem;">
            <?php foreach ($produits as $p):
                $dispo = $p['stock'] - $p['reservations_actives'];
                $dispo = max(0, $dispo);
            ?>
            <div class="card" style="padding:0; overflow:hidden; transition:box-shadow 0.2s; cursor:pointer;" onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow=''">
                <!-- Image placeholder -->
                <div style="height:160px; background: linear-gradient(135deg, var(--surface) 0%, var(--border) 100%); display:flex; align-items:center; justify-content:center;">
                    <span style="font-size:3rem;">
                        <?php
                        $icons = ['PC portable' => '💻', 'Écran' => '🖥️', 'Accessoire' => '🖱️', 'Serveur' => '🗄️', 'Autre' => '📦'];
                        echo $icons[$p['categorie']] ?? '📦';
                        ?>
                    </span>
                </div>

                <div style="padding:1.1rem;">
                    <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); font-weight:600; margin-bottom:0.35rem;"><?= htmlspecialchars($p['categorie']) ?></div>
                    <h3 style="font-family:'Clash Display',sans-serif; font-weight:600; font-size:0.95rem; line-height:1.3; margin-bottom:0.75rem;"><?= htmlspecialchars($p['nom']) ?></h3>

                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem;">
                        <div>
                            <span style="font-family:'Clash Display',sans-serif; font-size:1.2rem; font-weight:700; color:var(--cta);"><?= number_format($p['prix_jour'], 2, ',', ' ') ?>€</span>
                            <span style="color:var(--muted); font-size:0.75rem;"> / jour</span>
                        </div>
                        <span style="font-size:0.75rem; padding:3px 8px; border-radius:20px; font-weight:600;
                            <?= $dispo > 0 ? 'background:#e8f5ee; color:#2d6a4f;' : 'background:#fdf0ee; color:#c0392b;' ?>">
                            <?= $dispo > 0 ? $dispo . ' dispo.' : 'Indisponible' ?>
                        </span>
                    </div>

                    <a href="/rentit/produit.php?id=<?= $p['id'] ?>" class="btn <?= $dispo > 0 ? 'btn-dark' : 'btn-secondary' ?>" style="width:100%; justify-content:center;">
                        <?= $dispo > 0 ? 'Réserver' : 'Voir le détail' ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

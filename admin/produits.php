<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
requireAdmin();

$action = $_GET['action'] ?? 'list';
$errors = [];

// Suppression / désactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $pid = (int)$_POST['toggle_id'];
    $stmt = $pdo->prepare('UPDATE produits SET disponible = 1 - disponible WHERE id = ?');
    $stmt->execute([$pid]);
    setFlash('success', 'Disponibilité mise à jour.');
    header('Location: /rentit/admin/produits.php');
    exit;
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $pid         = (int)($_POST['id'] ?? 0);
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorie   = $_POST['categorie'] ?? 'Autre';
    $prix_jour   = floatval($_POST['prix_jour'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 1);
    $disponible  = isset($_POST['disponible']) ? 1 : 0;

    $cats = ['PC portable', 'Écran', 'Accessoire', 'Serveur', 'Autre'];

    if (empty($nom)) $errors[] = 'Le nom est obligatoire.';
    if (empty($description)) $errors[] = 'La description est obligatoire.';
    if ($prix_jour <= 0) $errors[] = 'Le prix doit être supérieur à 0.';
    if (!in_array($categorie, $cats)) $errors[] = 'Catégorie invalide.';

    if (empty($errors)) {
        if ($pid) {
            $pdo->prepare('UPDATE produits SET nom=?, description=?, categorie=?, prix_jour=?, stock=?, disponible=? WHERE id=?')
                ->execute([$nom, $description, $categorie, $prix_jour, $stock, $disponible, $pid]);
            setFlash('success', 'Produit mis à jour.');
        } else {
            $pdo->prepare('INSERT INTO produits (nom, description, categorie, prix_jour, stock, disponible) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$nom, $description, $categorie, $prix_jour, $stock, $disponible]);
            setFlash('success', 'Produit ajouté.');
        }
        header('Location: /rentit/admin/produits.php');
        exit;
    }
    $action = $pid ? 'edit' : 'add';
}

// Chargement d'un produit pour édition
$produit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $produit = $stmt->fetch();
    if (!$produit) { header('Location: /rentit/admin/produits.php'); exit; }
}

// Liste des produits
$produits = $pdo->query('SELECT p.*, (SELECT COUNT(*) FROM reservations r WHERE r.produit_id = p.id AND r.statut IN ("confirmée","en_cours")) AS reservations_actives FROM produits p ORDER BY p.categorie, p.nom')->fetchAll();

$cats = ['PC portable', 'Écran', 'Accessoire', 'Serveur', 'Autre'];
$icons = ['PC portable' => '💻', 'Écran' => '🖥️', 'Accessoire' => '🖱️', 'Serveur' => '🗄️', 'Autre' => '📦'];

$pageTitle = 'Produits — RentIT Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="margin-bottom:0.5rem;"><a href="/rentit/admin/index.php" style="color:var(--muted); text-decoration:none; font-size:0.85rem;">← Tableau de bord</a></div>
        <h1 style="font-family:'Clash Display',sans-serif; font-size:2rem; font-weight:700;">Produits</h1>
    </div>
    <a href="/rentit/admin/produits.php?action=add" class="btn btn-primary">+ Ajouter un produit</a>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- FORMULAIRE -->
<div class="card" style="max-width:680px; margin-bottom:2rem;">
    <div class="card-title"><?= $action === 'add' ? 'Nouveau produit' : 'Modifier le produit' ?></div>

    <?php if (!empty($errors)): ?>
        <div style="background:#fdf0ee; border-left:3px solid var(--danger); color:var(--danger); padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.88rem;"><?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="save" value="1">
        <input type="hidden" name="id" value="<?= $produit['id'] ?? 0 ?>">

        <div class="form-group">
            <label>Nom du produit *</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom'] ?? $_POST['nom'] ?? '') ?>" placeholder="Ex : Dell XPS 15 — i7 / 32 Go" required>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label>Catégorie</label>
                <select name="categorie">
                    <?php foreach ($cats as $c): ?>
                    <option value="<?= $c ?>" <?= ($produit['categorie'] ?? $_POST['categorie'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Prix / jour (€) *</label>
                <input type="number" name="prix_jour" step="0.50" min="0.50" value="<?= $produit['prix_jour'] ?? $_POST['prix_jour'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Stock (unités)</label>
                <input type="number" name="stock" min="1" value="<?= $produit['stock'] ?? $_POST['stock'] ?? 1 ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($produit['description'] ?? $_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="disponible" id="disponible" value="1" <?= ($produit['disponible'] ?? 1) ? 'checked' : '' ?> style="width:auto;">
            <label for="disponible" style="margin:0; color:var(--text);">Produit disponible à la location</label>
        </div>

        <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
            <a href="/rentit/admin/produits.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- LISTE DES PRODUITS -->
<div class="card" style="padding:0; overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Prix / jour</th>
                <th>Stock</th>
                <th>Réservations actives</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
            <tr>
                <td>
                    <span style="margin-right:0.5rem;"><?= $icons[$p['categorie']] ?? '📦' ?></span>
                    <span style="font-weight:500; font-size:0.88rem;"><?= htmlspecialchars($p['nom']) ?></span>
                </td>
                <td style="color:var(--muted); font-size:0.85rem;"><?= htmlspecialchars($p['categorie']) ?></td>
                <td style="font-weight:600; color:var(--cta);"><?= number_format($p['prix_jour'], 2, ',', ' ') ?>€</td>
                <td><?= $p['stock'] ?></td>
                <td><?= $p['reservations_actives'] ?></td>
                <td>
                    <span style="font-size:0.8rem; padding:3px 10px; border-radius:20px; font-weight:600;
                        <?= $p['disponible'] ? 'background:#e8f5ee; color:#2d6a4f;' : 'background:#fdf0ee; color:var(--danger);' ?>">
                        <?= $p['disponible'] ? 'Actif' : 'Masqué' ?>
                    </span>
                </td>
                <td style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                    <a href="/rentit/admin/produits.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:var(--surface); border:1px solid var(--border);">
                            <?= $p['disponible'] ? 'Masquer' : 'Activer' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ? AND disponible = 1');
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    setFlash('error', 'Produit introuvable.');
    header('Location: /rentit/index.php');
    exit;
}

// Récupère les dates déjà réservées pour ce produit
$stmt = $pdo->prepare("
    SELECT date_debut, date_fin FROM reservations
    WHERE produit_id = ? AND statut IN ('confirmée', 'en_cours')
    AND date_fin >= CURDATE()
");
$stmt->execute([$id]);
$reservations_existantes = $stmt->fetchAll();

// Construire la liste des dates bloquées pour JS
$dates_bloquees = [];
foreach ($reservations_existantes as $r) {
    $debut = new DateTime($r['date_debut']);
    $fin   = new DateTime($r['date_fin']);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($debut, $interval, (clone $fin)->modify('+1 day'));
    foreach ($period as $date) {
        $dates_bloquees[] = $date->format('Y-m-d');
    }
}
$dates_bloquees_json = json_encode(array_unique($dates_bloquees));

// Traitement de la réservation
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        setFlash('info', 'Connectez-vous pour effectuer une réservation.');
        header('Location: /rentit/login.php');
        exit;
    }

    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin   = $_POST['date_fin']   ?? '';
    $message    = trim($_POST['message'] ?? '');

    if (empty($date_debut) || empty($date_fin)) {
        $errors[] = 'Veuillez sélectionner une période de location.';
    } else {
        $d1 = new DateTime($date_debut);
        $d2 = new DateTime($date_fin);

        if ($d1 >= $d2) {
            $errors[] = 'La date de fin doit être après la date de début.';
        } elseif ($d1 < new DateTime('today')) {
            $errors[] = 'La date de début ne peut pas être dans le passé.';
        } else {
            $nb_jours   = $d1->diff($d2)->days;
            $prix_total = $nb_jours * $produit['prix_jour'];
            $user       = getCurrentUser();

            $stmt = $pdo->prepare('INSERT INTO reservations (user_id, produit_id, date_debut, date_fin, nb_jours, prix_total, message) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $id, $date_debut, $date_fin, $nb_jours, $prix_total, $message]);

            setFlash('success', 'Réservation effectuée ! Nous vous confirmons votre demande sous 24h.');
            header('Location: /rentit/compte.php');
            exit;
        }
    }
}

$icons = ['PC portable' => '💻', 'Écran' => '🖥️', 'Accessoire' => '🖱️', 'Serveur' => '🗄️', 'Autre' => '📦'];
$icon  = $icons[$produit['categorie']] ?? '📦';

$pageTitle = htmlspecialchars($produit['nom']) . ' — RentIT';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom:1rem;">
    <a href="/rentit/index.php" style="color:var(--muted); text-decoration:none; font-size:0.85rem; font-weight:500;">← Retour au catalogue</a>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:2rem; align-items:start;">

    <!-- Infos produit -->
    <div>
        <div class="card" style="padding:0; overflow:hidden; margin-bottom:1.5rem;">
            <div style="height:280px; background:linear-gradient(135deg, var(--surface) 0%, var(--border) 100%); display:flex; align-items:center; justify-content:center;">
                <span style="font-size:6rem;"><?= $icon ?></span>
            </div>
            <div style="padding:2rem;">
                <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.6px; color:var(--muted); font-weight:600; margin-bottom:0.5rem;"><?= htmlspecialchars($produit['categorie']) ?></div>
                <h1 style="font-family:'Clash Display',sans-serif; font-weight:700; font-size:1.6rem; line-height:1.2; margin-bottom:1.25rem;"><?= htmlspecialchars($produit['nom']) ?></h1>
                <p style="color:var(--muted); line-height:1.75;"><?= htmlspecialchars($produit['description']) ?></p>

                <div style="display:flex; gap:2rem; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--border);">
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--muted); font-weight:600; margin-bottom:0.25rem;">Prix / jour</div>
                        <div style="font-family:'Clash Display',sans-serif; font-size:1.8rem; font-weight:700; color:var(--cta);"><?= number_format($produit['prix_jour'], 2, ',', ' ') ?>€</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--muted); font-weight:600; margin-bottom:0.25rem;">Stock total</div>
                        <div style="font-family:'Clash Display',sans-serif; font-size:1.8rem; font-weight:700;"><?= $produit['stock'] ?> unité<?= $produit['stock'] > 1 ? 's' : '' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de réservation -->
    <div>
        <div class="card" style="position:sticky; top:80px;">
            <div class="card-title">Réserver ce matériel</div>

            <?php if (!empty($errors)): ?>
                <div style="background:#fdf0ee; border-left:3px solid var(--danger); color:var(--danger); padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.88rem;">
                    <?= htmlspecialchars($errors[0]) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="formReservation">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" id="date_fin" min="<?= date('Y-m-d', strtotime('+2 days')) ?>" value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>" required>
                </div>

                <!-- Calcul du prix en temps réel -->
                <div id="prix-recap" style="display:none; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:1rem; margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:0.5rem;">
                        <span style="color:var(--muted);">Durée</span>
                        <span id="recap-jours" style="font-weight:600;"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:0.5rem;">
                        <span style="color:var(--muted);">Prix unitaire</span>
                        <span><?= number_format($produit['prix_jour'], 2, ',', ' ') ?>€ / jour</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:1rem; font-weight:700; padding-top:0.5rem; border-top:1px solid var(--border); margin-top:0.5rem;">
                        <span>Total estimé</span>
                        <span id="recap-total" style="color:var(--cta); font-family:'Clash Display',sans-serif; font-size:1.2rem;"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Message / précisions (optionnel)</label>
                    <textarea name="message" placeholder="Indiquez vos besoins spécifiques..." rows="3"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <?php if (isLoggedIn()): ?>
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:0.85rem;">Confirmer la réservation</button>
                <?php else: ?>
                    <a href="/rentit/login.php" class="btn btn-dark" style="width:100%; justify-content:center; padding:0.85rem;">Se connecter pour réserver</a>
                <?php endif; ?>
            </form>

            <p style="font-size:0.78rem; color:var(--muted); text-align:center; margin-top:1rem;">
                Confirmation sous 24h — Paiement à la livraison
            </p>
        </div>
    </div>
</div>

<script>
const prixJour = <?= $produit['prix_jour'] ?>;
const dateDebut = document.getElementById('date_debut');
const dateFin   = document.getElementById('date_fin');
const recap     = document.getElementById('prix-recap');
const recapJours  = document.getElementById('recap-jours');
const recapTotal  = document.getElementById('recap-total');

function calculerPrix() {
    const d1 = new Date(dateDebut.value);
    const d2 = new Date(dateFin.value);

    if (dateDebut.value && dateFin.value && d2 > d1) {
        const diffMs   = d2 - d1;
        const diffJours = Math.round(diffMs / (1000 * 60 * 60 * 24));
        const total    = diffJours * prixJour;

        recapJours.textContent = diffJours + ' jour' + (diffJours > 1 ? 's' : '');
        recapTotal.textContent = total.toFixed(2).replace('.', ',') + ' €';
        recap.style.display = 'block';
    } else {
        recap.style.display = 'none';
    }

    // Mettre à jour le min de date_fin
    if (dateDebut.value) {
        const minFin = new Date(dateDebut.value);
        minFin.setDate(minFin.getDate() + 1);
        dateFin.min = minFin.toISOString().split('T')[0];
    }
}

dateDebut.addEventListener('change', calculerPrix);
dateFin.addEventListener('change', calculerPrix);
calculerPrix(); // Au chargement si valeurs pré-remplies
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

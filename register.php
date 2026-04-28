<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) { header('Location: /rentit/index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel   = trim($_POST['telephone'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';
    $mdp2  = $_POST['mot_de_passe2'] ?? '';

    if (empty($nom) || empty($email) || empty($mdp) || empty($mdp2))
        $errors[] = 'Tous les champs obligatoires doivent être remplis.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Adresse email invalide.';
    elseif (strlen($mdp) < 6)
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    elseif ($mdp !== $mdp2)
        $errors[] = 'Les mots de passe ne correspondent pas.';
    else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (nom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nom, $email, $hash, $tel]);
            setFlash('success', 'Compte créé avec succès ! Connectez-vous.');
            header('Location: /rentit/login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — RentIT</title>
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;600;700&family=Bricolage+Grotesque:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg:#f5f3ee; --white:#fff; --border:#d8d4ca; --accent:#1a3a2a; --accent2:#2d6a4f; --text:#1a1a18; --muted:#7a7870; --cta:#e8521a; --danger:#c0392b; }
        body { font-family:'Bricolage Grotesque',sans-serif; background:var(--accent); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
        .auth-box { background:var(--white); border-radius:14px; padding:2.5rem; width:100%; max-width:460px; box-shadow:0 40px 80px rgba(0,0,0,0.3); }
        .auth-logo { font-family:'Clash Display',sans-serif; font-weight:700; font-size:1.6rem; color:var(--accent); text-align:center; margin-bottom:0.3rem; }
        .auth-logo span { color:#2d6a4f; }
        .auth-sub { text-align:center; color:var(--muted); font-size:0.85rem; margin-bottom:2rem; }
        h2 { font-family:'Clash Display',sans-serif; font-weight:700; font-size:1.3rem; margin-bottom:1.5rem; }
        .form-group { margin-bottom:1rem; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        label { display:block; margin-bottom:0.4rem; font-size:0.82rem; color:var(--muted); font-weight:500; }
        input { width:100%; background:var(--bg); border:1px solid var(--border); border-radius:6px; color:var(--text); padding:0.7rem 0.9rem; font-family:'Bricolage Grotesque',sans-serif; font-size:0.92rem; outline:none; transition:border-color 0.15s; }
        input:focus { border-color:var(--accent2); }
        .hint { font-size:0.75rem; color:var(--muted); margin-top:0.3rem; }
        .btn { width:100%; background:var(--cta); color:#fff; border:none; border-radius:6px; padding:0.8rem; font-family:'Bricolage Grotesque',sans-serif; font-weight:600; font-size:0.95rem; cursor:pointer; transition:background 0.15s; margin-top:0.5rem; }
        .btn:hover { background:#cc4515; }
        .error-box { background:#fdf0ee; border-left:3px solid var(--danger); color:var(--danger); padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.88rem; }
        .auth-footer { text-align:center; margin-top:1.5rem; font-size:0.85rem; color:var(--muted); }
        .auth-footer a { color:var(--accent2); text-decoration:none; font-weight:500; }
    </style>
</head>
<body>
<div class="auth-box">
    <div class="auth-logo">Rent<span>IT</span></div>
    <p class="auth-sub">Créez votre espace client gratuit</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box"><?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Jean Dupont" required>
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" placeholder="06 00 00 00 00">
            </div>
        </div>
        <div class="form-group">
            <label>Adresse email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="vous@exemple.fr" required>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="mot_de_passe" placeholder="Min. 6 caractères" required>
            </div>
            <div class="form-group">
                <label>Confirmer *</label>
                <input type="password" name="mot_de_passe2" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn">Créer mon compte</button>
    </form>

    <div class="auth-footer">
        Déjà client ? <a href="/rentit/login.php">Se connecter</a>
    </div>
</div>
</body>
</html>

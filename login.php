<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) { header('Location: /rentit/index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($mdp)) {
        $errors[] = 'Tous les champs sont obligatoires.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['role']    = $user['role'];
            setFlash('success', 'Bienvenue, ' . $user['nom'] . ' !');
            header('Location: /rentit/index.php');
            exit;
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — RentIT</title>
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;600;700&family=Bricolage+Grotesque:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg:#f5f3ee; --white:#fff; --border:#d8d4ca; --accent:#1a3a2a; --accent2:#2d6a4f; --text:#1a1a18; --muted:#7a7870; --cta:#e8521a; --danger:#c0392b; }
        body { font-family:'Bricolage Grotesque',sans-serif; background:var(--accent); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(ellipse at 70% 30%, rgba(126,200,164,0.15) 0%, transparent 60%); pointer-events:none; }
        .auth-wrap { display:grid; grid-template-columns:1fr 400px; max-width:820px; width:100%; border-radius:16px; overflow:hidden; box-shadow: 0 40px 80px rgba(0,0,0,0.4); }
        .auth-left { background: linear-gradient(135deg, #2d6a4f 0%, #1a3a2a 100%); padding:3rem; display:flex; flex-direction:column; justify-content:space-between; }
        .auth-logo { font-family:'Clash Display',sans-serif; font-weight:700; font-size:2rem; color:#fff; letter-spacing:-1px; }
        .auth-logo span { color:#7ec8a4; }
        .auth-tagline { color:rgba(255,255,255,0.6); font-size:0.9rem; margin-top:0.5rem; }
        .auth-features { display:flex; flex-direction:column; gap:0.75rem; }
        .auth-feature { display:flex; align-items:center; gap:0.75rem; color:rgba(255,255,255,0.75); font-size:0.88rem; }
        .auth-feature::before { content:'✓'; background:rgba(126,200,164,0.2); color:#7ec8a4; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0; }
        .auth-right { background:var(--white); padding:2.5rem; }
        h2 { font-family:'Clash Display',sans-serif; font-weight:700; font-size:1.5rem; margin-bottom:0.4rem; }
        .auth-sub { color:var(--muted); font-size:0.85rem; margin-bottom:2rem; }
        .form-group { margin-bottom:1rem; }
        label { display:block; margin-bottom:0.4rem; font-size:0.82rem; color:var(--muted); font-weight:500; }
        input { width:100%; background:var(--bg); border:1px solid var(--border); border-radius:6px; color:var(--text); padding:0.7rem 0.9rem; font-family:'Bricolage Grotesque',sans-serif; font-size:0.92rem; outline:none; transition:border-color 0.15s; }
        input:focus { border-color:var(--accent2); }
        .btn { width:100%; background:var(--cta); color:#fff; border:none; border-radius:6px; padding:0.8rem; font-family:'Bricolage Grotesque',sans-serif; font-weight:600; font-size:0.95rem; cursor:pointer; transition:background 0.15s; margin-top:0.5rem; }
        .btn:hover { background:#cc4515; }
        .error-box { background:#fdf0ee; border-left:3px solid var(--danger); color:var(--danger); padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.88rem; }
        .auth-footer { text-align:center; margin-top:1.5rem; font-size:0.85rem; color:var(--muted); }
        .auth-footer a { color:var(--accent2); text-decoration:none; font-weight:500; }
        @media(max-width:600px) { .auth-wrap { grid-template-columns:1fr; } .auth-left { display:none; } }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-left">
        <div>
            <div class="auth-logo">Rent<span>IT</span></div>
            <p class="auth-tagline">Location de matériel informatique</p>
        </div>
        <div class="auth-features">
            <div class="auth-feature">Catalogue de matériel professionnel</div>
            <div class="auth-feature">Réservation en ligne en quelques clics</div>
            <div class="auth-feature">Suivi de vos locations en temps réel</div>
            <div class="auth-feature">Livraison et récupération sur site</div>
        </div>
    </div>
    <div class="auth-right">
        <h2>Connexion</h2>
        <p class="auth-sub">Accédez à votre espace client</p>

        <?php if (!empty($errors)): ?>
            <div class="error-box"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Adresse email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="vous@exemple.fr" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="auth-footer">
            Pas encore de compte ? <a href="/rentit/register.php">Créer un compte</a>
        </div>
    </div>
</div>
</body>
</html>

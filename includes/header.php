<?php
require_once __DIR__ . '/../includes/auth.php';
$user  = getCurrentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'RentIT' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Bricolage+Grotesque:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #f5f3ee;
            --white:    #ffffff;
            --surface:  #edeae3;
            --border:   #d8d4ca;
            --text:     #1a1a18;
            --muted:    #7a7870;
            --accent:   #1a3a2a;
            --accent2:  #2d6a4f;
            --cta:      #e8521a;
            --cta-h:    #cc4515;
            --success:  #2d6a4f;
            --warning:  #c68b2a;
            --danger:   #c0392b;
            --info:     #1a4a6a;
            --radius:   8px;
        }

        body {
            font-family: 'Bricolage Grotesque', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 15px;
            line-height: 1.6;
        }

        /* NAV */
        nav {
            background: var(--accent);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-family: 'Clash Display', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-brand span { color: #7ec8a4; }

        .nav-links { display: flex; align-items: center; gap: 0.2rem; }

        .nav-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 0.45rem 1rem;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.15s;
        }

        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.12); color: #fff; }

        .nav-right { display: flex; align-items: center; gap: 0.75rem; }

        .nav-user-name { color: rgba(255,255,255,0.8); font-size: 0.85rem; }

        .badge-admin {
            background: #7ec8a4;
            color: var(--accent);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* MAIN */
        main { max-width: 1160px; margin: 0 auto; padding: 2.5rem 1.5rem; }

        /* FLASH */
        .flash {
            padding: 0.85rem 1.2rem;
            border-radius: var(--radius);
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-left: 4px solid;
        }
        .flash.success { background: #e8f5ee; border-color: var(--success); color: var(--success); }
        .flash.error   { background: #fdf0ee; border-color: var(--danger);  color: var(--danger); }
        .flash.info    { background: #e8f0f8; border-color: var(--info);    color: var(--info); }

        /* CARDS */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
        }

        .card-title {
            font-family: 'Clash Display', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid var(--border);
        }

        /* FORMS */
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.83rem; color: var(--muted); font-weight: 500; }

        input[type="text"], input[type="email"], input[type="password"],
        input[type="date"], input[type="tel"], input[type="number"],
        textarea, select {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            padding: 0.65rem 0.9rem;
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 0.92rem;
            transition: border-color 0.15s;
            outline: none;
        }
        input:focus, textarea:focus, select:focus { border-color: var(--accent2); }
        textarea { resize: vertical; min-height: 100px; }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.3rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-primary  { background: var(--cta); color: #fff; }
        .btn-primary:hover  { background: var(--cta-h); }
        .btn-dark     { background: var(--accent); color: #fff; }
        .btn-dark:hover { background: #0f2a1a; }
        .btn-secondary { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }
        .btn-danger   { background: var(--danger); color: #fff; }
        .btn-sm { padding: 0.35rem 0.85rem; font-size: 0.8rem; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-en_attente { background: #fef3e2; color: #c68b2a; }
        .badge-confirmée  { background: #e8f5ee; color: #2d6a4f; }
        .badge-en_cours   { background: #e8f0f8; color: #1a4a6a; }
        .badge-terminée   { background: var(--surface); color: var(--muted); }
        .badge-annulée    { background: #fdf0ee; color: var(--danger); }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.6rem 1rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.6px; color: var(--muted); border-bottom: 2px solid var(--border); font-weight: 600; }
        td { padding: 0.9rem 1rem; border-bottom: 1px solid var(--surface); font-size: 0.9rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg); }

        /* PAGE HEADER */
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Clash Display', sans-serif; font-weight: 700; font-size: 2rem; letter-spacing: -0.5px; }
        .page-header p  { color: var(--muted); margin-top: 0.3rem; font-size: 0.92rem; }

        /* GRID */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }

        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr 1fr; }
            nav { padding: 0 1rem; }
            main { padding: 1.5rem 1rem; }
        }
        @media (max-width: 480px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav>
    <a href="/rentit/index.php" class="nav-brand">Rent<span>IT</span></a>

    <?php if (isLoggedIn()): ?>
    <div class="nav-links">
        <a href="/rentit/index.php">Catalogue</a>
        <a href="/rentit/compte.php">Mes réservations</a>
        <?php if (isAdmin()): ?>
        <a href="/rentit/admin/index.php">Administration</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <?php if (isAdmin()): ?><span class="badge-admin">Admin</span><?php endif; ?>
        <span class="nav-user-name"><?= htmlspecialchars($user['nom']) ?></span>
        <a href="/rentit/logout.php" class="btn btn-secondary btn-sm">Déconnexion</a>
    </div>
    <?php else: ?>
    <div class="nav-links">
        <a href="/rentit/index.php">Catalogue</a>
    </div>
    <div class="nav-right">
        <a href="/rentit/login.php" class="btn btn-secondary btn-sm">Connexion</a>
        <a href="/rentit/register.php" class="btn btn-primary btn-sm">Créer un compte</a>
    </div>
    <?php endif; ?>
</nav>

<main>
<?php if ($flash): ?>
    <div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

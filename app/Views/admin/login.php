<?php
/**
 * Login del Panel de Administración
 * Her Beauty Center
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acceso Admin | Her Beauty Center</title>
    <meta name="robots" content="noindex, nofollow" />

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/css/styles.css" />
    <link rel="stylesheet" href="/public/assets/css/admin.css" />
</head>
<body>

<div class="admin-login-page">
    <div class="admin-login-card">
        <img src="/public/assets/media/logoText.jpeg" alt="Her Beauty Center" class="admin-login-card__logo" />
        <h1 class="admin-login-card__title">Panel Administrativo</h1>
        <p class="admin-login-card__subtitle">Ingresá con tus credenciales para continuar</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="admin-alert admin-alert--error">
                <span class="material-symbols-outlined" style="font-size:1.1rem; flex-shrink:0;">error</span>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input class="form-input" type="email" id="correo" name="correo"
                       placeholder="tu@email.com" required autocomplete="email" />
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password" />
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%; margin-top: 0.5rem;">
                Ingresar al panel
            </button>
        </form>

        <p style="margin-top: 1.5rem; font-size: 0.78rem; color: var(--color-on-surface-variant);">
            <a href="/" style="color: var(--color-primary);">← Volver al sitio web</a>
        </p>
    </div>
</div>

</body>
</html>

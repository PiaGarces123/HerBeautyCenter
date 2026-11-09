<?php
/**
 * Partial compartido: <head> de las vistas del cliente.
 * Variables: $pageTitle (string), $extraCss (array opcional de rutas en public/assets/css)
 */
$extraCss = $extraCss ?? [];
$v = '3.2.' . time();
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="description" content="Her Beauty Center — Centro de estética y bienestar en San Luis." />
    <title><?= esc($pageTitle ?? 'Her Beauty Center') ?> | Her Beauty Center</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('public/assets/media/logoText.ico?v=' . time()) ?>" />
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('public/assets/media/logoText.ico?v=' . time()) ?>" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('public/assets/media/favicon-32x32.png?v=' . time()) ?>" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('public/assets/media/favicon-16x16.png?v=' . time()) ?>" />
    <link rel="apple-touch-icon" href="<?= base_url('public/assets/media/apple-touch-icon.png?v=' . time()) ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . $v) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . $v) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/cliente.css?v=' . $v) ?>" />
    <?php foreach ($extraCss as $href): ?>
        <link rel="stylesheet" href="<?= esc($href) ?>" />
    <?php endforeach; ?>
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>


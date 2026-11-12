<?php
/**
 * En-tête HTML public. Variables attendues (optionnelles) :
 * $pageTitle, $pageDescription, $bodyClass, $activeNav, $extraCss (array), $noVeil (bool)
 */
declare(strict_types=1);
require_once APP_ROOT . '/includes/logo.php';
$pageTitle       = $pageTitle ?? '';
$pageDescription = $pageDescription ?? "AFRICA'SPY — académie africaine de formation au renseignement, au contre-espionnage et à l'intelligence stratégique.";
$fullTitle       = ($pageTitle !== '' ? $pageTitle . ' · ' : '') . "AFRICA'SPY Intelligence Academy";
$bodyClass       = $bodyClass ?? '';
$extraCss        = $extraCss ?? [];
$csrfMeta        = session_status() === PHP_SESSION_ACTIVE ? csrf_token() : '';
?><!doctype html>
<html lang="fr" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#05070B">
<meta name="csrf-token" content="<?= e($csrfMeta) ?>">
<meta name="base-path" content="<?= e(base_path()) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:image" content="<?= e(url('assets/images/hero-command-center.webp')) ?>">
<link rel="icon" href="<?= e(url('assets/images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(url('assets/images/apple-touch-icon.png')) ?>">
<link rel="preload" href="<?= e(url('assets/fonts/orbitron-500.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('assets/fonts/inter-400.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/animations.css')) ?>">
<?php foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= e(asset($css)) ?>">
<?php endforeach; ?>
<script src="<?= e(asset('js/boot.js')) ?>"></script>
<script src="<?= e(asset('js/main.js')) ?>" defer></script>
<script src="<?= e(asset('js/interactions.js')) ?>" defer></script>
<?php foreach (($extraJs ?? []) as $js): ?>
<script src="<?= e(asset($js)) ?>" defer></script>
<?php endforeach; ?>
</head>
<body class="<?= e($bodyClass) ?><?= !empty($noVeil) ? ' no-veil' : '' ?>">
<a class="skip-link" href="#main">Aller au contenu</a>
<div class="page-veil" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
<?php if (empty($hideChrome)): ?>
<header class="site-header" id="top">
  <div class="container site-header__inner">
    <a href="<?= e(url('index.php')) ?>" aria-label="AFRICA'SPY — accueil"><?= logo_lockup('holo') ?></a>
    <?php require APP_ROOT . '/includes/navigation.php'; ?>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
  </div>
</header>
<?php endif; ?>
<div class="toasts" role="status" aria-live="polite">
<?php foreach (flashes() as $f): $t = in_array($f['type'], ['success', 'error', 'warning', 'info'], true) ? $f['type'] : 'info'; ?>
  <div class="toast toast--<?= e($t) ?>"><span class="toast__tag"><?= e(['success' => 'OK', 'error' => 'ERR', 'warning' => 'ALERTE', 'info' => 'INFO'][$t]) ?></span><span><?= e($f['msg']) ?></span><button class="toast__close" type="button" aria-label="Fermer">×</button></div>
<?php endforeach; ?>
</div>

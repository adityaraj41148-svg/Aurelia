<?php
// AURELIA — WEAR THE MOMENT.
// Modular Header Include
if (file_exists(__DIR__ . '/config.php')) require_once __DIR__ . '/config.php';
if (file_exists(__DIR__ . '/functions.php')) require_once __DIR__ . '/functions.php';
if (file_exists(__DIR__ . '/auth.php')) require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'AURELIA — WEAR THE MOMENT. | Luxury Fashion Editorial';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($sanitize) ? sanitize($page_title) : htmlspecialchars($page_title) ?></title>
    <meta name="description" content="AURELIA — Wear The Moment. Curated luxury fashion, architectural silhouettes, French linen shirts, silk slip gowns, and Italian tailoring.">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- External Custom Styles -->
    <link rel="stylesheet" href="<?= $baseUrl ?>style.css">
</head>
<body class="bg-[#faf8f5] text-[#0f0e0d] font-sans-body min-h-screen flex flex-col antialiased selection:bg-[#c5a059] selection:text-white overflow-x-hidden">

    <!-- Desktop Custom Cursor -->
    <div id="custom-cursor" class="hidden md:block"></div>
    <div id="cursor-follower" class="hidden md:block"></div>

    <!-- Top Scroll Progress Bar -->
    <div id="scroll-progress"></div>

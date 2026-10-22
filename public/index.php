<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FoodEase</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { DEFAULT: '#b45309', dark: '#92400e', light: '#fef3c7' } } } }
    };
  </script>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="bg-stone-50 text-stone-900">
  <main class="mx-auto max-w-5xl p-6">
    <h1 class="text-2xl font-semibold">FoodEase</h1>
    <p class="mt-2 text-stone-500">Setup berhasil. Base URL: <?= e(url()) ?></p>
    <button id="btn-test" class="mt-4 rounded-md bg-brand px-4 py-2 text-white hover:bg-brand-dark">Tes jQuery</button>
    <p id="out" class="mt-3 text-sm text-stone-600"></p>
  </main>
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script>$('#btn-test').on('click', function () { $('#out').text('jQuery bekerja'); });</script>
</body>
</html>
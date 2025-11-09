<?php

declare(strict_types=1);
session_start();

/**
 * Si usas MVC con vistas en /vista/login/login.php, este index redirige allí.
 * Ajusta $loginPath si tu ruta es distinta.
 */
$loginPath = '/vista/login/login.php';  // <-- cambia si tu login está en otro lugar

// (Opcional) Si ya hay sesión iniciada, envía a tu dashboard.
// if (!empty($_SESSION['id'])) {
//   header('Location: /vista/dashboard.php');
//   exit;
// }

// Construye URL absoluta respetando subcarpetas del proyecto
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$target = $base . $loginPath;

// Evita caché y redirige
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . $target);
    exit;
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>">
    <title>Redirigiendo…</title>
</head>

<body>
    Si no eres redirigido automáticamente, haz clic aquí:
    <a href="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>">Ir al login</a>
    <script>
        location.replace("<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>");
    </script>
</body>

</html>
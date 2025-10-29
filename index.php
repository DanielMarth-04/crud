<?php
require_once "config/app.php";
require_once "controllers/usuarioController.php";

session_start();
// 🔹 Acción de logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller = new UsuarioController();
    $controller->logout();
    exit();
}
// Si el usuario envía el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    $controller = new usuarioController();
    $controller->login($usuario, $password);
    exit();
}

// Si el usuario NO ha iniciado sesión, mostrar login
if (!isset($_SESSION[APP_SESSION_NAME])) {
    include "views/usuarios/index.php";
    exit();
}

// --- Si ya está logueado, cargar el dashboard o vista correspondiente ---
if (isset($_GET['views'])) {
    $view = $_GET['views'];
    $ruta = "views/" . $view . ".php";

    if (file_exists($ruta)) {
        include __DIR__ . "/views/inc/header.php";
        include __DIR__ . "/views/inc/sidebar.php";
        include $ruta;
        include __DIR__ . "/views/inc/footer.php";
    } else {
        include __DIR__ . "/views/404.php";
    }
} else {
    include __DIR__ . "/views/inc/header.php";
    include __DIR__ . "/views/inc/sidebar.php";
    include __DIR__ . "/views/dashboard/index.php";
    include __DIR__ . "/views/inc/footer.php";
}

<?php
require_once "models/usuario.php";

class usuarioController {

    public function login($usuario, $password) {
        $model = new usuarioModel();
        $user = $model->login($usuario, $password);

        if ($user) {
            session_start();
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['roles'];
            $_SESSION[APP_SESSION_NAME] = true; // bandera de sesión activa

            header("Location: " . APP_URL);
            exit();
        } else {
            echo "❌ Usuario o contraseña incorrectos";
        }
    }

    // 🚪 MÉTODO LOGOUT
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . APP_URL);
        exit();
    }
}

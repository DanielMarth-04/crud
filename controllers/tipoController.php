<?php
require_once __DIR__ . '/../models/tipo.php';

class tipoController {
    public function listar() {
        $model = new Tipo();
        return $model->obtenerTipos();
    }
}
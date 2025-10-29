<?php
require_once __DIR__ . '/../models/personal.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';
class personalController
{


    public function obtenerPersonalId($id)
    {
        $model = new personal();
        return $model->obtenerPersonal($id);
    }
}
<?php
class ModelExtensionAnagraficaAgenti extends Model {
    public function getAnagraficaAgenti() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anagrafica_agenti`");

        return $query->rows;
    }
}
<?php
class ModelExtensionRecentCustomers extends Model {
    public function getRecentCustomers() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anagrafica_agenti` ORDER BY creazione DESC LIMIT 5");

        return $query->rows;
    }
}
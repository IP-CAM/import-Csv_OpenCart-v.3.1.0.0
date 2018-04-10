<?php
class ModelExtensionRecentCustomers extends Model {
    public function getRecentCustomers() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anagrafica_agenti`");

        return $query->rows;
    }
}
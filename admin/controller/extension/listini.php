<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.03.MD
 */

class ControllerExtensionListini extends Controller
{

    const ANAGRAFICA_LISTINI = '/var/www/html/importer/Anagrafica_listini.csv';

    public function index(){
        $this->importAnagraficaListini(self::ANAGRAFICA_LISTINI);
        echo "Inserimento finito con successo";

    }

    //funzione per leggere i CSV e restiture un array di righe
    function elabora_csv_1($nome_file)
    {
        $csvFile = fopen($nome_file, 'r');
        $csv = array_map('str_getcsv', file($nome_file));
        $headers = $csv[0];
        fgetcsv($csvFile);// skip the first row of the csv file
        $rowsWithKeys = [];
        while(($line = fgetcsv($csvFile, 1000, ";")) !== FALSE){
            $newRow = [];
            $splitHeaders = explode(";", $headers[0]);

            foreach ($splitHeaders as $k => $key) {
                $newRow[$key] = $line[$k];
            }
            $rowsWithKeys[] = $newRow;
        }
        fclose($csvFile);
        return $rowsWithKeys;
    }

    //la funzione restituisce 0 se è da fare una insert, -1 se i valori sono già presenti invariati nel DB, >0, ovvero l'ID della tabella da modificare
    function retrieve_oc_id($csv_id,$opencart_table,$row_csv)
    {
        $string_checksum='';
        foreach($row_csv as $key => $value){
            $string_checksum.=$key.'-'.$value.';';
        }
        $md5=md5($string_checksum);
        $query = $this->db->query("SELECT opencart_id,checksum_md5 FROM " . DB_PREFIX . "checksums WHERE csv_id='".$csv_id."' AND opencart_table='".$opencart_table."'");
        if ($query->num_rows==0) { //si tratta di un nuovo prodotto e quindi va fatto l'inserimento
            return 0;
        }
        else {

            foreach ($query->rows as $result) {
                $checksum_md5=$result['checksum_md5'];
                $id_to_update=$result['opencart_id'];
                if ($checksum_md5==$md5) {
                    return -1*$id_to_update; //nothing to update
                }
                else {
                    return $id_to_update; //id to update
                }
            }
        }
    }

    function sync_checksums($csv_id, $opencart_id, $opencart_table, $row_csv)
    {
        $string_checksum='';
        foreach($row_csv as $key => $value){
            $string_checksum.=$key.'-'.$value.';';
        }
        $md5 = md5($string_checksum);
        $query = $this->db->query("SELECT opencart_id,checksum_md5 FROM " . DB_PREFIX . "checksums WHERE csv_id='".$csv_id."' AND opencart_id=".$opencart_id." AND opencart_table='".$opencart_table."'");

        if ($query->num_rows == 0) { //si tratta di un nuovo prodotto e quindi va fatto l'inserimento

            $query =  $this->db->query("INSERT INTO " . DB_PREFIX . "checksums (`csv_id`, `opencart_id`, `opencart_table`, `checksum_md5`, `date_add`, `date_modified`) VALUES ('".$csv_id."','".$opencart_id."','".$opencart_table."', '".$md5."', '".date('Y-m-d')."', '".date('Y-m-d')."')");

        }
        else {
            $query = $this->db->query("UPDATE `" . DB_PREFIX . "checksums` SET `checksum_md5`='".$md5."',`date_modified`='".date('Y-m-d')."' WHERE csv_id='".$csv_id."' AND opencart_id=".$opencart_id." AND opencart_table='".$opencart_table."'");
        }
    }


    //elaborazione di Anagrafica_listini.csv
    public function importAnagraficaListini($file){

        $righe = $this->elabora_csv_1($file);
        $array_customer_group_inseriti=array(); //array che come chiave avrà l'ID nel CSV dei listini e come valore il customer_group_id in oc_customer_group
        foreach($righe as $key => $row){

            $id_opencart_item = $this->retrieve_oc_id($row["codice"],'oc_customer_group',$row);

            if ($id_opencart_item<0) {
                $array_customer_group_inseriti[$row["codice"]] = abs($id_opencart_item); //nothing to update
            }
            else {
                if ($id_opencart_item==0) {
                    $sql_to_execute="INSERT INTO `oc_customer_group` ( `approval`, `sort_order`) VALUES (0, 1)";
                    $this->db->query($sql_to_execute);

                    $sql = $this->db->query("select customer_group_id from oc_customer_group ORDER BY customer_group_id DESC LIMIT 1");

                    $result_fetched_array=$sql->rows;//variabile che contiene l'array della query
                    $id_opencart_item = $result_fetched_array[0]['customer_group_id'];

                    $this->db->query("INSERT INTO `oc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) VALUES (".$id_opencart_item.", 2, '".addslashes($row['codice'])."', '".addslashes($row['descrizione'])."')");

                }

                $array_customer_group_inseriti[$row["codice"]]=$id_opencart_item;

                $this->sync_checksums($row["codice"],$id_opencart_item,'oc_customer_group',$row);
            }
        }

    }
}
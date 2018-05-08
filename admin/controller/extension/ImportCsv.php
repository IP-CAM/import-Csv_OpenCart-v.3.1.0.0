<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-08
 * Time: 5.04.MD
 */

class ControllerExtensionImportCsv extends Controller
{
    public function index(){

        $data = [];
        $directory = '/var/www/html/opencart/EsempiCSV';
        if (is_dir($directory)){
            if ($handle = opendir($directory)) {
                while (false !== ($file = readdir($handle))) {
                    $results_array[] = $file;
                }
                closedir($handle);
            }
        }
//        $this->elabora_csv($file);
    }


    //funzione per leggere i CSV e restiture un array di righe
    function elabora_csv($nome_file)
    {
        $csvFile = fopen($nome_file, 'r');

        //skip first line
        fgetcsv($csvFile);
        //parse data from csv file line by line
        $righe = [];
        while(($line = fgetcsv($csvFile, 1000, ";")) !== FALSE){
            $righe[] = $line;

        }
        fclose($csvFile);
        return $righe;
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

    function sync_checksums($csv_id,$opencart_id,$opencart_table,$row_csv)
    {
        $string_checksum='';
        foreach($row_csv as $key => $value){
            $string_checksum.=$key.'-'.$value.';';
        }
        $md5=md5($string_checksum);
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "checksums WHERE csv_id='".$csv_id."' AND opencart_id=".$opencart_id." AND opencart_table='".$opencart_table."'");
        if ($query->num_rows==0) { //si tratta di un nuovo prodotto e quindi va fatto l'inserimento
            $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "checksums` (`csv_id`, `opencart_id`, `opencart_table`, `checksum_md5`, `date_add`, `date_modified`) VALUES ('".$csv_id."', ".$opencart_id.", '".$opencart_table."','".$md5.",'".date('Y-m-d')."','".date('Y-m-d')."')");
        }
        else {
            $query = $this->db->query("UPDATE `" . DB_PREFIX . "checksums` SET `checksum_md5`='".$md5."',`date_modified`='".date('Y-m-d')."' WHERE csv_id='".$csv_id."' AND opencart_id=".$opencart_id." AND opencart_table='".$opencart_table."'");
        }
    }


}
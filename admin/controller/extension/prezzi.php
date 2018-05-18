<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.09.MD
 */

class ControllerExtensionPrezzi extends Controller
{

    const ANAGRAFICA_PREZZI = '/var/www/html/opencart/EsempiCSV/Anagrafica_prezzi.csv';

    public function index(){
        $this->importAnagraficaPrezzi(self::ANAGRAFICA_PREZZI);
        echo "Inserimento finito con successo";

    }

    //funzione per leggere i CSV e restiture un array di righe
    function elabora_csv_prezzi($filename)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '180');
        $csv = array();
        $rowsWithKeys = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            $headers = fgetcsv($handle);// skip the first row of the csv file
            $headers = explode(";", $headers[0]);
            while (($result = fgetcsv($handle, 1000, ";")) !== false)  {

                $csv = [
                    $headers[0] => $result[0],
                    $headers[1] => $result[1],
                    $headers[2] => floatval(str_replace(",",".",$result[2])),
                    $headers[3] => $result[3],
                    $headers[4] => $result[4],
                    $headers[5] => $result[5],
                    $headers[6] => $result[6],
                    $headers[7] => $result[7],
                    $headers[8] => $result[8],
                    $headers[9] => $result[9],
                    $headers[10] => $result[10],
                    $headers[11] => $result[11]
                ];

                $rowsWithKeys[] = $csv;

            }
            fclose($handle);
            return $rowsWithKeys;
        }
    }

    //elaborazione di Anagrafica_prezzi.csv
    public function importAnagraficaPrezzi($file){
//
        $righe=$this->elabora_csv_prezzi($file);
        $listino_di_default="LIS_50_0";
        $array_prodotti_inseriti = [];
        $query_update_on_products = [];
        $array_customer_group_inseriti = [];
        foreach($righe as $key => $row){
            if (isset($array_prodotti_inseriti[$row['codice articolo']]) && ($row['codice listino']==$listino_di_default)) {


                if (isset($query_update_on_products[$array_prodotti_inseriti[$row['codice articolo']]])) {
                    $query_update_on_products[$array_prodotti_inseriti[$row['codice articolo']]]=",";
                }
                $query_update_on_products[$array_prodotti_inseriti[$row['codice articolo']].="`price`=".$row['prezzo']];
            }
            elseif (isset($array_customer_group_inseriti[$row['codice listino']])) { //aggiorniamo i prezzi per gli altri clienti
                //aggiorniamo l'associazione allo store
                $sql_to_execute="DELETE FROM `oc_product_discount`  WHERE `product_id`=".$array_prodotti_inseriti[$row['codice articolo']]." AND `customer_group_id`=".$array_customer_group_inseriti[$row['codice listino']];
                $this->db->query($sql_to_execute);

                $sql_to_execute="INSERT INTO `oc_product_to_store` (`product_id`, `customer_group_id`) VALUES  (".$array_prodotti_inseriti[$row['codice articolo']].", ".$array_customer_group_inseriti[$row['codice listino']].",".$row['prezzo'].")";
                $this->db->query($sql_to_execute);
            }

        }

        foreach($query_update_on_products as $key => $row){

            $sql_to_execute="UPDATE `oc_product` SET ".$row." where `product_id`=".$key;
            $this->db->query($sql_to_execute);
        }
    }
}
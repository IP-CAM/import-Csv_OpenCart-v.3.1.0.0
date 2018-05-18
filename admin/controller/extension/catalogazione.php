<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.10.MD
 */

class ControllerExtensionCatalogazione extends Controller
{

    const ANAGRAFICA_CATALOGAZIONE = '/var/www/html/opencart/EsempiCSV/Anagrafica_catalogazione.csv';

    public function index(){
        $this->importAnagraficaCatalogazione(self::ANAGRAFICA_CATALOGAZIONE);
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



    //elaborazione di Anagrafica_catalogazione.csv

    public function importAnagraficaCatalogazione($filename){

        $righe=$this->elabora_csv_1($filename);
        $array_prodotti_inseriti=[];
        $array_category_inserite=[];
        foreach($righe as $key => $row){
            if ((isset($array_prodotti_inseriti[$row['codice articolo']])) && (isset($array_category_inserite[$row['Entita2']]))) {
                $this->db->query("DELETE FROM `oc_product_to_category`  WHERE `product_id`=".$array_prodotti_inseriti[$row['codice articolo']]);

                $this->db->query("INSERT INTO `oc_product_to_category` (`product_id`, `category_id`) VALUES (".$array_prodotti_inseriti[$row['codice articolo']].", ".$array_category_inserite[$row['Entita2']].")");

            }
        }
    }

}
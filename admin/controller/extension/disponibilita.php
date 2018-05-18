<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.07.MD
 */

class ControllerExtensionDisponibilita extends Controller
{
    const ANAGRAFICA_DISPONIBILITA = '/var/www/html/importer/Anagrafica_disponibilita.csv';

    public function index(){
        $this->importAnagraficaDisponibilita(self::ANAGRAFICA_DISPONIBILITA);
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


    //elaborazione di Anagrafica_disponibilita.csv
    public function importAnagraficaDisponibilita($file){
        $query_update_on_products=array();
        $array_prodotti_inseriti=array();
        $righe=$this->elabora_csv_1($file);

        foreach($righe as $key => $row){

            if (isset($array_prodotti_inseriti[$row['codice articolo']])) {
                $quantity=intval($row['inventario']);
                if ($quantity>0) {
                    $stock_status_id=7;//Disponibile
                }
                elseif (intval($row['ordinato fornitore'])>0) {
                    $stock_status_id=6;//In 2-3 Giorni
                }
                else {
                    $stock_status_id=5;//Non disponibile
                }

                $query_update_on_products[$array_prodotti_inseriti[$row['codice articolo']]]="`quantity`=".$row['inventario'].",`stock_status_id`=".$stock_status_id;

            }
        }
    }
}
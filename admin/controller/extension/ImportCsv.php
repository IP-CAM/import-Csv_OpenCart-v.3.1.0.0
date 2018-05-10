<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-08
 * Time: 5.04.MD
 */

class ControllerExtensionImportCsv extends Controller
{
    const ANAGRAFICA_AGENTI = '/var/www/html/opencart/EsempiCSV/Anagrafica_agenti.csv';
    const ANAGRAFICA_ARTICOLI = '/var/www/html/opencart/EsempiCSV/Anagrafica_articoli.csv';
    const ANAGRAFICA_CATALOGAZIONE = '/var/www/html/opencart/EsempiCSV/Anagrafica_catalogazione.csv';
    const ANAGRAFICA_CATEGORIE_CLASSIFICAZIONE = '/var/www/html/opencart/EsempiCSV/Anagrafica_categorie_classificazione.csv';
    const ANAGRAFICA_CLIENTE = '/var/www/html/opencart/EsempiCSV/Anagrafica_cliente.csv';
    const ANAGRAFICA_CODICI_IVA = '/var/www/html/opencart/EsempiCSV/Anagrafica_codici_iva.csv';
    const ANAGRAFICA_DISPONIBILITA = '/var/www/html/opencart/EsempiCSV/Anagrafica_disponibilita.csv';
    const ANAGRAFICA_LISTINI = '/var/www/html/opencart/EsempiCSV/Anagrafica_listini.csv';
    const ANAGRAFICA_MAGAZZINI = '/var/www/html/opencart/EsempiCSV/Anagrafica_magazzini.csv';
    const ANAGRAFICA_ORDINI = '/var/www/html/opencart/EsempiCSV/Anagrafica_ordini.csv';
    const ANAGRAFICA_PAGAMENTI = '/var/www/html/opencart/EsempiCSV/Anagrafica_pagamenti.csv';
    const ANAGRAFICA_PREZZI = '/var/www/html/opencart/EsempiCSV/Anagrafica_prezzi.csv';
    const ANAGRAFICA_SCANDENZARIO = '/var/www/html/opencart/EsempiCSV/Anagrafica_scandenzario.csv';

    public function index(){

        $fileList = glob('/var/www/html/opencart/EsempiCSV/*.csv');

       //Loop through the array that glob returned.
        foreach($fileList as $filename){
            //Simply print all the files out onto the screen.
            if (is_file($filename)){

             if($filename == self::ANAGRAFICA_AGENTI){

             }elseif ($filename == self::ANAGRAFICA_ARTICOLI){
//                 var_dump($this->importAnagraficaArticoli(self::ANAGRAFICA_ARTICOLI));die;
             }elseif ($filename == self::ANAGRAFICA_CATALOGAZIONE){

             }elseif ($filename == self::ANAGRAFICA_CATEGORIE_CLASSIFICAZIONE){
//                 var_dump($this->importAnagraficaCategorieClassificazione(self::ANAGRAFICA_CATEGORIE_CLASSIFICAZIONE));
             }elseif ($filename == self::ANAGRAFICA_CLIENTE){

             }elseif ($filename == self::ANAGRAFICA_CODICI_IVA){

             }elseif ($filename == self::ANAGRAFICA_DISPONIBILITA){
//                 var_dump($this->importAnagraficaDisponibilita(self::ANAGRAFICA_DISPONIBILITA));
             }elseif ($filename == self::ANAGRAFICA_LISTINI){
//               var_dump($this->importAnagraficaListini(self::ANAGRAFICA_LISTINI));die;
             }elseif ($filename == self::ANAGRAFICA_MAGAZZINI){

             }elseif ($filename == self::ANAGRAFICA_ORDINI){

             }elseif ($filename == self::ANAGRAFICA_PAGAMENTI){

             }elseif ($filename == self::ANAGRAFICA_PREZZI){
                 var_dump($this->importAnagraficaPrezzi(self::ANAGRAFICA_PREZZI));die;
             }elseif ($filename == self::ANAGRAFICA_SCANDENZARIO){
                 var_dump("Anagrafica_scandenzario");
             }
            }
        }
        closedir($fileList);
        echo "u be me ne fundddd";
    }


    //funzione per leggere i CSV e restiture un array di righe
    function elabora_csv($filename, $delimiter=',',  $enclosure='"', $escape = '\\')
    {
        $rows = file($filename);
        $header = array_shift($rows); //get the header out
        $header = explode(";", $header);
        $final_array = array();
        foreach ($rows as $row) {
            $row = explode(";", $row);

            $final_array[] = array($header[0] => $row[0], $header[1] => $row[1], $header[2] => $row[2]);
        }
        return $final_array;
    }

    //restituire un array di righe dal
    public function readCsvAnagraficaPrezzi($csvfile){

        $csvFile = fopen($csvfile, 'r');
        $newRow = [];
        fgetcsv($csvFile);// skip the first row of the csv file
        while(($line = fgetcsv($csvFile, 1000, ";")) !== FALSE){

            $rowsWithKeys = [
                "codice articolo" =>$line[0],
                "codice listino" => $line[1],
                "prezzo" => $line[2],
                "unita vendita" => $line[3],
                "fattore vendita" => $line[4],
                "note" => $line[5],
                "sconto" => $line[6],
                "sconto_max" => $line[7],
                "stato" => $line[8],
                "ultima modifica" => $line[9],
                "flag fedelta" => $line[10],
                "flag sconto" => $line[11],
                "flag" => $line[12]

            ];
          $newRow[] = $rowsWithKeys;

        }
        fclose($csvFile);
        return $newRow;

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
                $newRow[$key] = $line[intval($k)];
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

    public function create_slug($text){
        $replace = [
            '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
            '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä'=> 'Ae',
            '&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
            'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
            'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
            'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
            'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
            'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
            'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
            'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
            'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
            'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
            'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
            '&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
            'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
            'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
            'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
            'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
            'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
            'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
            'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
            'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
            'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
            'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
            'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
            '&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
            'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
            'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
            'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
            'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
            'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
            'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
            'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
            'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
            'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
            'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
            'ю' => 'yu', 'я' => 'ya'
        ];

        // make a human readable string
        $text = strtr($text, $replace);

        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d.]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // remove unwanted characters
        $text = preg_replace('~[^-\w.]+~', '', $text);

        $text = strtolower($text);

        return $text;
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

                    $sql_to_execute= $this->db->query("INSERT INTO `oc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) VALUES (".$id_opencart_item.", 2, '".addslashes($row['codice'])."', '".addslashes($row['descrizione'])."')");

                }

                $array_customer_group_inseriti[$row["codice"]]=$id_opencart_item;

               $this->sync_checksums($row["codice"],$id_opencart_item,'oc_customer_group',$row);
            }
        }

    }

    //elaborazione di Anagrafica_categorie_classificazione.csv
    public function importAnagraficaCategorieClassificazione($file){
        $righe=$this->elabora_csv_1($file);
//        var_dump($righe);die;
        $array_category_inserite=array(); //array che come chiave avrà l'ID nel CSV delle categorie e come valore il category_id in oc_category

        foreach($righe as $key => $row){

            $id_opencart_item=$this->retrieve_oc_id($row["codice categoria"],'oc_category',$row);
            if ($id_opencart_item < 0) {
                $array_category_inserite[$row["codice categoria"]]=$id_opencart_item; //nothing to update
            }
            else {

                if ($row['stato']=='SI') {
                    $category_status=1;
                }
                else {
                    $category_status=0;
                }
                if ($row['codice sezione']=='CAT') {
                    $parent_category_id=0;
                    $category_level=0;
                }
                else {
                    //retrieve tra parent category
                    $temp=explode('  ',$row['codice categoria']);
                    $parent_category_csvid=$temp[count($temp)-1];
//                    var_dump($parent_category_csvid);die;
                    $category_level=count($temp)-1;
                    $sql = $this->db->query("SELECT oc_id from oc_synch where oc_id=".$parent_category_csvid." and module='Category' LIMIT 1");

                    $result_fetched_array_parent=$sql->rows;//variabile che contiene l'array della query

                    $parent_category_id=$result_fetched_array_parent[0]['oc_id'];
                }

                 $data_update = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $row['ultima modifica'])));

                if ($id_opencart_item == 0) {
                    $sql_to_execute=$this->db->query("INSERT INTO `oc_category` ( `image`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`, `sorting`, `viewtype`, `itemsperpage`, `showviewtype`, `showsorting`, `showitemsperpage`) VALUES ( '', ".$parent_category_id.", 0, 2, 2, ".$category_status.", '".$data_update."', '".$data_update."', 1, 1, 15, 0, 0, 0)");

                    $sql=$this->db->query("select category_id from oc_category ORDER BY category_id DESC LIMIT 1");
                    $result_fetched_array=$sql->rows;//variabile che contiene l'array della query
                    $id_opencart_item=$result_fetched_array[0]['category_id'];

                }
                else {
                    $sql_to_execute=$this->db->query("UPDATE `oc_category` SET `parent_id`=".$parent_category_id.", `status`=".$category_status.", `date_modified`='".$data_update."' where `category_id`=".$id_opencart_item);
                }

                $array_category_inserite[$row["codice categoria"]]=$id_opencart_item;
                $this->sync_checksums($row["codice categoria"],$id_opencart_item,'oc_category',$row);

                //aggiungiamo una voce in oc_category_path
                if ($parent_category_id==0) {
                    $path_id=$id_opencart_item;
                }
                else {
                    $path_id=$parent_category_id;
                }

                $sql_to_execute="DELETE FROM `oc_category_path`  WHERE `category_id`=".$id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute="INSERT INTO `oc_category_path` (`category_id`, `path_id`, `level`) VALUES
		        (".$id_opencart_item.", ".$path_id.", ".$category_level.")";
                $this->db->query($sql_to_execute);

                //Aggiorniamo la descrizione della categoria
                $sql_to_execute="DELETE FROM `oc_category_description`  WHERE `category_id`=".$id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute="INSERT INTO `oc_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES
	        	(".$id_opencart_item.", 2, '".$row["descrizione categoria"]."', '', '".$row["descrizione categoria"]."', '', '')";
                $this->db->query($sql_to_execute);

                //aggiorniamo il layout
                $sql_to_execute="DELETE FROM `oc_category_to_layout`  WHERE `category_id`=".$id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute="INSERT INTO `oc_category_to_layout` (`category_id`, `store_id`, `layout_id`) VALUES (".$id_opencart_item.", 0,0)";
                $this->db->query($sql_to_execute);

                //aggiorniamo l'associazione allo store
                $sql_to_execute="DELETE FROM `oc_category_to_store`  WHERE `category_id`=".$id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute="INSERT INTO `oc_category_to_store` (`category_id`, `store_id`) VALUES (".$id_opencart_item.", 0)";
                $this->db->query($sql_to_execute);

                //aggiorniamo i link allo store
                //aggiorniamo l'associazione allo store
                $sql_to_execute="DELETE FROM `oc_seo_url`  WHERE `query`='category_id=".$id_opencart_item."'";
                $this->db->query($sql_to_execute);

                //creare una funzione che fa lo slug della
                $slug=$this->create_slug($row["descrizione categoria"]);

                //controlliamo prima l'univocità dello slug_altrimenti gli appendiamo l'id della categoria
                $sql=$this->db->query("SELECT * from oc_seo_url where keywork='".$slug."' LIMIT 1");

                $result_fetched_array_seo_url=$sql->rows;//variabile che contiene l'array della query

                if (count($result_fetched_array_seo_url)>0) {
                    $slug.='_'.$id_opencart_item;
                }
                $sql_to_execute="INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`) VALUES(0, 1, 'category_id=".$id_opencart_item."', '".$slug."')";
                $this->db->query($sql_to_execute);
            }
        }

    }

   //elaborazione di Anagrafica_articoli.csv
    public function importAnagraficaArticoli($file){

        $righe=$this->elabora_csv($file);
        var_dump($righe);die;
        $array_prodotti_inseriti=array(); //array che come chiave avrà l'ID nel CSV dei prodotti e come valore il product_id in oc_product
        foreach($righe as $key => $row) {
            var_dump($row);die;
            $id_opencart_item = $this->retrieve_oc_id($row["codice"], 'oc_product', $row);
            if ($id_opencart_item < 0) {
                $array_prodotti_inseriti[$row["codice"]] = $id_opencart_item; //nothing to update
            } else {
                $data_update = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $row['ultima modifica'])));

                $data_disponibilitae = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $row['data disponibilita'])));

                $Data_scadenza = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $row['Data_scadenza'])));

                $minimum = "";
                if ($id_opencart_item == 0) {

                    //quantity e stock_status_id li aggiorneremo dopo sulla base di Anagrafica_diponinilita;
                    $quantity = 0;
                    $stock_status_id = 0;
                    $prezzo = 0; //lo aggiorniamo sul parsing dell'anagrafica listini
                    $status = 1;
                    $minimum = $row['pezzi_confezione'];
                    $sql_to_execute = "INSERT INTO `oc_product` (`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `sort_order`, `status`, `viewed`, `date_added`, `date_modified`, `date_start`, `date_end`) VALUES ('" . addslashes($row["descrizione"]) . "', '" . addslashes($row["codice"]) . "', '', '', '', '', '', '', " . $quantity . ",  " . $stock_status_id . ", 'catalog/" . addslashes($row["codice"]) . ".jpg', 0, 1, " . $prezzo . ", 0, 0, '2017-01-01', 0.00000000, 1, 0.00000000, 0.00000000, 0.00000000, 1, " . $minimum . ", " . $minimum . ", 1, " . $status . ", 0, '" . $data_update . "', '" . $data_update . "', '0000-00-00 00:00:00', '0000-00-00 00:00:00')";

                    $this->db->query($sql_to_execute);

                    $sql = "select product_id from oc_product ORDER BY product_id DESC LIMIT 1";
                    $this->db->query($sql);
                    $result_fetched_array = [];//variabile che contiene l'array della query
                    $id_opencart_item = $result_fetched_array['product_id'];


                    $sql_to_execute = "INSERT INTO `oc_product_image` (`product_id`, `image`, `sort_order`) VALUES (" . $id_opencart_item . ", 'catalog/" . $row["codice"] . ".jpg', 0)";
                    $this->db->query($sql_to_execute);

                } else {
                    $sql_to_execute = "UPDATE `oc_product` SET `model`='" . addslashes($row["descrizione"]) . "',`subtract`=" . $minimum . ", `minimum`=" . $minimum . ", `date_modified`='" . $data_update . "' where `product_id`=" . $id_opencart_item;
                    $this->db->query($sql_to_execute);
                }
                $array_prodotti_inseriti[$row["codice"]] = $id_opencart_item;
                $this->sync_checksums($row["codice"], $id_opencart_item, 'oc_product', $row);

                //Aggiorniamo la descrizione della categoria
                $sql_to_execute = "DELETE FROM `oc_product_description`  WHERE `product_id`=" . $id_opencart_item;
                $this->db->query($sql_to_execute);


                $sql_to_execute = "INSERT INTO `oc_product_description` (`product_id`, `language_id`, `name`, `subtitle`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`, `introtext`) VALUES
	         	(" . $id_opencart_item . ", 2, '" . addslashes($row["descrizione"]) . "', '', '" . addslashes($row["descrizione aggiuntiva"]) . "', '" . addslashes($row["descrizione aggiuntiva"]) . "','" . addslashes($row["descrizione aggiuntiva"]) . "','" . addslashes($row["descrizione aggiuntiva"]) . "','" . addslashes($row["descrizione aggiuntiva"]) . "','" . addslashes($row["descrizione aggiuntiva"]) . "')";

                $this->db->query($sql_to_execute);

                $sql_to_execute = "INSERT INTO `oc_product_to_category` (`product_id`, `category_id`) VALUES
	         	(" . $id_opencart_item . ", 2, )";

                $this->db->query($sql_to_execute);


                //aggiorniamo il layout
                $sql_to_execute = "DELETE FROM `oc_product_to_layout`  WHERE `product_id`=" . $id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute = "INSERT INTO `oc_product_to_layout` (`product_id`, `store_id`, `layout_id`) VALUES(" . $id_opencart_item . ", 0,0)";
                $this->db->query($sql_to_execute);

                //aggiorniamo l'associazione allo store
                $sql_to_execute = "DELETE FROM `oc_product_to_store`  WHERE `product_id`=" . $id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute = "INSERT INTO `oc_product_to_store` (`product_id`, `store_id`) VALUES VALUES (" . $id_opencart_item . ", 0)";
                $this->db->query($sql_to_execute);

                //aggiorniamo i link allo store
                //aggiorniamo l'associazione allo store
                $sql_to_execute = "DELETE FROM `oc_seo_url`  WHERE `query`='product_id=" . $id_opencart_item . "'";
                $this->db->query($sql_to_execute);

                //creare una funzione che fa lo slug della
                $slug = $this->create_slug($row["descrizione categoria"]);

                //controlliamo prima l'univocità dello slug_altrimenti gli appendiamo l'id della categoria
                $sql = "SELECT * from oc_seo_url where keyword='" . $slug . "'";
                $this->db->query($sql);
                $result_fetched_array_seo_url = [];//variabile che contiene l'array della query

                if (count($result_fetched_array_seo_url) > 0) {
                    $slug .= '_' . $id_opencart_item;
                }
                $sql_to_execute = "INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`) VALUES(0, 1, 'product_id=" . $id_opencart_item . "', '" . $slug . "')";
                $this->db->query($sql_to_execute);
            }
        }
    }

    //elaborazione di Anagrafica_disponibilita.csv
    public function importAnagraficaDisponibilita($file){
        $query_update_on_products=array();
        $array_prodotti_inseriti=array();
        $righe=$this->elabora_csv($file);
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

//    //elaborazione di Anagrafica_prezzi.csv
    public function importAnagraficaPrezzi($file){
//
        $query_update_on_products=array();
        $righe= $this->elabora_csv($file);
        var_dump($righe);die;

        $array_prodotti_inseriti = [];
        $array_customer_group_inseriti = [];
        $listino_di_default="LIS_50_0";
        foreach($righe as $key => $row){
            var_dump($row['codice articolo']);die;
            $codice_articolo = $array_prodotti_inseriti[$row['codice articolo']];
            $codice_listino = $row['codice listino'];

            if ((isset($codice_listino) &&  $codice_listino == $listino_di_default)) {

			if (isset($query_update_on_products[$codice_articolo])) {
                $query_update_on_products[$codice_articolo]=",";
            }
            $query_update_on_products[$array_prodotti_inseriti[$row['codice articolo']].="`price`=".$row['prezzo']];

        }
        elseif (isset($array_customer_group_inseriti[$row['codice listino']])) { //aggiorniamo i prezzi per gli altri clienti
			//aggiorniamo l'associazione allo store
			$sql_to_execute=$this->db->query("DELETE FROM `oc_product_discount`  WHERE `product_id`=".$array_prodotti_inseriti[$row['codice articolo']]." AND `customer_group_id`=".$array_customer_group_inseriti[$row['codice listino']]);

            $sql_to_execute=$this->db->query("INSERT INTO `oc_product_to_store` (`product_id`, `customer_group_id`) VALUES  (".$array_prodotti_inseriti[$row['codice articolo']].", ".$array_customer_group_inseriti[$row['codice listino']].",".$row['prezzo'].")");
        }

        }

        foreach($query_update_on_products as $key => $row){
            $sql_to_execute  =$this->db->query("UPDATE `oc_product` SET ".$row." where `product_id`=".$key);

        }
    }
}
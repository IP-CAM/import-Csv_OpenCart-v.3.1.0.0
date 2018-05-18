<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.06.MD
 */

class ControllerExtensionArticoli extends Controller
{

    const ANAGRAFICA_ARTICOLI = '/var/www/html/opencart/EsempiCSV/Anagrafica_articoli.csv';

    public function index(){
        $this->importAnagraficaArticoli(self::ANAGRAFICA_ARTICOLI);
        echo "Inserimento finito con successo";

    }

    //funzione per leggere i CSV e restiture un array di righe
    function elabora_csv_articoli($filename,$enclosure="'", $escapestring="'"){

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '180');
        $rowsWithKeys = [];

        if (($handle = fopen($filename, "r")) !== FALSE) {
            $headers = fgetcsv($handle);// skip the first row of the csv file
            $splitHeaders = explode(";", $headers[0]);
            while(($line = fgetcsv($handle, 1000, ";",$enclosure, $escapestring)) !== FALSE){

                $newRow = [];
                foreach ($splitHeaders as $k => $key) {
                    $newRow[$key] = $line[$k];
                }
                $rowsWithKeys[] = $newRow;
            }

            fclose($handle);
            return $rowsWithKeys;
        }
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

    //elaborazione di Anagrafica_articoli.csv
    public function importAnagraficaArticoli($file){

        $righe=$this->elabora_csv_articoli($file);

        $array_prodotti_inseriti=array(); //array che come chiave avrà l'ID nel CSV dei prodotti e come valore il product_id in oc_product
        foreach($righe as $key => $row) {

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

                    $sql = $this->db->query("select product_id from oc_product ORDER BY product_id DESC LIMIT 1");
                    $result_fetched_array=$sql->rows;//variabile che contiene l'array della query
                    $id_opencart_item = $result_fetched_array[0]['product_id'];;

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
	         	(" . $id_opencart_item . ", 2)";
                $this->db->query($sql_to_execute);


                //aggiorniamo il layout
                $sql_to_execute = "DELETE FROM `oc_product_to_layout`  WHERE `product_id`=" . $id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute = "INSERT INTO `oc_product_to_layout` (`product_id`, `store_id`, `layout_id`) VALUES(" . $id_opencart_item . ", 0,0)";
                $this->db->query($sql_to_execute);
                //aggiorniamo l'associazione allo store
                $sql_to_execute = "DELETE FROM `oc_product_to_store`  WHERE `product_id`=" . $id_opencart_item;
                $this->db->query($sql_to_execute);

                $sql_to_execute = "INSERT INTO `oc_product_to_store` (`product_id`, `store_id`) VALUES  (" . $id_opencart_item . ", 0)";
                $this->db->query($sql_to_execute);

                //aggiorniamo i link allo store
                //aggiorniamo l'associazione allo store
                $sql_to_execute = "DELETE FROM `oc_seo_url`  WHERE `query`='product_id=" . $id_opencart_item . "'";
                $this->db->query($sql_to_execute);

                //creare una funzione che fa lo slug della
                $slug = $this->create_slug($row["descrizione aggiuntiva"]);

                //controlliamo prima l'univocità dello slug_altrimenti gli appendiamo l'id della categoria
                $sql = "SELECT * from oc_seo_url where keyword='" . $slug . "'";
                $sql = $this->db->query($sql);
                $result_fetched_array_seo_url = $sql->rows;//variabile che contiene l'array della query

                if (count($result_fetched_array_seo_url) > 0) {
                    $slug .= '_' . $id_opencart_item;
                }
                $sql_to_execute = "INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`) VALUES(0, 1, 'product_id=" . $id_opencart_item . "', '" . $slug . "')";
                $this->db->query($sql_to_execute);

            }
        }
    }

}
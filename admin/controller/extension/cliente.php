<?php
/**
 * Created by PhpStorm.
 * User: ina
 * Date: 18-05-18
 * Time: 6.10.MD
 */

class ControllerExtensionCliente extends Controller
{
    const ANAGRAFICA_CLIENTE = '/var/www/html/opencart/EsempiCSV/Anagrafica_cliente.csv';


    public function index(){
        $this->importAnagraficaCliente(self::ANAGRAFICA_CLIENTE);
        echo "Clinte inserito con successo";

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

    //elaborazione di Anagrafica_cliente.csv
    public function importAnagraficaCliente($filename){
        $righe=$this->elabora_csv_1($filename);
        $array_clienti_inseriti=array(); //array che come chiave avrà l'ID nel CSV dei clienti e come valore il customer_id in oc_customer

        foreach($righe as $key => $row){
            $id_opencart_item=$this->retrieve_oc_id($row["codice"],'oc_customer',$row);
            if ($id_opencart_item < 0) {
                $array_clienti_inseriti[$row["codice"]]=$row["codice"]; //nothing to update
            }
            else {

                $firstname=addslashes($row["ragione sociale"]);
                $lastname=addslashes($row["ragione sociale"]);
                $company=addslashes($row["ragione sociale"]);
                $address_1=addslashes($row["via"]);
                $email=str_replace("'", "", $row['Email']);
                if ($email=='') $email=$row['codice'].'@sweeping.it';
                $telephone=$row['telefono'];
                $fax=$row['fax'];
                $cap=$row['cap'];
                $password="3a4bb0b51fee300cdb19370cab4a434f02ed818c"; //mvtech.2018
                $salt='f77B00sKY';
                $date_insert=date('Y-m-d H:i:s');
                $token = $this->session->data['user_token'];
                $code = $row['codice'];

                //recuperiamo oc_zone
                $sql="select * from oc_zone where UPPER(code)='".strtoupper($row['Prov'])."'";
                $query = $this->db->query($sql);

                if ($query->num_rows==0) { //si tratta di un nuovo prodotto e quindi va fatto l'inserimento

                    $zone_id=3924; //Roma
                    $country_id=105;
                    $sql_to_execute="INSERT INTO `oc_zone` ( `zone_id`, `country_id`, `name`, `code`, `status`) VALUES ('".$zone_id."','".$country_id."' ,'".$row['Citta']."','".$row['Prov']."',1)";
                    $this->db->query($sql_to_execute);
                }
                else {

                    foreach ($query->rows as $result) {
                        $zone_id=$result['zone_id'];
                        $country_id=$result['country_id'];
                    }
                    $city=addslashes($row['Citta']);

                    if ($id_opencart_item==0) {

                        $sql_to_execute="INSERT INTO `oc_customer_group` ( `approval`, `sort_order`) VALUES (0, 1)";
                        $this->db->query($sql_to_execute);

                        $sql = $this->db->query("select customer_group_id from oc_customer_group ORDER BY customer_group_id DESC LIMIT 1");

                        $result_fetched_array=$sql->rows;//variabile che contiene l'array della query
                        $customers_group_id = $result_fetched_array[0]['customer_group_id'];

                        $sql = "INSERT INTO `oc_customer` (`customer_group_id`, `store_id`, `language_id`, `firstname`, `lastname`, `email`, `telephone`, `fax`, `password`, `salt`, `cart`, `wishlist`, `newsletter`, `address_id`, `custom_field`, `ip`, `status`, `safe`, `token`, `code`, `date_added`) VALUES ('".$customers_group_id."', 0, 2, '".$firstname."', '".$lastname."', '".$email."', '".$telephone."', '".$fax."', '".$password."', '".$salt."', NULL, NULL, 0, 0, '', '172.17.0.29', 1, 0, '".$token."', '".$code."', '".$date_insert."')";

                        $this->db->query($sql);

                        $sql="select product_id from oc_product ORDER BY product_id DESC LIMIT 1";
                        $sql = $this->db->query($sql);
                        $result_fetched_array=$sql->rows;//variabile che contiene l'array della query
                        $id_opencart_item=$result_fetched_array[0]['product_id'];

                    }
                    else {
                        $this->db->query("UPDATE `oc_address` SET `firstname`='".$firstname."',`lastname`='".$lastname."',`company`='".$company."',`address_1`='".$address_1."',`address_2`='',	`city`='".$city."',	`postcode`='".$cap."',`country_id`=".$country_id.",`zone_id`=".$zone_id." where `customer_id`=".$id_opencart_item);
                    }

                    $array_clienti_inseriti[$row["codice"]]=$id_opencart_item;
                    $this->sync_checksums($row["codice"],$id_opencart_item,'oc_customer',$row);


                    //eseguiamo prima l'address_id
                    $this->db->query("DELETE FROM `oc_address`  WHERE `customer_id`=".$id_opencart_item);

                    $sql_to_execute="INSERT INTO `oc_address` (`customer_id`, `firstname`, `lastname`, `company`, `address_1`, `address_2`, `city`, `postcode`, `country_id`, `zone_id`, `custom_field`) VALUES (".$id_opencart_item.", '".$firstname."', '".$lastname."', '".$company."', '".$address_1."', '', '".$city."', '".$cap."', ".$country_id.", ".$zone_id.", '')";
                    $this->db->query($sql_to_execute);
                }
            }

        }
    }
}
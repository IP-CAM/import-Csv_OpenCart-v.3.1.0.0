<?php
class ControllerExtensionModuleAnagraficaAgenti extends Controller{
    public function index() {

        $this->load->language('extension/module/anagrafica_agenti');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['codice'] = $this->language->get('codice');
        $data['codice_agente_padre'] = $this->language->get('codice_agente_padre');
        $data['codice_ruolo'] = $this->language->get('codice_ruolo');
        $data['ragione_sociale'] = $this->language->get('ragione_sociale');
        $data['attivo'] = $this->language->get('attivo');
        $data['p_iva'] = $this->language->get('p_iva');
        $data['codice_fiscale'] = $this->language->get('codice_fiscale');
        $data['via'] = $this->language->get('via');
        $data['cap'] = $this->language->get('cap');
        $data['provincia'] = $this->language->get('provincia');
        $data['citta'] = $this->language->get('citta');
        $data['latitudine'] = $this->language->get('latitudine');
        $data['longtitudine'] = $this->language->get('longtitudine');
        $data['nazione'] = $this->language->get('nazione');
        $data['telefono'] = $this->language->get('telefono');
        $data['fax'] = $this->language->get('fax');
        $data['sito_web'] = $this->language->get('sito_web');
        $data['email'] = $this->language->get('email');
        $data['codice_listino'] = $this->language->get('codice_listino');
        $data['note'] = $this->language->get('note');
        $data['creazione'] = $this->language->get('creazione');
        $data['ultima_modifica'] = $this->language->get('ultima_modifica');
        $data['mail_customer'] = $this->language->get('mail_customer');
        $data['categoria_vendita'] = $this->language->get('categoria_vendita');

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_list'] = $this->language->get('text_list');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        /**
         * If there is any warning in the private property '$error', then it will be put into '$data' array
         */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        /**
         * Breadcrumbs are declared as array
         */
        $data['breadcrumbs'] = array();
        /**
         * Breadcrumbs are defined
         */
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], $this->ssl)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], $this->ssl)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/anagrafica_agenti', 'user_token=' . $this->session->data['user_token'], $this->ssl)
        );
        /**
         * Header data is loaded
         */
        $data['header'] = $this->load->controller('common/header');
        /**
         * Column left part is loaded
         */
        $data['column_left'] = $this->load->controller('common/column_left');
        /**
         * Footer data is loaded
         */
        $data['footer'] = $this->load->controller('common/footer');

        $data['anagrafica_agenti'] = array();

        $this->load->model('extension/anagrafica_agenti');
        $results = $this->model_extension_anagrafica_agenti->getAnagraficaAgenti();

        foreach ($results as $result) {
            $data['anagrafica_agenti'][] = array(
                'codice' => $result['codice'],
                'codice_agente_padre' => $result['codice_agente_padre'],
                'codice_ruolo' => $result['codice_ruolo'],
                'ragione_sociale' => $result['ragione_sociale'],
                'attivo' => $result['attivo'],
                'p_iva' => $result['p_iva'],
                'codice_fiscale' => $result['codice_fiscale'],
                'via' => $result['via'],
                'cap' => $result['cap'],
                'provincia' => $result['provincia'],
                'citta' => $result['citta'],
                'latitudine' => $result['latitudine'],
                'longtitudine' => $result['longtitudine'],
                'nazione' => $result['nazione'],
                'telefono' => $result['telefono'],
                'fax' => $result['fax'],
                'sito_web' => $result['sito_web'],
                'email' => $result['email'],
                'codice_listino' => $result['codice_listino'],
                'note' => $result['note'],
                'creazione' => $result['creazione'],
                'ultima_modifica' => $result['ultima_modifica'],
                'mail_customer' => $result['mail_customer'],
                'categoria_vendita' => $result['categoria_vendita'],
            );
        }

        $this->response->setOutput($this->load->view('extension/module/anagrafica_agenti', $data));
    }
}

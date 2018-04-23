<?php
class ControllerExtensionModuleRecentcustomers extends Controller {
    public function index() {

        $this->load->language('extension/module/recentcustomers');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['column_customer_id'] = $this->language->get('column_customer_id');
        $data['column_customer_name'] = $this->language->get('column_customer_name');
        $data['column_customer_email'] = $this->language->get('column_customer_email');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['codice_agente_padre'] = $this->language->get('codice_agente_padre');
        $data['text_no_results'] = $this->language->get('text_no_results');

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
            'href' => $this->url->link('extension/module/recentcustomers', 'user_token=' . $this->session->data['user_token'], $this->ssl)
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

        $data['recentcustomers'] = array();

        $this->load->model('extension/recentcustomers');
        $results = $this->model_extension_recentcustomers->getRecentCustomers();

        foreach ($results as $result) {
            $data['recentcustomers'][] = array(
                'customer_id' => $result['codice'],
                'name' => $result['ragione_sociale'],
                'email' => $result['email'],
                'date_added' => $result['creazione'],
                'codice_agente_padre' => $result['codice_agente_padre'],
            );
        }

        $this->response->setOutput($this->load->view('extension/module/recentcustomers', $data));
    }
}
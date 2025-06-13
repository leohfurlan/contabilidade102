<?php defined('BASEPATH') or exit('No direct script access allowed');

class Selecionarcliente extends AdminController
{
    public function index()
    {
        $return = $this->input->get('return') ?: admin_url('contabilidade102');
        $this->load->model('clients_model');
        $data['clientes'] = $this->clients_model->get();

        if ($this->input->post('cliente_id')) {
            $this->load->library('clientecontext');   // ← minúsculo ao carregar
            $this->clientecontext->set($this->input->post('cliente_id'));
            redirect($return);
        }

        $data['title']  = _l('contabilidade102_escolha_cliente');
        $data['return'] = $return;
        $this->load->view('contabilidade102/selecionarcliente', $data);
    }
}

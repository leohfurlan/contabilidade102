<?php defined('BASEPATH') or exit('No direct script access allowed');

class Clientecontext
{
    const SESSION_KEY = 'cliente_selecionado';
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function get()
    {
        $id = $this->CI->session->userdata(self::SESSION_KEY);
        if (!$id) { return null; }
        $this->CI->load->model('clients_model');
        return $this->CI->clients_model->get($id);
    }

    public function set($cliente_id)
    {
        $this->CI->session->set_userdata([self::SESSION_KEY => (int)$cliente_id]);
    }

    public function clear()
    {
        $this->CI->session->unset_userdata(self::SESSION_KEY);
    }

    public function ensureSelected()
    {
        if (!$this->get()) {
            $current = current_url();
            redirect(admin_url('contabilidade102/selecionarcliente?return=' . urlencode($current)));
        }
    }
}

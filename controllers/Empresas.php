<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Empresas extends AdminController
{
    /**
     * Nome do módulo/pasta dentro de modules/
     * (pode ser sobrescrito por uma constante definida no helper do módulo).
     */
    protected $module_name;

    public function __construct()
    {
        parent::__construct();

        // ------------------------------------------------------------------
        // Configurações básicas
        // ------------------------------------------------------------------
        $this->module_name = defined('CONTABILIDADE102_MODULE_NAME')
            ? CONTABILIDADE102_MODULE_NAME
            : 'contabilidade102';

        // Permissão mínima para acessar qualquer tela do módulo
        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }

        // ------------------------------------------------------------------
        // Dependências
        // ------------------------------------------------------------------
        // Carrega Cadastro_model e força alias em minúsculo (cadastro_model)
        $this->load->model($this->module_name . '/Cadastro_model', 'cadastro_model');
        $this->load->model('clients_model');
        $this->load->library('form_validation');
    }

    // ----------------------------------------------------------------------
    // LISTAGEM
    // ----------------------------------------------------------------------
    public function index()
    {
        $data['title']    = _l('contabilidade102_empresas_vinculadas_titulo_lista');
        $data['empresas'] = $this->cadastro_model->get_all_empresas_vinculadas();

        $this->load->view($this->module_name . '/empresas/index', $data);
    }

    // ----------------------------------------------------------------------
    // FORMULÁRIO DE VÍNCULO / EDIÇÃO
    // ----------------------------------------------------------------------
    /**
     * @param int|string $id  ID do vínculo (contabilidade_empresas). Vazio = novo
     */
    public function manage($id = '')
    {
        // ---- Permissões ---------------------------------------------------
        if (empty($id) && !has_permission($this->module_name, '', 'create')) {
            access_denied($this->module_name);
        }
        if (!empty($id) && !has_permission($this->module_name, '', 'edit')) {
            access_denied($this->module_name);
        }

        // ---- Dados do vínculo (modo edição) ------------------------------
        $data['empresa_contabil'] = null;
        if (!empty($id)) {
            $data['empresa_contabil'] = $this->cadastro_model->get_empresa_contabil($id);
            if (!$data['empresa_contabil']) {
                set_alert('danger', _l('contabilidade102_empresa_nao_encontrada'));
                redirect(admin_url($this->module_name . '/empresas'));
            }
        }

        // ---- Título -------------------------------------------------------
        $data['title'] = empty($id)
            ? _l('contabilidade102_vincular_nova_empresa_titulo')
            : _l('contabilidade102_editar_vinculo_empresa_titulo');

        // ---- Clientes Perfex disponíveis ----------------------------------
        $perfex_clients = $this->clients_model->get();
        $clientes_disponiveis = [];

        if (empty($id)) {
            // Exclui clientes já vinculados
            $ids_vinculados = array_column(
                $this->cadastro_model->get_all_empresas_vinculadas(),
                'cliente_id'
            );
            foreach ($perfex_clients as $cli) {
                if (!in_array($cli['userid'], $ids_vinculados)) {
                    $clientes_disponiveis[] = $cli;
                }
            }
        } else {
            // Em edição lista todos (o select ficará desabilitado na view)
            $clientes_disponiveis = $perfex_clients;
        }
        $data['clientes_disponiveis'] = $clientes_disponiveis;

        // ---- Contadores ativos -------------------------------------------
        $data['contadores'] = $this->cadastro_model->get_all_contadores();

        // ---- Carrega view -------------------------------------------------
        $this->load->view($this->module_name . '/empresas/manage', $data);
    }

    // ----------------------------------------------------------------------
    // PROCESSAMENTO DO FORMULÁRIO
    // ----------------------------------------------------------------------
    public function processar_vinculo($id = '')
    {
        $perm = empty($id) ? 'create' : 'edit';
        if (!has_permission($this->module_name, '', $perm)) {
            access_denied($this->module_name);
        }

        if (!$this->input->post()) {
            redirect(admin_url($this->module_name . '/empresas'));
        }

        // ---- Validação ----------------------------------------------------
        $this->form_validation->set_rules('cliente_id', _l('contabilidade102_cliente_perfex'), 'required');

        if ($this->form_validation->run() === false) {
            set_alert('danger', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        $post   = $this->input->post(null, true);
        $result = $this->cadastro_model->vincular_ou_atualizar_empresa($id, $post);

        if ($result) {
            set_alert('success', _l(empty($id)
                ? 'contabilidade102_empresa_vinculada_sucesso'
                : 'contabilidade102_vinculo_atualizado_sucesso'));
        } else {
            set_alert('danger', _l('contabilidade102_erro_processar_vinculo'));
        }

        redirect(admin_url($this->module_name . '/empresas'));
    }

    // ----------------------------------------------------------------------
    // REMOVER VÍNCULO
    // ----------------------------------------------------------------------
    public function remover_vinculo($id)
    {
        if (!has_permission($this->module_name, '', 'delete')) {
            access_denied($this->module_name);
        }

        if ($this->cadastro_model->remover_vinculo_empresa($id)) {
            set_alert('success', _l('contabilidade102_vinculo_removido_sucesso'));
        } else {
            set_alert('danger', _l('contabilidade102_erro_remover_vinculo'));
        }
        redirect(admin_url($this->module_name . '/empresas'));
    }
}

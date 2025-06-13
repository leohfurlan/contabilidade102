<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Empresas extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = 'contabilidade102';
        if (defined('CONTABILIDADE102_MODULE_NAME')) {
            $this->module_name = CONTABILIDADE102_MODULE_NAME;
        }

        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }
        
        // Garante que os models são carregados com a capitalização correta
        $this->load->model($this->module_name . '/Cadastro_model');
        $this->load->model('clients_model');
        
        $this->load->library('form_validation');
    }

    /**
     * Lista as empresas/clientes Perfex vinculados à contabilidade.
     */
    public function index()
    {
        // Teste de Debug (descomente as linhas uma por uma para encontrar o erro)

        // Teste 1: Apenas o título
        $data['title']    = _l('contabilidade102_empresas_vinculadas_titulo_lista');
        
        // Teste 2: Tente carregar os dados do model. Se a página quebrar aqui, o erro está no model.
        // $data['empresas'] = $this->Cadastro_model->get_all_empresas_vinculadas();

        // Teste 3: Tente carregar a view. Se a página quebrar aqui, o erro está na view.
        // $this->load->view($this->module_name . '/empresas/index', $data);
    }

    /**
     * Exibe o formulário para vincular um novo cliente Perfex à contabilidade
     * ou para editar um vínculo existente.
     * @param string $id (Opcional) ID do vínculo na tabela contabilidade_empresas para edição.
     */
    public function manage($id = '')
    {
        if (!empty($id)) { // Modo Edição
            if (!has_permission($this->module_name, '', 'edit')) {
                access_denied($this->module_name);
            }
            $data['empresa_contabil'] = $this->Cadastro_model->get_empresa_contabil($id);
            if (!$data['empresa_contabil']) {
                set_alert('danger', _l('contabilidade102_empresa_nao_encontrada'));
                redirect(admin_url($this->module_name . '/empresas'));
            }
            $data['title'] = _l('contabilidade102_editar_vinculo_empresa_titulo');
        } else { // Modo Adição
            if (!has_permission($this->module_name, '', 'create')) {
                access_denied($this->module_name);
            }
            $data['title'] = _l('contabilidade102_vincular_nova_empresa_titulo');
        }

        // Busca clientes do Perfex
        $perfex_clients = $this->clients_model->get();
        $clientes_disponiveis = [];
        
        // Filtra clientes que já estão vinculados se estiver no modo de adição
        if (empty($id)) {
            $empresas_vinculadas = $this->Cadastro_model->get_all_empresas_vinculadas();
            $cliente_ids_vinculados = array_column($empresas_vinculadas, 'cliente_id');
            
            foreach ($perfex_clients as $client) {
                if (!in_array($client['userid'], $cliente_ids_vinculados)) {
                    $clientes_disponiveis[] = $client;
                }
            }
        } else {
            // No modo de edição, exibe todos para popular o select, que estará desabilitado
            $clientes_disponiveis = $perfex_clients;
        }
        $data['clientes_disponiveis'] = $clientes_disponiveis;
        
        // Busca contadores para o select
        $data['contadores'] = $this->Cadastro_model->get_all_contadores();

        // Carrega a view do formulário (que você renomeou para form_vincular_clientes.php)
        // Se você renomeou para 'manage.php' dentro da pasta 'empresas', o caminho estaria correto.
        // Vou usar o nome manage.php como padrão, ajuste se o seu for diferente.
        $this->load->view($this->module_name . '/empresas/manage', $data);
    }

    /**
     * Processa os dados do formulário de vínculo/atualização de empresa.
     * @param string $id (Opcional) ID do vínculo a ser atualizado.
     */
    public function processar_vinculo($id = '')
    {
        if (!has_permission($this->module_name, '', ($id == '' ? 'create' : 'edit'))) {
            access_denied($this->module_name);
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Validação
            $this->form_validation->set_rules('cliente_id', _l('contabilidade102_cliente_perfex'), 'required');
            
            if ($this->form_validation->run() !== false) {
                $result = $this->Cadastro_model->vincular_ou_atualizar_empresa($id, $data);

                if ($result) {
                    set_alert('success', _l(empty($id) ? 'contabilidade102_empresa_vinculada_sucesso' : 'contabilidade102_vinculo_atualizado_sucesso'));
                } else {
                    set_alert('danger', _l('contabilidade102_erro_processar_vinculo'));
                }
            } else {
                set_alert('danger', _l('contabilidade102_erro_validacao_formulario') . validation_errors());
            }
             redirect(admin_url($this->module_name . '/empresas'));
        }
    }

    /**
     * Remove o vínculo de uma empresa com a contabilidade.
     * @param int $id ID do vínculo na tabela contabilidade_empresas
     */
    public function remover_vinculo($id)
    {
        if (empty($id) || !has_permission($this->module_name, '', 'delete')) {
            access_denied($this->module_name);
        }

        $success = $this->Cadastro_model->remover_vinculo_empresa($id);
        if ($success) {
            set_alert('success', _l('contabilidade102_vinculo_removido_sucesso'));
        } else {
            set_alert('danger', _l('contabilidade102_erro_remover_vinculo'));
        }
        redirect(admin_url($this->module_name . '/empresas'));
    }
}
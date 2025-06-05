<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Sugestão: Renomear este controller para Empresas.php e a classe para Empresas
class Cadastro extends AdminController // Ou class Empresas extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = CONTABILIDADE102_MODULE_NAME;
        $this->load->model($this->module_name . '/cadastro_model');
        $this->load->model('clients_model'); // Model nativo Perfex
        $this->load->library('form_validation');
    }

    /**
     * Lista as empresas/clientes Perfex vinculados à contabilidade.
     */
    public function index()
    {
        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }

        $data['empresas'] = $this->cadastro_model->get_all_empresas_vinculadas();
        $data['title']    = _l('contabilidade_empresas_vinculadas_titulo_lista'); // Nova string de idioma
        $this->load->view($this->module_name . '/cadastro/index', $data);
    }

    /**
     * Exibe o formulário para vincular um novo cliente Perfex à contabilidade
     * ou para editar um vínculo existente.
     * @param string $id (Opcional) ID da empresa na tabela contabilidade_empresas para edição.
     */
    public function vincular($id = '')
    {
        if (!has_permission($this->module_name, '', ($id == '' ? 'create' : 'edit'))) {
            access_denied($this->module_name);
        }

        $data = [];
        if (!empty($id)) {
            $data['empresa_contabil'] = $this->cadastro_model->get_empresa_contabil($id);
            if (!$data['empresa_contabil']) {
                set_alert('danger', _l('contabilidade_empresa_nao_encontrada'));
                redirect(admin_url($this->module_name . '/empresas'));
            }
            $data['title'] = _l('contabilidade_editar_vinculo_empresa_titulo'); // Nova string de idioma
        } else {
            $data['title'] = _l('contabilidade_vincular_nova_empresa_titulo'); // Nova string de idioma
        }

        // Clientes Perfex que ainda não estão vinculados (para o modo de adição)
        // ou todos para seleção (e o controller/model impede duplicação se necessário)
        $perfex_clients = $this->clients_model->get();
        
        // Filtra clientes já vinculados se estiver no modo de adição de novo vínculo
        if (empty($id)) {
            $empresas_vinculadas = $this->cadastro_model->get_all_empresas_vinculadas();
            $cliente_ids_vinculados = array_column($empresas_vinculadas, 'cliente_id');
            
            $clientes_disponiveis = [];
            foreach ($perfex_clients as $client) {
                if (!in_array($client['userid'], $cliente_ids_vinculados)) {
                    $clientes_disponiveis[] = $client;
                }
            }
             $data['clientes'] = $clientes_disponiveis;
        } else {
            // No modo de edição, o cliente já está selecionado e não deve ser alterado,
            // ou se permitir alteração, tratar a lógica de desvincular o antigo e vincular o novo.
            // Por simplicidade, vamos assumir que o cliente vinculado não muda na edição do vínculo.
            // Para exibir o cliente selecionado no dropdown, pode-se adicionar o cliente atual aos disponíveis ou apenas exibir o nome.
            // Aqui, estamos passando todos os clientes. A view form_vincular_cliente.php já lida com o 'selected'.
            $data['clientes'] = $perfex_clients;
        }


        $data['contadores'] = $this->cadastro_model->get_all_contadores(); // Para o select de contador

        $this->load->view($this->module_name . '/cadastro/form_vincular_cliente', $data);
    }

    /**
     * Processa os dados do formulário de vínculo/atualização de empresa.
     */
    public function processar_vinculo()
    {
        if (!has_permission($this->module_name, '', 'create') && !has_permission($this->module_name, '', 'edit')) {
            access_denied($this->module_name);
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $id_empresa_contabil = isset($data['empresa_contabil_id']) ? $data['empresa_contabil_id'] : null;

            // Regras de Validação
            $this->form_validation->set_rules('cliente_id', _l('contabilidade_cliente_perfex'), 'required');
            // Adicione mais regras conforme necessário para outros campos
            // Ex: $this->form_validation->set_rules('regime_tributario', _l('contabilidade_regime_tributario'), 'max_length[100]');

            if ($this->form_validation->run() !== false) {
                // Verificar se já existe um vínculo para este cliente_id se for uma nova adição
                if (empty($id_empresa_contabil)) { // Apenas para novos vínculos
                    $empresa_existente = $this->cadastro_model->get_empresa_by_cliente_id($data['cliente_id']);
                    if ($empresa_existente) {
                        set_alert('warning', _l('contabilidade_cliente_ja_vinculado'));
                        // Redirecionar de volta para o formulário, talvez com os dados preenchidos
                        // Para simplificar, redirecionamos para a lista.
                        redirect(admin_url($this->module_name . '/empresas/vincular'));
                        return;
                    }
                }

                $result = $this->cadastro_model->vincular_ou_atualizar_empresa($data);

                if ($result) {
                    set_alert('success', _l(empty($id_empresa_contabil) ? 'contabilidade_empresa_vinculada_sucesso' : 'contabilidade_vinculo_atualizado_sucesso'));
                } else {
                    set_alert('danger', _l('contabilidade_erro_processar_vinculo'));
                }
            } else {
                // Falha na validação
                set_alert('danger', _l('contabilidade_erro_validacao_formulario') . validation_errors());
                // Poderia redirecionar para o formulário com os erros, mas Perfex geralmente redireciona para a lista ou dashboard.
                // Para uma melhor UX, redirecionar de volta ao formulário seria bom,
                // mas requer carregar os dados novamente.
                // redirect(admin_url($this->module_name . '/empresas/vincular/' . $id_empresa_contabil));
                // Por ora, redireciona para a lista:
                 redirect(admin_url($this->module_name . '/empresas'));
                 return; // Para garantir que o script pare aqui
            }
             redirect(admin_url($this->module_name . '/empresas'));
        }
    }

    /**
     * Remove o vínculo de uma empresa com a contabilidade.
     * @param int $id ID da empresa na tabela contabilidade_empresas
     */
    public function remover_vinculo($id)
    {
        if (empty($id)) {
            redirect(admin_url($this->module_name . '/empresas'));
        }
        if (!has_permission($this->module_name, '', 'delete')) {
            access_denied($this->module_name);
        }

        $empresa = $this->cadastro_model->get_empresa_contabil($id);
        if (!$empresa) {
            set_alert('danger', _l('contabilidade_empresa_nao_encontrada'));
            redirect(admin_url($this->module_name . '/empresas'));
        }

        $success = $this->cadastro_model->remover_vinculo_empresa($id);
        if ($success) {
            set_alert('success', _l('contabilidade_vinculo_removido_sucesso'));
        } else {
            set_alert('danger', _l('contabilidade_erro_remover_vinculo'));
        }
        redirect(admin_url($this->module_name . '/empresas'));
    }

    /*
     * O arquivo original `views/cadastro/form_empresa.php`
     * que permitia cadastrar CNPJ e Razão Social diretamente não está sendo usado
     * neste fluxo de "vincular cliente Perfex". Se essa funcionalidade for desejada,
     * um novo método no controller e um model associado seriam necessários para
     * criar um novo cliente no Perfex E depois vinculá-lo, ou criar uma "empresa"
     * apenas no módulo de contabilidade sem vínculo direto com tblclients.
     * Por ora, este controller foca em gerenciar os VÍNCULOS com clientes Perfex existentes.
     */
}
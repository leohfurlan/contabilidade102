<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PlanoContas extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = CONTABILIDADE102_MODULE_NAME;

        if (!has_permission($this->module_name, '', 'view')) { // Permissão geral para ver o módulo
            access_denied($this->module_name);
        }
        $this->load->model($this->module_name . '/planocontas_model');
        $this->load->library('form_validation');
    }

    /**
     * Lista todas as contas do Plano de Contas.
     */
    public function index()
    {
        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }

        // Chama o método do model que retorna a estrutura de árvore
        $data['contas_tree'] = $this->planocontas_model->get_all_contas_hierarchical();
        $data['title']       = _l('contabilidade102_plano_de_contas_titulo');
        
        $this->load->view($this->module_name . '/planocontas/index', $data);
    }

    /**
     * Gerencia a adição ou edição de uma conta.
     * @param string $id (Opcional) ID da conta para edição.
     */
    public function manage($id = '')
    {
        if ($this->input->post()) {
            if (!has_permission($this->module_name, '', ($id == '' ? 'create' : 'edit'))) {
                access_denied($this->module_name);
            }

            $data = $this->input->post();
            
            // Regras de Validação
            // Validação para código único será feita no model, mas podemos adicionar aqui também
            $original_codigo = '';
            if (!empty($id)) {
                $conta_original = $this->planocontas_model->get_conta($id);
                if ($conta_original) {
                    $original_codigo = $conta_original->codigo;
                }
            }
            $is_unique_rule = ($data['codigo'] != $original_codigo) ? '|is_unique[' . db_prefix() . 'contabilidade_planocontas.codigo]' : '';

            $this->form_validation->set_rules('codigo', _l('contabilidade102_conta_codigo'), 'required|trim|max_length[50]' . $is_unique_rule);
            $this->form_validation->set_rules('nome', _l('contabilidade102_conta_nome'), 'required|trim|max_length[255]');
            $this->form_validation->set_rules('tipo', _l('contabilidade102_conta_tipo'), 'required');
            $this->form_validation->set_rules('natureza', _l('contabilidade102_conta_natureza'), 'required');
            $this->form_validation->set_rules('permite_lancamentos', _l('contabilidade102_conta_permite_lancamentos'), 'required|in_list[0,1]');
            $this->form_validation->set_rules('conta_pai_id', _l('contabilidade102_conta_pai'), 'trim|numeric');

            if ($this->form_validation->run() !== false) {
                $data_conta = [
                    'codigo' => $data['codigo'],
                    'nome' => $data['nome'],
                    'tipo' => $data['tipo'],
                    'natureza' => $data['natureza'],
                    'conta_pai_id' => !empty($data['conta_pai_id']) ? $data['conta_pai_id'] : null,
                    'permite_lancamentos' => $data['permite_lancamentos'],
                    'obrigatorio_centro_custo' => isset($data['obrigatorio_centro_custo']) ? 1 : 0,
                    'ativo' => isset($data['ativo']) ? 1 : 0,
                ];

                if (empty($id)) { // Adicionar Nova Conta
                    $new_id = $this->planocontas_model->add_conta($data_conta);
                    if ($new_id) {
                        set_alert('success', _l('contabilidade102_conta_adicionada_sucesso'));
                        redirect(admin_url($this->module_name . '/planocontas/manage/' . $new_id));
                    } else {
                        set_alert('danger', _l('contabilidade102_erro_adicionar_conta_codigo_existente'));
                    }
                } else { // Editar Conta Existente
                    $success = $this->planocontas_model->update_conta($id, $data_conta);
                    if ($success) {
                        set_alert('success', _l('contabilidade102_conta_atualizada_sucesso'));
                    } else {
                        set_alert('danger', _l('contabilidade102_erro_atualizar_conta_codigo_existente'));
                    }
                }
                // Redireciona para a edição da conta (seja nova ou existente) ou para a lista
                redirect(admin_url($this->module_name . '/planocontas/manage/' . ($id ? $id : ($new_id ?? ''))));

            } else {
                 // Erro de validação, preenche $data['conta'] com os dados do POST para repopular o formulário
                $data['conta'] = (object)$this->input->post();
                set_alert('danger', _l('contabilidade102_erro_validacao_formulario') . validation_errors());
            }
        }

        // Preparar dados para exibir o formulário (GET request ou falha de validação no POST)
        if (empty($id)) {
            if (!has_permission($this->module_name, '', 'create')) {
                access_denied($this->module_name);
            }
            $data['title'] = _l('contabilidade102_adicionar_nova_conta');
            if (!isset($data['conta'])) { // Se não for um POST com erro de validação
                // Valores padrão para nova conta
                $data['conta'] = (object)[
                    'codigo' => '', 'nome' => '', 'tipo' => '', 'natureza' => '',
                    'conta_pai_id' => null, 'permite_lancamentos' => 1,
                    'obrigatorio_centro_custo' => 0, 'ativo' => 1
                ];
            }
        } else {
            if (!has_permission($this->module_name, '', 'edit')) {
                access_denied($this->module_name);
            }
            // Se não for um POST com erro de validação, busca do banco
            if (!$this->input->post()) {
                $data['conta'] = $this->planocontas_model->get_conta($id);
            } // Se for um POST com erro, $data['conta'] já foi setado acima com os dados do POST

            if (!$data['conta']) {
                set_alert('danger', _l('contabilidade102_conta_nao_encontrada'));
                redirect(admin_url($this->module_name . '/planocontas'));
            }
            $data['title'] = _l('contabilidade102_editar_conta', $data['conta']->nome);
        }

        // Contas que podem ser pai (geralmente sintéticas e não a própria conta em edição)
        $this->db->select('id, CONCAT(codigo, " - ", nome) as nome_formatado_pai');
        $this->db->where('permite_lancamentos', 0); // Apenas contas sintéticas como pai
        if (!empty($id)) {
            $this->db->where('id !=', $id); // Não pode ser pai de si mesma
            // Adicionar lógica para não permitir que uma conta seja filha de suas próprias filhas (evitar loop)
        }
        $this->db->order_by('codigo', 'ASC');
        $data['contas_pai_options'] = $this->db->get(db_prefix() . 'contabilidade_planocontas')->result_array();

        // Tipos de conta e Naturezas (definidos na tabela)
        // Poderiam ser carregados de um helper ou constantes
        $data['tipos_conta_options'] = [
            'ativo' => _l('contabilidade102_tipo_ativo'),
            'passivo' => _l('contabilidade102_tipo_passivo'),
            'patrimonio_liquido' => _l('contabilidade102_tipo_patrimonio_liquido'),
            'receita' => _l('contabilidade102_tipo_receita'),
            'despesa' => _l('contabilidade102_tipo_despesa'),
            'custo' => _l('contabilidade102_tipo_custo'),
            'conta_compensacao' => _l('contabilidade102_tipo_conta_compensacao'),
        ];
        $data['naturezas_conta_options'] = [
            'devedora' => _l('contabilidade102_natureza_devedora'),
            'credora' => _l('contabilidade102_natureza_credora'),
        ];

        // A view 'planocontas/form_conta.php' (a ser criada) terá o formulário.
        $this->load->view($this->module_name . '/planocontas/form_conta', $data);
    }


    /**
     * Exclui uma conta do plano de contas.
     * @param int $id ID da conta
     */
    public function delete($id)
    {
        if (!has_permission($this->module_name, '', 'delete')) {
            access_denied($this->module_name);
        }
        if (empty($id)) {
            redirect(admin_url($this->module_name . '/planocontas'));
        }

        $response = $this->planocontas_model->delete_conta($id);

        if ($response === true) {
            set_alert('success', _l('contabilidade102_conta_excluida_sucesso'));
        } elseif ($response === 'is_parent') {
            set_alert('warning', _l('contabilidade102_erro_excluir_conta_e_pai'));
        } elseif ($response === 'has_entries') {
            set_alert('warning', _l('contabilidade102_erro_excluir_conta_tem_lancamentos'));
        } else {
            set_alert('danger', _l('contabilidade102_erro_excluir_conta'));
        }
        redirect(admin_url($this->module_name . '/planocontas'));
    }
}
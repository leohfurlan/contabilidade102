<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lancamentos extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = CONTABILIDADE102_MODULE_NAME;

        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }
        
        $this->load->model($this->module_name . '/lancamentos_model'); // Carrega Lancamentos_model
        // Carrega Planocontas_model (arquivo Planocontas_model.php, classe Planocontas_model)
        $this->load->model($this->module_name . '/Planocontas_model'); 
        $this->load->library('form_validation');
    }

    // ... método index() ...
    public function index()
    {
        // ... (código do método index como você postou, que parece correto) ...
        $page = ($this->input->get('page')) ? $this->input->get('page') : 1;
        $limit = get_option('tables_pagination_limit'); 
        $offset = ($page - 1) * $limit;

        // Certifique-se que a instância do model aqui também usa a capitalização correta
        $data['lancamentos'] = $this->lancamentos_model->get_all_lancamentos_com_itens($limit, $offset); 
        $total_rows = $this->lancamentos_model->count_all_lancamentos(); 

        $this->load->library('pagination');
        $config['base_url'] = admin_url($this->module_name . '/lancamentos/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['use_page_numbers'] = true;
        $config['reuse_query_string'] = true; 

        $this->pagination->initialize($config);
        $data['pagination_links'] = $this->pagination->create_links();
        
        $data['title'] = _l('contabilidade102_lancamentos'); 
        $this->load->view($this->module_name . '/lancamentos/index', $data);
    }


    public function lancamento($id = '')
    {
        // ... (início do método lancamento como você postou) ...
        if (!empty($id)) { 
            if (!has_permission($this->module_name, '', 'edit')) {
                access_denied($this->module_name);
            }
            // Certifique-se que a instância do model aqui também usa a capitalização correta
            $data['lancamento'] = $this->lancamentos_model->get_lancamento_principal($id); 
            $data['itens_lancamento'] = $this->lancamentos_model->get_itens_lancamento($id); 
            if (!$data['lancamento']) {
                set_alert('danger', _l('contabilidade102_lancamento_nao_encontrado'));
                redirect(admin_url($this->module_name . '/lancamentos'));
            }
            $data['title'] = _l('contabilidade102_editar_lancamento_titulo'); 
        } else { 
            if (!has_permission($this->module_name, '', 'create')) {
                access_denied($this->module_name);
            }
            $data['title'] = _l('contabilidade102_novo_lancamento'); 
        }

        $this->form_validation->set_rules('data_lancamento', _l('contabilidade102_data_lancamento'), 'required');
        $this->form_validation->set_rules('descricao_historico', _l('contabilidade102_historico_lancamento'), 'required|min_length[5]');
        
        if ($this->input->post()) {
            // ... (lógica do POST como você postou) ...
             if ($this->form_validation->run() !== false) {
                $post_data = $this->input->post();
                
                $validacao_itens = $this->_validar_itens_lancamento($post_data['itens']);
                if (!$validacao_itens['status']) {
                    set_alert('danger', $validacao_itens['mensagem']);
                } else {
                    if (empty($id)) { 
                        // Certifique-se que a instância do model aqui também usa a capitalização correta
                        $lancamento_id = $this->lancamentos_model->add_lancamento_completo($post_data); 
                        if ($lancamento_id) {
                            set_alert('success', _l('contabilidade102_lancamento_cadastrado')); 
                            redirect(admin_url($this->module_name . '/lancamentos/lancamento/' . $lancamento_id));
                        } else {
                            set_alert('danger', _l('contabilidade102_erro_cadastrar_lancamento'));
                        }
                    } else { 
                        // Certifique-se que a instância do model aqui também usa a capitalização correta
                        $success = $this->lancamentos_model->update_lancamento_completo($id, $post_data); 
                        if ($success) {
                            set_alert('success', _l('contabilidade102_lancamento_atualizado_sucesso'));
                        } else {
                            set_alert('danger', _l('contabilidade102_erro_atualizar_lancamento'));
                        }
                    }
                    redirect(admin_url($this->module_name . '/lancamentos'));
                }
            } else {
               $data['validation_errors'] = validation_errors(); 
               set_alert('danger', _l('contabilidade102_erro_validacao_formulario_geral'));
            }
            if(isset($data['validation_errors']) && $this->input->post()){
               if(empty($id)) $data['lancamento'] = (object)$this->input->post();
            }
        }

        // ---- CORREÇÃO DA CAPITALIZAÇÃO E NOME DO MÉTODO AQUI (Linha ~132) ----
        $data['contas_analiticas'] = $this->Planocontas_model->get_contas_analiticas_para_select();

        $this->load->view($this->module_name . '/lancamentos/form_lancamento', $data);
    }

    // ... método _validar_itens_lancamento e delete_lancamento como você postou ...
    // (Dentro deles, certifique-se que as chamadas a $this->lancamentos_model também usam a capitalização correta se você tiver renomeado a classe/arquivo Lancamentos_model)
    // Pelo seu código postado, $this->lancamentos_model parece ser a forma correta para o Lancamentos_model.
    private function _validar_itens_lancamento($itens)
    {
        if (empty($itens) || !is_array($itens)) {
            return ['status' => false, 'mensagem' => _l('contabilidade102_erro_nenhum_item_lancamento')];
            }

        $total_debito = 0;
        $total_credito = 0;
        $item_count = 0;

        foreach ($itens as $key => $item) {
            $item_count++;
            // Garanta que 'valor' e 'tipo_movimento' existam antes de usá-los
            if (empty($item['plano_conta_id']) || !isset($item['valor']) || !isset($item['tipo_movimento']) || !in_array($item['tipo_movimento'], ['D', 'C'])) {
                return ['status' => false, 'mensagem' => _l('contabilidade102_erro_item_incompleto', $item_count)];
            }
            
            // O JavaScript já deve ter enviado o valor desformatado (ex: 1234.56 ou 1234)
            // A conversão de vírgula para ponto já deve ter sido feita no JavaScript antes do POST.
            // Mas uma verificação extra aqui pode ser útil.
            $valor_numerico = $item['valor'];
            if (!is_numeric($valor_numerico)) {
                $valor_numerico = (float)str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $item['valor']));
            } else {
                $valor_numerico = (float)$valor_numerico;
            }

            if (!is_numeric($valor_numerico) || $valor_numerico < 0) {
                return ['status' => false, 'mensagem' => _l('contabilidade102_erro_valor_item_invalido', $item_count)];
            }

            if ($item['tipo_movimento'] == 'D') {
                $total_debito += $valor_numerico;
            } else { // 'C'
                $total_credito += $valor_numerico;
            }
        }

        // Verifica se o lançamento tem valor total zero (soma de débitos ou créditos é zero)
        // e se há pelo menos um item. Lançamentos zerados podem ser permitidos em alguns casos,
        // mas geralmente um lançamento tem valor. Ajuste esta regra se necessário.
        if (abs($total_debito) < 0.001 && abs($total_credito) < 0.001 && $item_count > 0) {
            return ['status' => false, 'mensagem' => _l('contabilidade102_erro_lancamento_zerado')];
        }
        
        // Verifica se a soma dos débitos é igual à soma dos créditos
        if (abs($total_debito - $total_credito) > 0.001) {
            // CORREÇÃO APLICADA AQUI: Usando app_format_number()
            // app_format_number($number, $force_decimals = false)
            // Passar $force_decimals como true (ou um número de casas) garante as casas decimais.
            // Se omitido ou false, pode não mostrar .00 para inteiros.
            return ['status' => false, 'mensagem' => _l('contabilidade102_erro_debitos_creditos_nao_batem', [app_format_number($total_debito, true), app_format_number($total_credito, true)])];
        }
        
        // Validação opcional: um lançamento deve ter pelo menos um débito e um crédito (ou 2+ itens)
        // if ($item_count < 2 && ($total_debito > 0 || $total_credito > 0)) {
        //      return ['status' => false, 'mensagem' => _l('contabilidade102_erro_minimo_dois_itens')];
        // }

        return ['status' => true, 'mensagem' => ''];
    }
    
    public function delete_lancamento($id)
    {
        if (empty($id)) {
            redirect(admin_url($this->module_name . '/lancamentos'));
        }
        if (!has_permission($this->module_name, '', 'delete')) {
            access_denied($this->module_name);
        }

        $lancamento = $this->lancamentos_model->get_lancamento_principal($id);
        if (!$lancamento) {
            set_alert('danger', _l('contabilidade102_lancamento_nao_encontrado'));
        } else {
            $success = $this->lancamentos_model->delete_lancamento_completo($id); 
            if ($success) {
                set_alert('success', _l('contabilidade102_lancamento_excluido_sucesso'));
            } else {
                set_alert('danger', _l('contabilidade102_erro_excluir_lancamento'));
            }
        }
        redirect(admin_url($this->module_name . '/lancamentos'));
    }

}
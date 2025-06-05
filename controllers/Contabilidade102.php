<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contabilidade102 extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = CONTABILIDADE102_MODULE_NAME;

        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }
        
        // ---- CORREÇÃO DA CAPITALIZAÇÃO AO CARREGAR O MODEL ----
        $this->load->model($this->module_name . '/Cadastro_model'); // 'C' Maiúsculo
        
        // Helpers, se necessário
        // if (file_exists(module_dir_path($this->module_name) . 'helpers/contabilidade_helper.php')) {
        //     $this->load->helper($this->module_name . '/contabilidade');
        // }
    }

    /**
     * Dashboard principal do módulo de contabilidade
     */
    public function index()
    {
        $data = [];
        try {
            // ---- CORREÇÃO DA CAPITALIZAÇÃO AO ACESSAR O MODEL ----
            $data['total_empresas']   = $this->Cadastro_model->count_all_empresas_vinculadas();
            $data['total_socios']     = $this->Cadastro_model->count_all_socios();
            $data['total_contadores'] = $this->Cadastro_model->count_all_contadores_ativos();
            
            $data['empresas_count']   = $data['total_empresas'];
            $data['socios_count']     = $data['total_socios'];
            $data['contadores_count'] = $data['total_contadores'];

            $data['title'] = _l('contabilidade102_dashboard');
            $data['bodyclass'] = 'contabilidade-dashboard';
            
        } catch (Exception $e) {
            log_message('error', 'Erro no dashboard do módulo ' . $this->module_name . ': ' . $e->getMessage());
            
            $data['empresas_count']   = 0;
            $data['socios_count']     = 0;
            $data['contadores_count'] = 0;
            $data['error_message'] = _l('contabilidade_erro_carregar_dashboard');
        }

        $this->load->view($this->module_name . '/dashboard', $data);
    }

    /**
     * Método para Plano de Contas (se centralizado aqui, ou em controller dedicado)
     */
    public function plano_contas() 
    {
        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }
        // Lógica para carregar dados do plano de contas
        // $this->load->model($this->module_name . '/Planocontas_model'); // Carregar com 'P' e 'c' se essa foi a decisão
        $data['title'] = _l('contabilidade102_menu_plano_contas');
        // $data['planos'] = $this->Planocontas_model->get_all_contas_list();
        $data['bodyclass'] = 'contabilidade-plano-contas';
        
        // $this->load->view($this->module_name . '/plano_contas/index', $data); // Requer view
        echo "Página do Plano de Contas (a ser implementada ou acessada via /admin/contabilidade102/plano_contas)";
    }

    /**
     * Método AJAX para buscar dados dinâmicos.
     */
    public function ajax_get_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type');
        $response = ['success' => false, 'data' => [], 'message' => ''];

        try {
            switch ($type) {
                case 'empresas':
                    // ---- CORREÇÃO DA CAPITALIZAÇÃO AO ACESSAR O MODEL ----
                    $response['data'] = $this->Cadastro_model->get_all_empresas_vinculadas();
                    $response['success'] = true;
                    break;
                    
                case 'socios':
                    $empresa_id = $this->input->post('empresa_id');
                    if (empty($empresa_id)) {
                        $response['message'] = _l('contabilidade_empresa_id_obrigatorio');
                    } else {
                        // ---- CORREÇÃO DA CAPITALIZAÇÃO AO ACESSAR O MODEL ----
                        $response['data'] = $this->Cadastro_model->get_socios_by_empresa($empresa_id);
                        $response['success'] = true;
                    }
                    break;
                
                default:
                    $response['message'] = _l('contabilidade_tipo_dados_ajax_nao_encontrado');
            }
        } catch (Exception $e) {
            $response['message'] = _l('contabilidade_erro_buscar_dados_ajax') . $e->getMessage();
            log_message('error', 'Erro AJAX no módulo ' . $this->module_name . ': ' . $e->getMessage());
        }

        echo json_encode($response);
    }

    /* Métodos redundantes (cadastro(), lancamentos(), livros()) foram comentados/removidos 
     * pois essas funcionalidades são tratadas por controllers dedicados (Empresas.php, Lancamentos.php, Livros.php).
     */
}
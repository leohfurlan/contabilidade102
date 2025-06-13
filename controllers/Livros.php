<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Livros extends AdminController
{
    protected $module_name;

    public function __construct()
    {
        parent::__construct();
        $this->module_name = CONTABILIDADE102_MODULE_NAME;

        if (!has_permission($this->module_name, '', 'view')) {
            access_denied($this->module_name);
        }
        $this->load->model($this->module_name . '/livros_model');

        // ---- ALTERAÇÃO AQUI ----
        $this->load->model($this->module_name . '/Planocontas_model'); // Nome do arquivo/classe com 'c' minúsculo
        $this->load->library('cliente_context');
        $this->cliente_context->ensure_selected();      // ← NOVO
        $this->cliente = $this->cliente_context->get(); // objeto disponível em $this->cliente
    }

    // ... método index() e diario() permanecem os mesmos ...
    public function index()
    {
        $data['title'] = _l('contabilidade102_livros');
        $data['periodo_inicio'] = $this->input->get('periodo_inicio') ?: date('Y-m-01');
        $data['periodo_fim']    = $this->input->get('periodo_fim') ?: date('Y-m-t');
        
        $this->load->view($this->module_name . '/livros/index', $data);
    }

    public function diario()
    {
        $data['title'] = _l('contabilidade102_livro_diario');
        $periodo_inicio_filtro = $this->input->get('periodo_inicio') ?: date(get_current_financial_year() . '-m-01');
        $periodo_fim_filtro    = $this->input->get('periodo_fim') ?: date(get_current_financial_year() . '-m-t');
        
        $data['periodo_inicio'] = $periodo_inicio_filtro;
        $data['periodo_fim']    = $periodo_fim_filtro;
        
        $data['lancamentos_diario'] = $this->livros_model->get_diario_formatado(
            $periodo_inicio_filtro,
            $periodo_fim_filtro
        );

        $this->load->view($this->module_name . '/livros/diario_view', $data);
    }


    public function razao()
    {
        $data['title'] = _l('contabilidade_livro_razao_titulo');

        $periodo_inicio_filtro = $this->input->get('periodo_inicio') ?: date(get_current_financial_year() . '-m-01');
        $periodo_fim_filtro    = $this->input->get('periodo_fim') ?: date(get_current_financial_year() . '-m-t');
        $conta_id_filtro       = $this->input->get('conta_id');

        $data['periodo_inicio'] = $periodo_inicio_filtro;
        $data['periodo_fim']    = $periodo_fim_filtro;
        $data['conta_id']       = $conta_id_filtro;

        // ---- ALTERAÇÃO AQUI ----
        $data['contas_analiticas'] = $this->Planocontas_model->get_contas_analiticas_para_select();
        
        if (!empty($data['conta_id'])) {
            $saldo_anterior_info = $this->livros_model->get_saldo_anterior_razao(
                $data['conta_id'],
                $periodo_inicio_filtro
            );
            $data['saldo_anterior_razao'] = $saldo_anterior_info;
            // ---- ALTERAÇÃO AQUI ----
            $data['conta_selecionada'] = $saldo_anterior_info['conta_info'] ?? $this->Planocontas_model->get_conta($data['conta_id']);


            $data['movimentacao_razao'] = $this->livros_model->get_movimentacao_razao(
                $data['conta_id'],
                $periodo_inicio_filtro,
                $periodo_fim_filtro
            );
        } else {
            $data['movimentacao_razao'] = [];
            $data['saldo_anterior_razao'] = ['valor' => 0, 'natureza_short' => '-', 'conta_info' => null];
            $data['conta_selecionada'] = null;
        }
        
        $this->load->view($this->module_name . '/livros/razao_view', $data);
    }
}
<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Livros_model extends App_Model
{
    private $table_lancamentos;
    private $table_lancamentos_itens;
    private $table_plano_contas;
    // private $table_empresas; // Se for filtrar por empresa e precisar de join

    public function __construct()
    {
        parent::__construct();
        $this->table_lancamentos       = db_prefix() . 'contabilidade_lancamentos';
        $this->table_lancamentos_itens = db_prefix() . 'contabilidade_lancamentos_itens';
        $this->table_plano_contas      = db_prefix() . 'contabilidade_plano_contas';
        // $this->table_empresas          = db_prefix() . 'contabilidade_empresas';
    }

    /**
     * Busca dados para o Livro Diário, formatados e agrupados.
     *
     * @param string $data_inicio Data de início (Y-m-d)
     * @param string $data_fim Data de fim (Y-m-d)
     * @param int|null $empresa_id (Opcional) ID da empresa para filtrar (se aplicável)
     * @return array Dados formatados para o Livro Diário
     */
    public function get_diario_formatado($data_inicio, $data_fim, $empresa_id = null)
    {
        $this->db->select([
            "{$this->table_lancamentos}.id as lancamento_id",
            "{$this->table_lancamentos}.data_lancamento",
            "{$this->table_lancamentos}.descricao_historico as historico_principal",
            "{$this->table_lancamentos_itens}.id as item_id",
            "{$this->table_lancamentos_itens}.plano_conta_id",
            "{$this->table_lancamentos_itens}.tipo_movimento",
            "{$this->table_lancamentos_itens}.valor",
            "{$this->table_lancamentos_itens}.historico_complementar",
            "{$this->table_plano_contas}.codigo as plano_conta_codigo",
            "{$this->table_plano_contas}.nome as plano_conta_nome",
        ]);
        $this->db->from($this->table_lancamentos_itens);
        $this->db->join($this->table_lancamentos, "{$this->table_lancamentos}.id = {$this->table_lancamentos_itens}.lancamento_id");
        $this->db->join($this->table_plano_contas, "{$this->table_plano_contas}.id = {$this->table_lancamentos_itens}.plano_conta_id");

        $this->db->where("{$this->table_lancamentos}.data_lancamento >=", to_sql_date($data_inicio));
        $this->db->where("{$this->table_lancamentos}.data_lancamento <=", to_sql_date($data_fim));
        
        $this->db->where("{$this->table_lancamentos}.status !=", 'rascunho');

        // if ($empresa_id) {
        //     // Adicionar condição de filtro por empresa_id se os lançamentos tiverem essa associação
        //     $this->db->where("{$this->table_lancamentos}.empresa_id", $empresa_id);
        // }

        $this->db->order_by("{$this->table_lancamentos}.data_lancamento", 'ASC');
        $this->db->order_by("{$this->table_lancamentos}.id", 'ASC'); // Agrupar pelo ID do lançamento
        $this->db->order_by("{$this->table_lancamentos_itens}.tipo_movimento", 'DESC'); // Débitos (D) antes de Créditos (C)
        $this->db->order_by("{$this->table_lancamentos_itens}.id", 'ASC');


        $query_result = $this->db->get()->result_array();

        // Formatar para a view (agrupar por dia, depois por lançamento)
        $diario_formatado = [];
        foreach ($query_result as $row) {
            $data_lanc = $row['data_lancamento'];
            $lanc_id = $row['lancamento_id'];

            if (!isset($diario_formatado[$data_lanc])) {
                $diario_formatado[$data_lanc] = [];
            }
            if (!isset($diario_formatado[$data_lanc][$lanc_id])) {
                $diario_formatado[$data_lanc][$lanc_id] = [
                    'data_lancamento' => $row['data_lancamento'],
                    'descricao_historico' => $row['historico_principal'],
                    'total_debito' => 0,
                    'total_credito' => 0,
                    'itens' => [],
                ];
            }
            $diario_formatado[$data_lanc][$lanc_id]['itens'][] = $row;
            if($row['tipo_movimento'] == 'D'){
                $diario_formatado[$data_lanc][$lanc_id]['total_debito'] += $row['valor'];
            } else if ($row['tipo_movimento'] == 'C'){
                 $diario_formatado[$data_lanc][$lanc_id]['total_credito'] += $row['valor'];
            }
        }
        return $diario_formatado;
    }

    /**
     * Busca informações básicas de uma conta.
     * @param int $conta_id
     * @return object|null
     */
    private function _get_info_conta($conta_id) {
        $this->db->select('id, codigo, nome, natureza'); // 'natureza' deve ser 'devedora' ou 'credora'
        $this->db->where('id', $conta_id);
        $conta_info = $this->db->get($this->table_plano_contas)->row();

        if ($conta_info) {
            $conta_info->natureza_short = ($conta_info->natureza == 'devedora' ? 'D' : 'C');
        }
        return $conta_info;
    }


    /**
     * Calcula o saldo anterior para o Livro Razão.
     * @param int $conta_id ID da conta no plano de contas
     * @param string $data_inicio_periodo Data de início do período do Razão (Y-m-d)
     * @param int|null $empresa_id (Opcional) ID da empresa
     * @return array ['valor' => (float), 'natureza_short' => 'D'/'C'/'S'] (S para Sem Saldo)
     */
    public function get_saldo_anterior_razao($conta_id, $data_inicio_periodo, $empresa_id = null)
    {
        $conta_info = $this->_get_info_conta($conta_id);
        if (!$conta_info) {
            return ['valor' => 0, 'natureza_short' => '-']; // Conta não encontrada
        }

        $this->db->select_sum("CASE WHEN {$this->table_lancamentos_itens}.tipo_movimento = 'D' THEN {$this->table_lancamentos_itens}.valor ELSE 0 END", 'total_debito_anterior');
        $this->db->select_sum("CASE WHEN {$this->table_lancamentos_itens}.tipo_movimento = 'C' THEN {$this->table_lancamentos_itens}.valor ELSE 0 END", 'total_credito_anterior');
        $this->db->from($this->table_lancamentos_itens);
        $this->db->join($this->table_lancamentos, "{$this->table_lancamentos}.id = {$this->table_lancamentos_itens}.lancamento_id");
        
        $this->db->where("{$this->table_lancamentos_itens}.plano_conta_id", $conta_id);
        $this->db->where("{$this->table_lancamentos}.data_lancamento <", $data_inicio_periodo);
        $this->db->where("{$this->table_lancamentos}.status !=", 'rascunho');

        // if ($empresa_id) {
        //     $this->db->where("{$this->table_lancamentos}.empresa_id", $empresa_id);
        // }

        $result = $this->db->get()->row();
        $total_debito = (float) ($result->total_debito_anterior ?? 0);
        $total_credito = (float) ($result->total_credito_anterior ?? 0);

        $saldo_valor = 0;
        if ($conta_info->natureza == 'devedora') {
            $saldo_valor = $total_debito - $total_credito;
        } else { // credora
            $saldo_valor = $total_credito - $total_debito;
        }
        
        $natureza_short = '-';
        if (abs($saldo_valor) > 0.001) { // Se há saldo
             $natureza_short = ($saldo_valor > 0 ? $conta_info->natureza_short : ($conta_info->natureza_short == 'D' ? 'C' : 'D'));
        }


        return ['valor' => $saldo_valor, 'natureza_short' => $natureza_short, 'conta_info' => $conta_info];
    }

    /**
     * Busca a movimentação para o Livro Razão de uma conta específica.
     * @param int $conta_id
     * @param string $data_inicio
     * @param string $data_fim
     * @param int|null $empresa_id
     * @return array
     */
    public function get_movimentacao_razao($conta_id, $data_inicio, $data_fim, $empresa_id = null)
    {
        $this->db->select([
            "{$this->table_lancamentos}.id as lancamento_id",
            "{$this->table_lancamentos}.data_lancamento",
            "{$this->table_lancamentos}.descricao_historico as descricao_historico_lancamento", // Histórico principal do lançamento
            "{$this->table_lancamentos_itens}.tipo_movimento",
            "{$this->table_lancamentos_itens}.valor",
            "{$this->table_lancamentos_itens}.historico_complementar as historico_complementar_item", // Histórico do item
        ]);
        $this->db->from($this->table_lancamentos_itens);
        $this->db->join($this->table_lancamentos, "{$this->table_lancamentos}.id = {$this->table_lancamentos_itens}.lancamento_id");
        
        $this->db->where("{$this->table_lancamentos_itens}.plano_conta_id", $conta_id);
        $this->db->where("{$this->table_lancamentos}.data_lancamento >=", $data_inicio);
        $this->db->where("{$this->table_lancamentos}.data_lancamento <=", $data_fim);
        $this->db->where("{$this->table_lancamentos}.status !=", 'rascunho');

        // if ($empresa_id) {
        //     $this->db->where("{$this->table_lancamentos}.empresa_id", $empresa_id);
        // }

        $this->db->order_by("{$this->table_lancamentos}.data_lancamento", 'ASC');
        $this->db->order_by("{$this->table_lancamentos}.id", 'ASC'); // Para manter a ordem dentro do mesmo dia
        $this->db->order_by("{$this->table_lancamentos_itens}.id", 'ASC');


        return $this->db->get()->result_array();
    }

    /**
     * Métodos originais como get_livro_caixa, get_livro_geral, get_livro_mensal
     * foram removidos pois referenciavam tabelas inexistentes (`cdmvp_*`).
     * Estes relatórios (Caixa, etc.) devem ser construídos a partir dos lançamentos
     * em contas específicas (ex: Livro Caixa seria o Razão das contas de Caixa/Banco).
     */
}
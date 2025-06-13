<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cadastro_model extends App_Model
{
    private $table_empresas;
    private $table_socios;
    private $table_contadores;
    private $table_clients; // Tabela de clientes do Perfex

    public function __construct()
    {
        parent::__construct();
        $this->table_empresas   = db_prefix() . 'contabilidade_empresas';
        $this->table_socios     = db_prefix() . 'contabilidade_socios';
        $this->table_contadores = db_prefix() . 'contabilidade_contadores';
        $this->table_clients    = db_prefix() . 'clients';
    }

    /**
     * Busca uma empresa da contabilidade pelo ID da tabela contabilidade_empresas
     * @param int $id
     * @return object|null
     */
    public function get_empresa_contabil($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_empresas)->row();
    }
    
    /**
     * Busca uma empresa da contabilidade pelo ID do cliente Perfex (userid)
     * @param int $cliente_id
     * @return object|null
     */
    public function get_empresa_by_cliente_id($cliente_id)
    {
        $this->db->where('cliente_id', $cliente_id);
        return $this->db->get($this->table_empresas)->row();
    }

    /**
     * Lista todas as empresas vinculadas à contabilidade com dados do cliente Perfex.
     * @return array
     */
    public function get_all_empresas_vinculadas()
    {
        $this->db->select("{$this->table_empresas}.*, {$this->table_clients}.company as nome_cliente, {$this->table_clients}.vat as cnpj_cliente, {$this->table_clients}.phonenumber as telefone_cliente, {$this->table_clients}.address as endereco_cliente");
        $this->db->from($this->table_empresas);
        $this->db->join($this->table_clients, "{$this->table_clients}.userid = {$this->table_empresas}.cliente_id", 'left');
        $this->db->order_by("{$this->table_clients}.company", 'asc');
        return $this->db->get()->result_array();
    }

    /**
     * Adiciona ou atualiza o vínculo de um cliente Perfex com a contabilidade.
     * @param int|string $id ID do vínculo a ser atualizado (vazio se for novo)
     * @param array $data Dados do formulário
     * @return int|bool ID da inserção/atualização ou false em caso de falha
     */
    public function vincular_ou_atualizar_empresa($id, $data)
    {
        $empresa_data = [
            'cliente_id'            => $data['cliente_id'],
            'regime_tributario'     => isset($data['regime_tributario']) ? $data['regime_tributario'] : null,
            'inscricao_estadual'    => isset($data['inscricao_estadual']) ? $data['inscricao_estadual'] : null,
            'inscricao_municipal'   => isset($data['inscricao_municipal']) ? $data['inscricao_municipal'] : null,
            'data_inicio_atividades'=> isset($data['data_inicio_atividades']) && !empty($data['data_inicio_atividades']) ? to_sql_date($data['data_inicio_atividades']) : null,
            'contador_id'           => isset($data['contador_id']) && !empty($data['contador_id']) ? $data['contador_id'] : null,
            'ativo'                 => isset($data['ativo']) ? 1 : 0,
        ];

        if (!empty($id)) {
            // Atualiza
            $this->db->where('id', $id);
            $success = $this->db->update($this->table_empresas, $empresa_data);
            if ($success) {
                log_activity('Dados contábeis da empresa atualizados [Cliente ID: ' . $data['cliente_id'] . ', Vínculo ID: ' . $id . ']');
                return $id;
            }
        } else {
            // Insere
            $empresa_data['data_criacao'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table_empresas, $empresa_data);
            $insert_id = $this->db->insert_id();
            if ($insert_id) {
                log_activity('Nova empresa vinculada à contabilidade [Cliente ID: ' . $data['cliente_id'] . ', Vínculo ID: ' . $insert_id . ']');
                return $insert_id;
            }
        }
        return false;
    }

    /**
     * Remove o vínculo de uma empresa da contabilidade.
     * @param int $id ID da tabela contabilidade_empresas
     * @return bool
     */
    public function remover_vinculo_empresa($id)
    {
        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_empresas);
        if ($success) {
            log_activity('Vínculo de empresa com contabilidade removido [ID: ' . $id . ']');
        }
        return $success;
    }

    // --- Métodos para Sócios ---
    public function get_socios_by_empresa($empresa_contabil_id)
    {
        $this->db->where('empresa_id', $empresa_contabil_id);
        return $this->db->get($this->table_socios)->result_array();
    }

    public function add_socio($data)
    {
        $data['data_criacao'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table_socios, $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            log_activity('Novo sócio adicionado [ID: ' . $insert_id . ', Empresa Contábil ID: ' . $data['empresa_id'] . ']');
        }
        return $insert_id;
    }

    public function update_socio($id, $data)
    {
        $this->db->where('id', $id);
        $success = $this->db->update($this->table_socios, $data);
         if($success){
            log_activity('Sócio atualizado [ID: ' . $id . ']');
        }
        return $success;
    }

    public function delete_socio($id)
    {
        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_socios);
        if($success){
            log_activity('Sócio excluído [ID: ' . $id . ']');
        }
        return $success;
    }

    // --- Métodos para Contadores ---
    public function get_contador($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_contadores)->row();
    }
    
    public function get_all_contadores()
    {
        $this->db->where('ativo', 1);
        $this->db->order_by('nome_completo', 'asc');
        return $this->db->get($this->table_contadores)->result_array();
    }

    public function add_contador($data)
    {
        $data['data_criacao'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table_contadores, $data);
        $insert_id = $this->db->insert_id();
         if($insert_id){
            log_activity('Novo contador adicionado [ID: ' . $insert_id . ']');
        }
        return $insert_id;
    }

    public function update_contador($id, $data)
    {
        $this->db->where('id', $id);
        $success = $this->db->update($this->table_contadores, $data);
        if($success){
            log_activity('Contador atualizado [ID: ' . $id . ']');
        }
        return $success;
    }

    public function delete_contador($id)
    {
        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_contadores);
         if($success){
            log_activity('Contador excluído [ID: ' . $id . ']');
        }
        return $success;
    }
    
    /**
     * Retorna os tipos de empresa.
     */
    public function get_tipos_empresa()
    {
        return [
            ['id' => 'mei', 'name' => 'MEI - Microempreendedor Individual'],
            ['id' => 'ei', 'name' => 'EI - Empresário Individual'],
            ['id' => 'ltda', 'name' => 'LTDA - Sociedade Limitada'],
            ['id' => 'sa', 'name' => 'S.A. - Sociedade Anônima'],
        ];
    }
    
    /**
     * Conta o total de sócios cadastrados em todas as empresas.
     * @return int
     */
    public function count_all_socios()
    {
        return $this->db->count_all_results($this->table_socios);
    }

    /**
     * Conta o total de empresas vinculadas.
     * @return int
     */
    public function count_all_empresas_vinculadas()
    {
        return $this->db->count_all_results($this->table_empresas);
    }

    /**
     * Conta o total de contadores ativos.
     * @return int
     */
    public function count_all_contadores_ativos()
    {
        $this->db->where('ativo', 1);
        return $this->db->count_all_results($this->table_contadores);
    }
}

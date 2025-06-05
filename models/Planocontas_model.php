<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Planocontas_model extends App_Model // <--- CLASSE RENOMEADA AQUI
{
    private $table_plano_contas;

    public function __construct()
    {
        parent::__construct();
        $this->table_plano_contas = db_prefix() . 'contabilidade_plano_contas';
    }

    /**
     * Retorna uma conta pelo ID.
     * @param int $id
     * @return object|null
     */
    public function get_conta($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_plano_contas)->row();
    }

    /**
     * Retorna todas as contas analíticas (que permitem lançamentos),
     * formatadas para uso em selects.
     * @return array
     */
    public function get_contas_analiticas_para_select()
    {
        $this->db->select("id, CONCAT(codigo, ' - ', nome) as nome_formatado, tipo, natureza");
        $this->db->where('ativo', 1);
        $this->db->where('permite_lancamentos', 1);
        $this->db->order_by('codigo', 'ASC');
        $query = $this->db->get($this->table_plano_contas);
        return $query->result_array();
    }

    /**
     * Retorna todas as contas (analíticas e sintéticas) para exibição em lista.
     * @return array
     */
    public function get_all_contas_list()
    {
        $this->db->order_by('codigo', 'ASC');
        return $this->db->get($this->table_plano_contas)->result_array();
    }

    /**
     * Retorna todas as contas em uma estrutura hierárquica (árvore).
     * @param int|null $parent_id ID da conta pai para iniciar a árvore (null para contas raiz)
     * @return array Estrutura de árvore das contas
     */
    public function get_all_contas_hierarchical($parent_id = null)
    {
        $this->db->where('conta_pai_id', $parent_id);
        $this->db->order_by('codigo', 'ASC');
        $query = $this->db->get($this->table_plano_contas);
        $contas = $query->result_array();

        $tree = [];
        foreach ($contas as $conta) {
            $conta['children'] = $this->get_all_contas_hierarchical($conta['id']);
            $tree[] = $conta;
        }
        return $tree;
    }


    /**
     * Adiciona uma nova conta ao plano de contas.
     * @param array $data
     * @return int|string ID da conta inserida, string com erro, ou false.
     */
    public function add_conta($data)
    {
        // 1. Validação de Código Único
        $this->db->where('codigo', $data['codigo']);
        $exists = $this->db->get($this->table_plano_contas)->row();
        if ($exists) {
            return 'codigo_exists';
        }

        // 2. Validação da Conta Pai (se existir)
        if (!empty($data['conta_pai_id'])) {
            $conta_pai = $this->get_conta($data['conta_pai_id']);
            if (!$conta_pai) {
                return 'conta_pai_invalida';
            }
            // Conta pai deve ser sintética
            if ($conta_pai->permite_lancamentos == 1) {
                return 'conta_pai_nao_sintetica';
            }
        } else {
            $data['conta_pai_id'] = null; // Garantir NULL se vazio
        }
        
        if ($data['permite_lancamentos'] == 0 && empty($data['natureza'])) {
             // return 'natureza_obrigatoria_sintetica'; // Ajustar conforme regra de negócio
        }


        $data['data_criacao'] = date('Y-m-d H:i:s');
        $data['data_atualizacao'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table_plano_contas, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('Nova conta adicionada ao Plano de Contas [ID: ' . $insert_id . ', Código: ' . $data['codigo'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Atualiza uma conta existente.
     * @param int $id
     * @param array $data
     * @return bool|string True em sucesso, string com erro, ou false.
     */
    public function update_conta($id, $data)
    {
        $conta_atual = $this->get_conta($id);
        if (!$conta_atual) {
            return false; // Conta não existe
        }

        if (isset($data['codigo']) && $data['codigo'] != $conta_atual->codigo) {
            $this->db->where('codigo', $data['codigo']);
            $this->db->where('id !=', $id);
            $exists = $this->db->get($this->table_plano_contas)->row();
            if ($exists) {
                return 'codigo_exists';
            }
        }

        $nova_conta_pai_id = isset($data['conta_pai_id']) && !empty($data['conta_pai_id']) ? (int)$data['conta_pai_id'] : null;

        if ($nova_conta_pai_id !== null) {
            if ($nova_conta_pai_id == $id) {
                return 'circular_dependency_self'; 
            }
            $conta_pai = $this->get_conta($nova_conta_pai_id);
            if (!$conta_pai) {
                return 'conta_pai_invalida';
            }
            if ($conta_pai->permite_lancamentos == 1) {
                return 'conta_pai_nao_sintetica';
            }
            if ($this->_is_descendant($nova_conta_pai_id, $id)) {
                return 'circular_dependency_descendant';
            }
        }
        $data['conta_pai_id'] = $nova_conta_pai_id;

        if (isset($data['permite_lancamentos']) && $data['permite_lancamentos'] == 1) {
            $this->db->where('conta_pai_id', $id);
            $children_count = $this->db->count_all_results($this->table_plano_contas);
            if ($children_count > 0) {
                return 'analitica_com_filhos';
            }
        }
        if (isset($data['permite_lancamentos']) && $data['permite_lancamentos'] == 0 && $conta_atual->permite_lancamentos == 1) {
            if (isset($data['natureza']) && empty($data['natureza'])) {
                // return 'natureza_obrigatoria_sintetica'; 
            }
        }

        $data['data_atualizacao'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $success = $this->db->update($this->table_plano_contas, $data);

        if ($success) {
            log_activity('Conta do Plano de Contas atualizada [ID: ' . $id . ']');
        }
        return $success;
    }

    /**
     * Verifica se $potential_descendant_id é um descendente de $ancestor_id.
     */
    private function _is_descendant($potential_descendant_id, $ancestor_id)
    {
        $path = [];
        $current_id = $potential_descendant_id;

        while ($current_id !== null) {
            if ($current_id == $ancestor_id) {
                return true; 
            }
            $path[] = $current_id; 

            $this->db->select('conta_pai_id');
            $this->db->where('id', $current_id);
            $query = $this->db->get($this->table_plano_contas);
            $row = $query->row();

            if ($row) {
                $current_id = $row->conta_pai_id;
                if (in_array($current_id, $path)) break; 
            } else {
                break; 
            }
        }
        return false;
    }

    /**
     * Exclui uma conta.
     */
    public function delete_conta($id)
    {
        $this->db->where('conta_pai_id', $id);
        $children_count = $this->db->count_all_results($this->table_plano_contas);
        if ($children_count > 0) {
            return 'is_parent';
        }

        $table_lancamentos_itens = db_prefix() . 'contabilidade_lancamentos_itens';
        if ($this->db->table_exists($table_lancamentos_itens)) {
            $this->db->where('plano_conta_id', $id);
            $lancamentos_count = $this->db->count_all_results($table_lancamentos_itens);
            if ($lancamentos_count > 0) {
                return 'has_entries';
            }
        }

        $this->db->where('id', $id);
        $success = $this->db->delete($this->table_plano_contas);
        if ($success) {
            log_activity('Conta do Plano de Contas excluída [ID: ' . $id . ']');
        }
        return $success;
    }
}
<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lancamentos_model extends App_Model
{
    private $table_lancamentos;
    private $table_lancamentos_itens;
    private $table_plano_contas; // Para joins, se necessário obter nomes de contas

    public function __construct()
    {
        parent::__construct();
        $this->table_lancamentos         = db_prefix() . 'contabilidade_lancamentos';
        $this->table_lancamentos_itens   = db_prefix() . 'contabilidade_lancamentos_itens';
        $this->table_plano_contas        = db_prefix() . 'contabilidade_plano_contas';
    }

    /**
     * Retorna o lançamento principal pelo ID.
     * @param int $id
     * @return object|null
     */
    public function get_lancamento_principal($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_lancamentos)->row();
    }

    /**
     * Retorna os itens de um lançamento específico.
     * @param int $lancamento_id
     * @return array
     */
    public function get_itens_lancamento($lancamento_id)
    {
        $this->db->select("{$this->table_lancamentos_itens}.*, {$this->table_plano_contas}.codigo as plano_conta_codigo, {$this->table_plano_contas}.nome as plano_conta_nome");
        $this->db->from($this->table_lancamentos_itens);
        $this->db->join($this->table_plano_contas, "{$this->table_plano_contas}.id = {$this->table_lancamentos_itens}.plano_conta_id", 'left');
        $this->db->where('lancamento_id', $lancamento_id);
        $this->db->order_by("{$this->table_lancamentos_itens}.id", 'asc'); // Manter a ordem de inserção ou outra lógica
        return $this->db->get()->result_array();
    }

    /**
     * Lista todos os lançamentos com um resumo ou informações básicas para a tabela principal.
     * Para a listagem principal, pode ser mais performático não trazer todos os itens de todos os lançamentos.
     * Esta função é um exemplo, ajuste conforme a necessidade da sua view de listagem.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all_lancamentos_com_itens($limit = null, $offset = null)
    {
        $this->db->select("{$this->table_lancamentos}.id, 
                           {$this->table_lancamentos}.data_lancamento, 
                           {$this->table_lancamentos}.descricao_historico,
                           {$this->table_lancamentos}.status,
                           (SELECT SUM(li.valor) FROM {$this->table_lancamentos_itens} li WHERE li.lancamento_id = {$this->table_lancamentos}.id AND li.tipo_movimento = 'D') as total_debito,
                           (SELECT SUM(li.valor) FROM {$this->table_lancamentos_itens} li WHERE li.lancamento_id = {$this->table_lancamentos}.id AND li.tipo_movimento = 'C') as total_credito");
        $this->db->from($this->table_lancamentos);
        $this->db->order_by("{$this->table_lancamentos}.data_lancamento", 'DESC');
        $this->db->order_by("{$this->table_lancamentos}.id", 'DESC');

        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $lancamentos = $this->db->get()->result_array();

        // Opcional: buscar alguns itens para cada lançamento se a view precisar.
        // foreach ($lancamentos as $key => $lancamento) {
        //    $lancamentos[$key]['itens_preview'] = $this->get_itens_lancamento_preview($lancamento['id'], 2); // Ex: 2 primeiros itens
        // }
        return $lancamentos;
    }
    
    /**
     * Conta todos os lançamentos para paginação.
     * @return int
     */
    public function count_all_lancamentos()
    {
        return $this->db->count_all_results($this->table_lancamentos);
    }


    /**
     * Adiciona um lançamento completo (cabeçalho e itens).
     * Utiliza transações para garantir a integridade.
     * @param array $data Dados do formulário (incluindo um array 'itens')
     * @return int|bool ID do lançamento inserido ou false em caso de falha.
     */
    public function add_lancamento_completo($data)
    {
        $this->db->trans_start();

        // 1. Preparar e inserir dados do lançamento principal
        $total_debito_calculado = 0;
        $total_credito_calculado = 0;
        if (isset($data['itens']) && is_array($data['itens'])) {
            foreach ($data['itens'] as $item_calc) {
                $valor_item = (float) str_replace(',', '.', $item_calc['valor']);
                if ($item_calc['tipo_movimento'] == 'D') {
                    $total_debito_calculado += $valor_item;
                } elseif ($item_calc['tipo_movimento'] == 'C') {
                    $total_credito_calculado += $valor_item;
                }
            }
        }
         // Validação básica do balanceamento (Controller já faz, mas é bom ter aqui também)
        if (abs($total_debito_calculado - $total_credito_calculado) > 0.001 || $total_debito_calculado == 0) {
            $this->db->trans_rollback(); // ou trans_complete() irá fazer o rollback se status for false
            log_message('error', 'Tentativa de adicionar lançamento desbalanceado ou zerado. Débitos: ' . $total_debito_calculado . ' Créditos: ' . $total_credito_calculado);
            return false;
        }

        $lancamento_principal_data = [
            'data_lancamento'       => to_sql_date($data['data_lancamento']),
            'descricao_historico'   => $data['descricao_historico'],
            // 'numero_lancamento'  => $this->gerar_proximo_numero_lancamento(), // Se for automático
            'status'                => isset($data['status']) ? $data['status'] : 'rascunho', // Default 'rascunho'
            'staff_id_criacao'      => get_staff_user_id(),
            'data_criacao'          => date('Y-m-d H:i:s'),
            'data_atualizacao'      => date('Y-m-d H:i:s'),
            // 'valor_total'        => $total_debito_calculado, // Armazenar o valor total do lançamento (soma dos débitos ou créditos)
                                                            // A tabela 'contabilidade_lancamentos' tem 'valor_total'
                                                            // A tabela que criamos no hook tem 'valor_total', vamos usá-lo.
        ];
        // Adicionando valor_total (a tabela 'contabilidade_lancamentos' que criamos no hook tinha um campo 'valor_total')
        // É redundante se temos os itens, mas se a tabela tem, vamos preencher.
        // A tabela no hook de ativação era: `valor_total` decimal(15,2) NOT NULL
        // A tabela no hook de ativação *não* tinha o campo `valor_total`. A tabela `cdmvp_lancamentos` sim.
        // Vamos assumir que a tabela `contabilidade_lancamentos` criada *não* tem valor_total, pois isso vem dos itens.
        // Se a sua tabela `contabilidade_lancamentos` tiver um campo `valor_total`, descomente a linha acima.

        $this->db->insert($this->table_lancamentos, $lancamento_principal_data);
        $lancamento_id = $this->db->insert_id();

        if (!$lancamento_id) {
            $this->db->trans_rollback();
            return false;
        }

        // 2. Inserir os itens do lançamento
        if (isset($data['itens']) && is_array($data['itens'])) {
            foreach ($data['itens'] as $item_data) {
                if (empty($item_data['plano_conta_id']) || empty($item_data['valor']) || empty($item_data['tipo_movimento'])) {
                    continue; // Pular itens incompletos (idealmente validados no controller)
                }
                $item_para_inserir = [
                    'lancamento_id'          => $lancamento_id,
                    'plano_conta_id'         => $item_data['plano_conta_id'],
                    'tipo_movimento'         => $item_data['tipo_movimento'], // 'D' ou 'C'
                    'valor'                  => (float) str_replace(',', '.', $item_data['valor']),
                    'historico_complementar' => isset($item_data['historico_complementar']) ? $item_data['historico_complementar'] : null,
                ];
                $this->db->insert($this->table_lancamentos_itens, $item_para_inserir);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_activity('Falha ao adicionar lançamento contábil completo [Dados: ' . json_encode($data) . ']');
            return false;
        }

        log_activity('Novo lançamento contábil adicionado [ID: ' . $lancamento_id . ']');
        return $lancamento_id;
    }

    /**
     * Atualiza um lançamento completo (cabeçalho e itens).
     * @param int $lancamento_id ID do lançamento a ser atualizado
     * @param array $data Dados do formulário
     * @return bool
     */
    public function update_lancamento_completo($lancamento_id, $data)
    {
        // Validação de balanceamento (como no add)
        $total_debito_calculado = 0;
        $total_credito_calculado = 0;
        if (isset($data['itens']) && is_array($data['itens'])) {
            foreach ($data['itens'] as $item_calc) {
                 $valor_item = (float) str_replace(',', '.', $item_calc['valor']);
                if ($item_calc['tipo_movimento'] == 'D') {
                    $total_debito_calculado += $valor_item;
                } elseif ($item_calc['tipo_movimento'] == 'C') {
                    $total_credito_calculado += $valor_item;
                }
            }
        }
        if (abs($total_debito_calculado - $total_credito_calculado) > 0.001 || $total_debito_calculado == 0) {
            log_message('error', 'Tentativa de atualizar lançamento desbalanceado ou zerado. Lançamento ID: ' . $lancamento_id);
            return false;
        }

        $this->db->trans_start();

        // 1. Atualizar dados do lançamento principal
        $lancamento_principal_data = [
            'data_lancamento'     => to_sql_date($data['data_lancamento']),
            'descricao_historico' => $data['descricao_historico'],
            'status'              => isset($data['status']) ? $data['status'] : 'rascunho',
            'staff_id_atualizacao'=> get_staff_user_id(),
            'data_atualizacao'    => date('Y-m-d H:i:s'),
            // 'valor_total'        => $total_debito_calculado, // Se a tabela principal tiver valor_total
        ];
        $this->db->where('id', $lancamento_id);
        $this->db->update($this->table_lancamentos, $lancamento_principal_data);

        // 2. Remover itens antigos
        $this->db->where('lancamento_id', $lancamento_id);
        $this->db->delete($this->table_lancamentos_itens);

        // 3. Inserir os novos itens do lançamento
        if (isset($data['itens']) && is_array($data['itens'])) {
            foreach ($data['itens'] as $item_data) {
                 if (empty($item_data['plano_conta_id']) || empty($item_data['valor']) || empty($item_data['tipo_movimento'])) {
                    continue;
                }
                $item_para_inserir = [
                    'lancamento_id'          => $lancamento_id,
                    'plano_conta_id'         => $item_data['plano_conta_id'],
                    'tipo_movimento'         => $item_data['tipo_movimento'],
                    'valor'                  => (float) str_replace(',', '.', $item_data['valor']),
                    'historico_complementar' => isset($item_data['historico_complementar']) ? $item_data['historico_complementar'] : null,
                ];
                $this->db->insert($this->table_lancamentos_itens, $item_para_inserir);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_activity('Falha ao atualizar lançamento contábil completo [ID: ' . $lancamento_id . ']');
            return false;
        }
        
        log_activity('Lançamento contábil atualizado [ID: ' . $lancamento_id . ']');
        return true;
    }

    /**
     * Exclui um lançamento contábil e seus itens.
     * A FK com ON DELETE CASCADE na tabela de itens deve cuidar da exclusão dos itens.
     * @param int $lancamento_id
     * @return bool
     */
    public function delete_lancamento_completo($lancamento_id)
    {
        // Adicionar verificações aqui: lançamento já conciliado? Período fechado?
        // $lancamento = $this->get_lancamento_principal($lancamento_id);
        // if ($lancamento && $lancamento->status == 'conciliado_ou_fechado') {
        //     return false; // Ou lançar uma exceção/mensagem de erro
        // }

        $this->db->trans_start();
        
        // Se ON DELETE CASCADE estiver configurado corretamente na FK da tabela 'contabilidade_lancamentos_itens'
        // referenciando 'contabilidade_lancamentos.id', a exclusão dos itens será automática.
        // Caso contrário, excluir os itens primeiro:
        // $this->db->where('lancamento_id', $lancamento_id);
        // $this->db->delete($this->table_lancamentos_itens);

        $this->db->where('id', $lancamento_id);
        $this->db->delete($this->table_lancamentos);
        
        $affected_rows = $this->db->affected_rows();

        $this->db->trans_complete();

        if ($this->db->trans_status() === false || $affected_rows == 0) {
             log_activity('Falha ao excluir lançamento contábil [ID: ' . $lancamento_id . '] ou lançamento não encontrado.');
            return false;
        }
        
        log_activity('Lançamento contábil excluído [ID: ' . $lancamento_id . ']');
        return true;
    }

    /**
     * O método original `add($data)` provavelmente se referia
     * apenas à tabela `cdmvp_lancamentos` que era uma estrutura mais simples.
     * Ele foi substituído por `add_lancamento_completo`.
     * O método original `get_all()` também foi substituído por
     * `get_all_lancamentos_com_itens()` e `count_all_lancamentos()`.
     */
}
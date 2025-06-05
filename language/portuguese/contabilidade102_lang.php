<?php
defined('BASEPATH') or exit('No direct script access allowed');

// ===============================================================
// ITENS DE MENU E TÍTULOS PRINCIPAIS
// ===============================================================
$lang['contabilidade102_module_name']                  = 'Contabilidade';
$lang['contabilidade102_menu_main']                    = 'Contabilidade';
$lang['contabilidade102_dashboard']                    = 'Dashboard Contábil';
$lang['contabilidade102_menu_cadastros']               = 'Cadastros';
$lang['contabilidade102_menu_lancamentos']             = 'Lançamentos';
$lang['contabilidade102_menu_livros']                  = 'Livros/Relatórios';
$lang['contabilidade102_menu_plano_contas']            = 'Plano de Contas';
$lang['contabilidade102_menu_empresas']                = 'Empresas Vinculadas';


// ===============================================================
// DASHBOARD
// ===============================================================
$lang['contabilidade102_empresas']                     = 'Empresas Atendidas';
$lang['contabilidade102_socios']                       = 'Sócios Cadastrados';
$lang['contabilidade102_contadores']                   = 'Contadores Responsáveis';
$lang['contabilidade102_erro_carregar_dashboard']      = 'Erro ao carregar dados do dashboard.';


// ===============================================================
// CADASTRO DE EMPRESAS
// ===============================================================
$lang['contabilidade102_empresas_vinculadas_titulo_lista'] = 'Empresas Vinculadas à Contabilidade';
$lang['contabilidade102_vincular_nova_empresa_titulo']    = 'Vincular Nova Empresa à Contabilidade';
$lang['contabilidade102_editar_vinculo_empresa_titulo']  = 'Editar Vínculo da Empresa com a Contabilidade';
$lang['contabilidade102_vincular_novo_cliente']          = 'Vincular Novo Cliente à Contabilidade';
$lang['contabilidade102_empresa_nao_encontrada']        = 'Empresa ou vínculo não encontrado.';
$lang['contabilidade102_cliente_perfex']               = 'Cliente (Perfex CRM)';
$lang['contabilidade102_regime_tributario']            = 'Regime Tributário';
$lang['contabilidade102_inscricao_estadual']           = 'Inscrição Estadual';
$lang['contabilidade_102_inscricao_municipal']         = 'Inscrição Municipal'; // Mantido sem o mvp, mas o ideal seria padronizar
$lang['contabilidade102_data_inicio_atividades']       = 'Data de Início das Atividades';
$lang['contabilidade102_contador_responsavel']        = 'Contador Responsável';
$lang['contabilidade102_empresa_ativa']                = 'Vínculo Ativo';
$lang['contabilidade102_selecione_cliente_perfex']     = 'Selecione o Cliente do Perfex CRM';
$lang['contabilidade102_empresa_vinculada_sucesso']    = 'Empresa vinculada à contabilidade com sucesso!';
$lang['contabilidade102_vinculo_atualizado_sucesso']    = 'Vínculo da empresa atualizado com sucesso!';
$lang['contabilidade102_erro_processar_vinculo']       = 'Ocorreu um erro ao processar o vínculo da empresa.';
$lang['contabilidade102_vinculo_removido_sucesso']      = 'Vínculo da empresa com a contabilidade removido com sucesso!';
$lang['contabilidade102_erro_remover_vinculo']         = 'Ocorreu um erro ao remover o vínculo da empresa.';
$lang['contabilidade102_cliente_ja_vinculado']         = 'Este cliente do Perfex CRM já está vinculado à contabilidade.';
$lang['contabilidade102_empresa_lista_id_vinculo']      = 'ID Vínculo';
$lang['contabilidade102_empresa_lista_cliente_perfex']  = 'Cliente (Perfex)';
$lang['contabilidade102_empresa_lista_cnpj']            = 'CNPJ';
$lang['contabilidade102_empresa_lista_telefone']        = 'Telefone';
$lang['contabilidade102_empresa_lista_regime_trib']     = 'Regime Tributário';
$lang['contabilidade102_remover_vinculo_btn']           = 'Remover Vínculo';
$lang['contabilidade102_nenhuma_empresa_vinculada_lista'] = 'Nenhuma empresa/cliente vinculado à contabilidade.';


// ===============================================================
// PLANO DE CONTAS
// ===============================================================
$lang['contabilidade102_plano_de_contas_titulo']     = 'Plano de Contas';
$lang['contabilidade102_adicionar_nova_conta']      = 'Adicionar Nova Conta';
$lang['contabilidade102_editar_conta']             = 'Editar Conta: %s';
$lang['contabilidade102_conta_codigo']             = 'Código da Conta';
$lang['contabilidade102_conta_nome']               = 'Nome da Conta';
$lang['contabilidade102_conta_tipo']               = 'Tipo';
$lang['contabilidade102_conta_natureza']           = 'Natureza';
$lang['contabilidade102_conta_pai']                = 'Conta Pai (Sintética)';
$lang['contabilidade102_conta_permite_lancamentos'] = 'Permite Lançamentos (Analítica)';
$lang['contabilidade102_conta_obrigatorio_centro_custo'] = 'Obrigatório Centro de Custo';
$lang['contabilidade102_conta_ativa']              = 'Ativa';
$lang['contabilidade102_conta_adicionada_sucesso'] = 'Conta adicionada com sucesso!';
$lang['contabilidade102_erro_adicionar_conta_codigo_existente'] = 'Erro ao adicionar conta: O código da conta já existe ou ocorreu outro problema.';
$lang['contabilidade102_conta_atualizada_sucesso'] = 'Conta atualizada com sucesso!';
$lang['contabilidade102_erro_atualizar_conta_codigo_existente'] = 'Erro ao atualizar conta: O código da conta já existe para outra conta ou ocorreu outro problema.';
$lang['contabilidade102_conta_nao_encontrada']      = 'Conta não encontrada.';
$lang['contabilidade102_conta_excluida_sucesso']    = 'Conta excluída com sucesso!';
$lang['contabilidade102_erro_excluir_conta']       = 'Erro ao excluir a conta.';
$lang['contabilidade102_erro_excluir_conta_e_pai'] = 'Não é possível excluir a conta: ela é uma conta pai para outras contas.';
$lang['contabilidade102_erro_excluir_conta_tem_lancamentos'] = 'Não é possível excluir a conta: ela possui lançamentos contábeis associados.';
$lang['contabilidade102_tipo_ativo']               = 'Ativo';
$lang['contabilidade102_tipo_passivo']             = 'Passivo';
$lang['contabilidade102_tipo_patrimonio_liquido']  = 'Patrimônio Líquido';
$lang['contabilidade102_tipo_receita']             = 'Receita';
$lang['contabilidade102_tipo_despesa']             = 'Despesa';
$lang['contabilidade102_tipo_custo']               = 'Custo';
$lang['contabilidade102_tipo_conta_compensacao']   = 'Conta de Compensação';
$lang['contabilidade102_natureza_devedora']        = 'Devedora';
$lang['contabilidade102_natureza_credora']         = 'Credora';
$lang['contabilidade102_conta_permite_lancamentos_short'] = 'Analítica';
$lang['contabilidade102_status_ativo']             = 'Status';
$lang['contabilidade102_ativo_status']             = 'Ativa';
$lang['contabilidade102_inativo_status']           = 'Inativa';
$lang['contabilidade102_nenhuma_conta_cadastrada'] = 'Nenhuma conta cadastrada no Plano de Contas.';
$lang['contabilidade102_no_parent_account']        = 'Nenhuma (Conta Principal / Raiz)';
$lang['contabilidade102_analitica_short_label']    = 'Analítica';
$lang['contabilidade102_sintetica_short_label']    = 'Sintética';


// ===============================================================
// LANÇAMENTOS
// ===============================================================
$lang['contabilidade102_novo_lancamento']                  = 'Novo Lançamento';
$lang['contabilidade102_lancamento_cadastrado']            = 'Lançamento cadastrado!';
$lang['contabilidade102_editar_lancamento_titulo']         = 'Editar Lançamento Contábil';
$lang['contabilidade102_data_lancamento']                  = 'Data do Lançamento';
$lang['contabilidade102_historico_lancamento']             = 'Histórico do Lançamento';
$lang['contabilidade102_erro_cadastrar_lancamento']        = 'Erro ao cadastrar o lançamento.';
$lang['contabilidade102_lancamento_atualizado_sucesso']    = 'Lançamento atualizado com sucesso!';
$lang['contabilidade102_erro_atualizar_lancamento']        = 'Erro ao atualizar o lançamento.';
$lang['contabilidade102_erro_nenhum_item_lancamento']      = 'Nenhum item (débito/crédito) fornecido para o lançamento.';
$lang['contabilidade102_erro_item_incompleto']             = 'O item %s do lançamento está incompleto (Conta, Valor ou Tipo de Movimento).';
$lang['contabilidade102_erro_valor_item_invalido']         = 'O valor do item %s é inválido.';
$lang['contabilidade102_erro_debitos_creditos_nao_batem']  = 'A soma dos débitos (%s) não confere com a soma dos créditos (%s).';
$lang['contabilidade102_lancamento_nao_encontrado']       = 'Lançamento contábil não encontrado.';
$lang['contabilidade102_lancamento_excluido_sucesso']      = 'Lançamento contábil excluído com sucesso!';
$lang['contabilidade102_erro_excluir_lancamento']          = 'Erro ao excluir o lançamento contábil.';
$lang['contabilidade102_erro_lancamento_zerado']           = 'O lançamento não pode ter valor total zerado.';
$lang['contabilidade102_itens_do_lancamento']              = 'Itens do Lançamento (Partidas)';
$lang['contabilidade102_item_conta_contabil']              = 'Conta Contábil';
$lang['contabilidade102_item_tipo_movimento']              = 'Tipo';
$lang['contabilidade102_item_valor']                       = 'Valor';
$lang['contabilidade102_item_historico_complementar']      = 'Histórico Complementar';
$lang['contabilidade102_debito_short']                     = 'Débito';
$lang['contabilidade102_credito_short']                    = 'Crédito';
$lang['contabilidade102_adicionar_item_lancamento']       = 'Adicionar Item';
$lang['contabilidade102_total_debito']                     = 'Total Débito';
$lang['contabilidade102_total_credito']                    = 'Total Crédito';
$lang['contabilidade102_diferenca']                       = 'Diferença (D-C)';
$lang['contabilidade102_pelo_menos_um_item_necessario']    = 'Pelo menos um item é necessário no lançamento.';
$lang['contabilidade102_lanc_lista_id']                    = 'Nº Lanç.';
$lang['contabilidade102_lanc_lista_data']                  = 'Data';
$lang['contabilidade102_lanc_lista_historico']             = 'Histórico Principal';
$lang['contabilidade102_lanc_lista_valor']                 = 'Valor (R$)';
$lang['contabilidade102_lanc_lista_status']                = 'Status';
$lang['contabilidade102_nenhum_lancamento_encontrado']     = 'Nenhum lançamento contábil encontrado.';


// ===============================================================
// LIVROS E RELATÓRIOS
// ===============================================================
$lang['contabilidade102_livro_diario']                     = 'Livro Diário';
$lang['contabilidade102_periodo_inicio']                   = 'Período Início';
$lang['contabilidade102_periodo_fim']                      = 'Período Fim';
$lang['contabilidade102_selecione_relatorio_desejado']     = 'Selecione o Relatório Desejado';
$lang['contabilidade102_diario_data']                      = 'Data';
$lang['contabilidade102_diario_lancamento_id']             = 'Lançamento Nº';
$lang['contabilidade102_diario_conta_codigo']              = 'Código Conta';
$lang['contabilidade102_diario_conta_nome']                = 'Nome da Conta';
$lang['contabilidade102_diario_historico']                 = 'Histórico';
$lang['contabilidade102_diario_debito']                    = 'Débito';
$lang['contabilidade102_diario_credito']                   = 'Crédito';
$lang['contabilidade102_diario_dia_header']                = 'Dia: %s';
$lang['contabilidade102_nenhum_lancamento_periodo']        = 'Nenhum lançamento encontrado para o período selecionado.';
$lang['contabilidade102_diario_lancamento_desbalanceado']  = 'Atenção: Lançamento %s está desbalanceado.';
$lang['contabilidade102_livro_razao_titulo']               = 'Livro Razão';
$lang['contabilidade102_razao_data']                       = 'Data';
$lang['contabilidade102_razao_historico']                  = 'Histórico';
$lang['contabilidade102_razao_debito']                     = 'Débito';
$lang['contabilidade102_razao_credito']                    = 'Crédito';
$lang['contabilidade102_razao_saldo']                      = 'Saldo';
$lang['contabilidade102_razao_valor_saldo']                = 'Valor';
$lang['contabilidade102_razao_natureza_saldo']             = 'D/C';
$lang['contabilidade102_razao_saldo_anterior']             = 'SALDO ANTERIOR';
$lang['contabilidade102_razao_saldo_final']                = 'SALDO FINAL';
$lang['contabilidade102_razao_conta_selecionada']          = 'Conta: %s - %s';
$lang['contabilidade102_razao_selecione_conta_para_exibir'] = 'Por favor, selecione uma conta contábil e o período para exibir o Livro Razão.';


// ===============================================================
// GERAL / MENSAGENS DE ERRO
// ===============================================================
$lang['contabilidade102_erro_validacao_formulario']       = 'Por favor, corrija os erros no formulário: ';
$lang['contabilidade102_empresa_id_obrigatorio']          = 'ID da empresa é obrigatório.';
$lang['contabilidade102_tipo_dados_ajax_nao_encontrado']  = 'Tipo de dados solicitado não encontrado.';
$lang['contabilidade102_erro_buscar_dados_ajax']          = 'Erro ao buscar dados: ';

?>
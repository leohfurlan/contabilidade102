<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold">
                            <?= $title; // Título: Livro Razão ?>
                            <?php if(isset($conta_selecionada) && $conta_selecionada): ?>
                                <br /><small><?= _l('contabilidade_razao_conta_selecionada', [$conta_selecionada->codigo, $conta_selecionada->nome]); ?></small>
                            <?php endif; ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                            <a href="javascript:history.back()" class="btn btn-default mright5">
                                <i class="fa fa-arrow-left"></i> <?= _l('go_back'); ?>
                            </a>
                        <?php // Formulário de filtros para o Livro Razão ?>
                        <?= form_open(admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/razao'), ['method' => 'get', 'id' => 'form-filtro-razao']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                    $p_inicio_val = $this->input->get('periodo_inicio') ? $this->input->get('periodo_inicio') : $periodo_inicio;
                                    echo render_date_input('periodo_inicio', _l('contabilidade_periodo_inicio'), _d($p_inicio_val));
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                    $p_fim_val = $this->input->get('periodo_fim') ? $this->input->get('periodo_fim') : $periodo_fim;
                                    echo render_date_input('periodo_fim', _l('contabilidade_periodo_fim'), _d($p_fim_val));
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                // $conta_id já vem do controller com o valor do GET ou nulo
                                echo render_select(
                                    'conta_id',
                                    $contas_analiticas, // Array de contas passado pelo controller
                                    ['id', 'nome_formatado'], // 'id' é o valor, 'nome_formatado' é o texto da opção
                                    _l('contabilidade_item_conta_contabil'),
                                    $conta_id, // Valor selecionado
                                    ['data-live-search' => 'true', 'required' => true], // Atributos do select
                                    [], // Atributos do form-group
                                    '', // Classe CSS adicional para o form-group
                                    'selectpicker-conta-razao' // Classe CSS adicional para o select
                                );
                                ?>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary" style="margin-top: 25px;"><?= _l('filter'); ?></button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                        <hr />

                        <?php if(isset($conta_id) && !empty($conta_id) && isset($conta_selecionada) && $conta_selecionada): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="vertical-align: middle; text-align: center;"><?= _l('contabilidade_razao_data'); ?></th>
                                        <th rowspan="2" style="vertical-align: middle;"><?= _l('contabilidade_razao_historico'); ?></th>
                                        <th rowspan="2" style="vertical-align: middle;" class="text-right"><?= _l('contabilidade_razao_debito'); ?></th>
                                        <th rowspan="2" style="vertical-align: middle;" class="text-right"><?= _l('contabilidade_razao_credito'); ?></th>
                                        <th colspan="2" class="text-center"><?= _l('contabilidade_razao_saldo'); ?></th>
                                    </tr>
                                    <tr>
                                        <th class="text-right"><?= _l('contabilidade_razao_valor_saldo'); ?></th>
                                        <th class="text-center" width="5%"><?= _l('contabilidade_razao_natureza_saldo'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-right bold"><?= _l('contabilidade_razao_saldo_anterior'); ?></td>
                                        <td class="text-right bold"></td> <?php // Coluna Débito vazia para saldo anterior ?>
                                        <td class="text-right bold"></td> <?php // Coluna Crédito vazia para saldo anterior ?>
                                        <td class="text-right bold"><?= app_format_money(abs($saldo_anterior_razao['valor']), ''); ?></td>
                                        <td class="text-center bold"><?= $saldo_anterior_razao['natureza_short']; // Ex: "D" ou "C" ou "-" ?></td>
                                    </tr>

                                    <?php
                                    $saldo_atual_valor = $saldo_anterior_razao['valor']; // Valor numérico do saldo (pode ser negativo se a natureza for oposta ao esperado)
                                    
                                    if (isset($movimentacao_razao) && !empty($movimentacao_razao)) :
                                        foreach ($movimentacao_razao as $mov) :
                                            $valor_debito_mov = ($mov['tipo_movimento'] == 'D' ? (float)$mov['valor'] : 0);
                                            $valor_credito_mov = ($mov['tipo_movimento'] == 'C' ? (float)$mov['valor'] : 0);
                                            
                                            // Atualiza o saldo_atual_valor.
                                            // Se a natureza da conta principal ($conta_selecionada->natureza) for 'devedora', débitos aumentam o saldo e créditos diminuem.
                                            // Se a natureza for 'credora', créditos aumentam o saldo e débitos diminuem.
                                            if ($conta_selecionada->natureza == 'devedora') {
                                                $saldo_atual_valor += $valor_debito_mov - $valor_credito_mov;
                                            } else { // credora
                                                $saldo_atual_valor += $valor_credito_mov - $valor_debito_mov;
                                            }

                                            $natureza_saldo_atual_display = '-';
                                            if (abs($saldo_atual_valor) > 0.001) { // Se tem saldo (não é zero)
                                                if ($saldo_atual_valor > 0) { // Saldo positivo, mantém a natureza da conta
                                                    $natureza_saldo_atual_display = $conta_selecionada->natureza_short;
                                                } else { // Saldo negativo, inverte a natureza da conta
                                                    $natureza_saldo_atual_display = ($conta_selecionada->natureza_short == 'D' ? 'C' : 'D');
                                                }
                                            }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= _d($mov['data_lancamento']); ?></td>
                                        <td><?= htmlspecialchars($mov['descricao_historico_lancamento'] . ($mov['historico_complementar_item'] ? ' - ' . $mov['historico_complementar_item'] : '')); ?></td>
                                        <td class="text-right"><?= ($valor_debito_mov > 0 ? app_format_money($valor_debito_mov, '') : ''); ?></td>
                                        <td class="text-right"><?= ($valor_credito_mov > 0 ? app_format_money($valor_credito_mov, '') : ''); ?></td>
                                        <td class="text-right"><?= app_format_money(abs($saldo_atual_valor), ''); ?></td>
                                        <td class="text-center"><?= $natureza_saldo_atual_display; ?></td>
                                    </tr>
                                    <?php
                                        endforeach;
                                    else :
                                        // Nenhuma movimentação no período, apenas saldo anterior e final (que será igual ao anterior)
                                        // A linha de saldo final abaixo já cobre isso.
                                    endif;
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <td colspan="4" class="text-right bold"><?= _l('contabilidade_razao_saldo_final'); ?></td>
                                        <td class="text-right bold"><?= app_format_money(abs($saldo_atual_valor), ''); ?></td>
                                        <td class="text-center bold">
                                            <?php
                                                $natureza_final_display = '-';
                                                if (abs($saldo_atual_valor) > 0.001) {
                                                   if ($saldo_atual_valor > 0) {
                                                       $natureza_final_display = $conta_selecionada->natureza_short;
                                                   } else {
                                                       $natureza_final_display = ($conta_selecionada->natureza_short == 'D' ? 'C' : 'D');
                                                   }
                                                }
                                                echo $natureza_final_display;
                                            ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-info"><?= _l('contabilidade_razao_selecione_conta_para_exibir'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    // Inicializa o selectpicker para o filtro de conta
    $('.selectpicker-conta-razao').selectpicker('refresh');
});
</script>
</body>
</html>
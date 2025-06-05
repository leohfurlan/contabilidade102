<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold">
                            <?= $title; // Título: Livro Diário ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php // Formulário de filtros para o Livro Diário ?>
                        <?= form_open(admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/diario'), ['method' => 'get', 'id' => 'form-filtro-diario']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                    // $periodo_inicio é passado pelo controller
                                    echo render_date_input('periodo_inicio', _l('contabilidade_periodo_inicio'), _d($periodo_inicio));
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                    // $periodo_fim é passado pelo controller
                                    echo render_date_input('periodo_fim', _l('contabilidade_periodo_fim'), _d($periodo_fim));
                                ?>
                            </div>
                            <?php
                            // Se houver filtro por empresa, adicionar aqui
                            // if(isset($empresas_vinculadas)) { ... }
                            ?>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary" style="margin-top: 25px;"><?= _l('filter'); ?></button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                        <hr />

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered"> <?php // Removido dt-table se a formatação for manual e complexa ?>
                                <thead>
                                    <tr>
                                        <th><?= _l('contabilidade_diario_data'); ?></th>
                                        <th><?= _l('contabilidade_diario_lancamento_id'); ?></th>
                                        <th><?= _l('contabilidade_diario_conta_codigo'); ?></th>
                                        <th><?= _l('contabilidade_diario_conta_nome'); ?></th>
                                        <th><?= _l('contabilidade_diario_historico'); ?></th>
                                        <th class="text-right"><?= _l('contabilidade_diario_debito'); ?></th>
                                        <th class="text-right"><?= _l('contabilidade_diario_credito'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_debito_periodo = 0;
                                    $total_credito_periodo = 0;
                                    if (isset($lancamentos_diario) && !empty($lancamentos_diario)) :
                                        foreach ($lancamentos_diario as $data_lanc => $lancamentos_do_dia) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="bold" style="background-color: #f0f0f0;"><?= _l('contabilidade_diario_dia_header', _d($data_lanc)); ?></td>
                                        </tr>
                                        <?php foreach ($lancamentos_do_dia as $lanc_id => $lancamento_info) : ?>
                                            <?php foreach ($lancamento_info['itens'] as $item_idx => $item) :
                                                if ($item['tipo_movimento'] == 'D') $total_debito_periodo += (float)$item['valor'];
                                                if ($item['tipo_movimento'] == 'C') $total_credito_periodo += (float)$item['valor'];
                                            ?>
                                                <tr>
                                                    <?php // Exibe data e ID do lançamento apenas na primeira linha do lançamento ?>
                                                    <td><?= ($item_idx == 0) ? _d($lancamento_info['data_lancamento']) : ''; ?></td>
                                                    <td><?= ($item_idx == 0) ? $lanc_id : ''; ?></td>
                                                    <td><?= htmlspecialchars($item['plano_conta_codigo']); ?></td>
                                                    <td><?= htmlspecialchars($item['plano_conta_nome']); ?></td>
                                                    <td>
                                                        <?php
                                                            if ($item_idx == 0) {
                                                                echo htmlspecialchars($lancamento_info['descricao_historico']);
                                                                if (!empty($item['historico_complementar'])) {
                                                                    echo ' <small><em>(' . htmlspecialchars($item['historico_complementar']) . ')</em></small>';
                                                                }
                                                            } elseif (!empty($item['historico_complementar'])) {
                                                                echo '<small><em>' . htmlspecialchars($item['historico_complementar']) . '</em></small>';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="text-right"><?= ($item['tipo_movimento'] == 'D' ? app_format_money($item['valor'], '') : ''); ?></td>
                                                    <td class="text-right"><?= ($item['tipo_movimento'] == 'C' ? app_format_money($item['valor'], '') : ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php // Validação visual se o lançamento individual está balanceado
                                                if (abs($lancamento_info['total_debito'] - $lancamento_info['total_credito']) > 0.001) : ?>
                                                <tr><td colspan="7" class="text-danger text-center"><?= _l('contabilidade_diario_lancamento_desbalanceado', $lanc_id)?></td></tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php
                                        endforeach;
                                    else :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?= _l('contabilidade_nenhum_lancamento_periodo'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <td colspan="5" class="text-right bold"><?= _l('totals'); ?></td>
                                        <td class="text-right bold"><?= app_format_money($total_debito_periodo, ''); ?></td>
                                        <td class="text-right bold"><?= app_format_money($total_credito_periodo, ''); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
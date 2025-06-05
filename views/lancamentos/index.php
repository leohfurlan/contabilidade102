<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission(CONTABILIDADE102_MODULE_NAME, '', 'create')) : // Usando a constante para a permissão também é uma boa prática ?>
                            
                            <?php // ---- CORREÇÃO AQUI ---- ?>
                            <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos/lancamento'); ?>" class="btn btn-info pull-left display-block">
                                <?= _l('contabilidade102_novo_lancamento'); ?>
                            </a>
                            <?php // ---- FIM DA CORREÇÃO ---- ?>

                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />

                        <h4 class="no-margin bold"><?= $title; ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php if (count($lancamentos) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-lancamentos">
                                <thead>
                                    <tr>
                                        <th><?= _l('contabilidade_lanc_lista_id'); ?></th>
                                        <th><?= _l('contabilidade_lanc_lista_data'); ?></th>
                                        <th><?= _l('contabilidade_lanc_lista_historico'); ?></th>
                                        <th class="text-right"><?= _l('contabilidade_lanc_lista_valor'); ?></th>
                                        <th><?= _l('contabilidade_lanc_lista_status'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($lancamentos as $lancamento): ?>
                                        <?php
                                            $valor_lancamento = $lancamento['total_debito'];
                                        ?>
                                        <tr>
                                            <td><?= $lancamento['id']; ?></td>
                                            <td><?= _d($lancamento['data_lancamento']); ?></td>
                                            <td><?= $lancamento['descricao_historico']; ?></td>
                                            <td class="text-right"><?= app_format_money($valor_lancamento, ''); ?></td>
                                            <td>
                                                <?= htmlspecialchars($lancamento['status']); ?>
                                            </td>
                                            <td>
                                                <?php // ---- CORREÇÃO AQUI TAMBÉM ---- ?>
                                                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos/lancamento/' . $lancamento['id']); ?>" class="btn btn-default btn-icon" title="<?= _l('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                                <?php if (has_permission(CONTABILIDADE102_MODULE_NAME, '', 'delete')) : ?>
                                                <?php // ---- CORREÇÃO AQUI TAMBÉM ---- ?>
                                                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos/delete_lancamento/' . $lancamento['id']); ?>" class="btn btn-danger btn-icon _delete" title="<?= _l('delete'); ?>"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-wrapper">
                            <?= $pagination_links ?? ''; ?>
                        </div>
                        <?php else : ?>
                            <p class="no-margin"><?= _l('contabilidade_nenhum_lancamento_encontrado'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
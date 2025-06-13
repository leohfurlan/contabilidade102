<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('contabilidade102', '', 'create')) : ?>
                            <a href="javascript:history.back()" class="btn btn-default mright5">
                                <i class="fa fa-arrow-left"></i> <?= _l('go_back'); ?>
                            </a>
                            <a href="<?= admin_url('contabilidade102/empresas/manage'); ?>" class="btn btn-info pull-left display-block">
                                <?= _l('contabilidade102_vincular_novo_cliente'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />

                        <h4 class="no-margin bold"><?= $title; ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php if (isset($empresas) && count($empresas) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped dt-table">
                                <thead>
                                    <tr>
                                        <th><?= _l('contabilidade102_empresa_lista_id_vinculo'); ?></th>
                                        <th><?= _l('contabilidade102_empresa_lista_cliente_perfex'); ?></th>
                                        <th><?= _l('contabilidade102_empresa_lista_cnpj'); ?></th>
                                        <th><?= _l('contabilidade102_empresa_lista_telefone'); ?></th>
                                        <th><?= _l('contabilidade102_empresa_lista_regime_trib'); ?></th>
                                        <th><?= _l('contabilidade_status_ativo'); ?></th>
                                        <th><?= _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($empresas as $e): ?>
                                        <tr>
                                            <td><?= $e['id']; // ID da tabela contabilidade_empresas ?></td>
                                            <td>
                                                <a href="<?= admin_url('clients/client/' . $e['cliente_id']); ?>" target="_blank">
                                                    <?= htmlspecialchars($e['nome_cliente']); // Alias definido no Cadastro_model ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($e['cnpj_cliente']); // Alias do campo 'vat' ?></td>
                                            <td><?= htmlspecialchars($e['telefone_cliente']); // Alias do campo 'phonenumber' ?></td>
                                            <td><?= !empty($e['regime_tributario']) ? htmlspecialchars($e['regime_tributario']) : '-'; ?></td>
                                            <td>
                                                <span class="label label-<?= ($e['ativo'] == 1 ? 'success' : 'danger'); ?>">
                                                    <?= ($e['ativo'] == 1 ? _l('contabilidade102_ativo_status') : _l('contabilidade102_inativo_status')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= admin_url('contabilidade102/empresas/manage/' . $e['id']); ?>" class="btn btn-default btn-icon" title="<?= _l('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                                <?php if (has_permission('contabilidade102', '', 'delete')) : ?>
                                                <a href="<?= admin_url('contabilidade102/empresas/remover_vinculo/' . $e['id']); ?>" class="btn btn-danger btn-icon _delete" title="<?= _l('contabilidade102_remover_vinculo_btn'); ?>"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else : ?>
                            <p class="no-margin"><?= _l('contabilidade102_nenhuma_empresa_vinculada_lista'); ?></p>
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
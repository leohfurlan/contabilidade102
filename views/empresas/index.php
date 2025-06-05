<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission($this->module_name, '', 'create')) : // Ou uma permissão específica para 'vincular_empresa' ?>
                            <a href="<?= admin_url($this->module_name . '/empresas/vincular'); ?>" class="btn btn-info pull-left display-block">
                                <?= _l('contabilidade_vincular_novo_cliente'); // Nova string de idioma ?>
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
                                        <th><?= _l('contabilidade_empresa_lista_id_vinculo'); ?></th>
                                        <th><?= _l('contabilidade_empresa_lista_cliente_perfex'); ?></th>
                                        <th><?= _l('contabilidade_empresa_lista_cnpj'); ?></th>
                                        <th><?= _l('contabilidade_empresa_lista_telefone'); ?></th>
                                        <th><?= _l('contabilidade_empresa_lista_regime_trib'); ?></th>
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
                                                    <?= $e['nome_cliente']; // Alias definido no Cadastro_model ?>
                                                </a>
                                            </td>
                                            <td><?= $e['cnpj_cliente']; // Alias definido no Cadastro_model (originalmente 'vat') ?></td>
                                            <td><?= $e['telefone_cliente']; // Alias definido no Cadastro_model (originalmente 'phonenumber') ?></td>
                                            <td><?= !empty($e['regime_tributario']) ? $e['regime_tributario'] : '-'; ?></td>
                                            <td>
                                                <span class="label label-<?= ($e['ativo'] == 1 ? 'success' : 'danger'); ?>">
                                                    <?= ($e['ativo'] == 1 ? _l('contabilidade_ativo_status') : _l('contabilidade_inativo_status')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= admin_url($this->module_name . '/empresas/vincular/' . $e['id']); ?>" class="btn btn-default btn-icon" title="<?= _l('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                                <?php if (has_permission($this->module_name, '', 'delete')) : // Ou permissão específica ?>
                                                <a href="<?= admin_url($this->module_name . '/empresas/remover_vinculo/' . $e['id']); ?>" class="btn btn-danger btn-icon _delete" title="<?= _l('contabilidade_remover_vinculo_btn'); ?>"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else : ?>
                            <p class="no-margin"><?= _l('contabilidade_nenhuma_empresa_vinculada_lista'); // Nova string de idioma ?></p>
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
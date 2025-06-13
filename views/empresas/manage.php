<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold"><?= $title; ?></h4>
                        <hr class="hr-panel-heading" />
                            <a href="javascript:history.back()" class="btn btn-default mright5">
                                <i class="fa fa-arrow-left"></i> <?= _l('go_back'); ?>
                            </a>

                        <?php
                            $empresa_contabil_id = isset($empresa_contabil) ? $empresa_contabil->id : '';
                            echo form_open(admin_url('contabilidade102/empresas/processar_vinculo/' . $empresa_contabil_id), ['id' => 'empresa-form']);
                            if (!empty($empresa_contabil_id)) {
                                echo form_hidden('empresa_contabil_id', $empresa_contabil_id);
                            }
                        ?>

                        <div class="form-group">
                            <label for="cliente_id" class="control-label"><?= _l('contabilidade102_selecione_cliente_perfex'); ?></label>
                            <select id="cliente_id" name="cliente_id" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" required <?= !empty($empresa_contabil_id) ? 'disabled' : '' ?>>
                                <option value=""></option>
                                <?php
                                // Se for edição, adiciona o cliente atual à lista para que ele apareça selecionado
                                $cliente_ja_listado = false;
                                if (isset($empresa_contabil) && !empty($empresa_contabil->cliente_id)) {
                                    foreach ($clientes_disponiveis as $cliente) {
                                        if ($cliente['userid'] == $empresa_contabil->cliente_id) {
                                            $cliente_ja_listado = true;
                                            break;
                                        }
                                    }
                                    if (!$cliente_ja_listado) {
                                        // Busca o cliente da edição para adicionar na lista
                                        $cliente_edicao = $this->ci->clients_model->get($empresa_contabil->cliente_id);
                                        if ($cliente_edicao) {
                                             $clientes_disponiveis[] = (array)$cliente_edicao;
                                        }
                                    }
                                }
                                
                                foreach($clientes_disponiveis as $cliente): ?>
                                    <option value="<?= $cliente['userid']; ?>" <?= (isset($empresa_contabil) && $empresa_contabil->cliente_id == $cliente['userid']) ? 'selected' : ''; ?>>
                                        <?= $cliente['company']; ?>
                                        <?php if(!empty($cliente['vat'])): ?>
                                            (<?= $cliente['vat']; ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(!empty($empresa_contabil_id)): ?>
                                <small class="text-muted"><?= _l('contabilidade_cliente_vinculado_nao_pode_alterar'); ?></small>
                            <?php endif; ?>
                        </div>

                        <?php $value = (isset($empresa_contabil) ? $empresa_contabil->regime_tributario : ''); ?>
                        <?php echo render_input('regime_tributario', 'contabilidade102_regime_tributario', $value, 'text'); ?>

                        <?php $value = (isset($empresa_contabil) ? $empresa_contabil->inscricao_estadual : ''); ?>
                        <?php echo render_input('inscricao_estadual', 'contabilidade102_inscricao_estadual', $value, 'text'); ?>

                        <?php $value = (isset($empresa_contabil) ? $empresa_contabil->inscricao_municipal : ''); ?>
                        <?php echo render_input('inscricao_municipal', 'contabilidade102_inscricao_municipal', $value, 'text'); ?>

                        <?php $value = (isset($empresa_contabil) && !empty($empresa_contabil->data_inicio_atividades) ? _d($empresa_contabil->data_inicio_atividades) : ''); ?>
                        <?php echo render_date_input('data_inicio_atividades', 'contabilidade102_data_inicio_atividades', $value); ?>

                        <?php echo render_select('contador_id', $contadores, ['id', 'nome_completo'], 'contabilidade102_contador_responsavel', isset($empresa_contabil) ? $empresa_contabil->contador_id : ''); ?>

                        <div class="checkbox checkbox-primary">
                            <?php $checked = !isset($empresa_contabil) || (isset($empresa_contabil) && $empresa_contabil->ativo == 1); ?>
                            <input type="checkbox" name="ativo" id="ativo" <?= $checked ? 'checked' : ''; ?>>
                            <label for="ativo"><?= _l('contabilidade102_empresa_ativa'); ?></label>
                        </div>

                        <hr />
                        <div class="text-right">
                            <button type="submit" class="btn btn-info"><?= _l('submit'); ?></button>
                            <a href="<?= admin_url('contabilidade102/empresas'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    _validate_form($('#empresa-form'), {
        cliente_id: 'required',
    });
});
</script>
</body>
</html>
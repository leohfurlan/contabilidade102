<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <h4 class="bold"><?= $title; ?></h4>
        <?php // O action do form_open precisa ser ajustado para o método correto no controller Cadastro.php
        // Ex: admin_url(CONTABILIDADE102_MODULE_NAME . '/cadastro/vincular_cliente_action') ?>
        <?= form_open(admin_url(CONTABILIDADE102_MODULE_NAME . '/empresas/vincular_action')); ?>
        <div class="form-group">
            <label for="cliente_id" class="control-label"><?= _l('contabilidade_selecione_cliente_perfex'); ?></label>
            <select id="cliente_id" name="cliente_id" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" required>
                <option value=""></option>
                <?php foreach($clientes as $cliente): ?>
                    <option value="<?= $cliente['userid']; ?>" <?php if(isset($empresa_contabil) && $empresa_contabil->cliente_id == $cliente['userid']) echo 'selected'; ?>>
                        <?= $cliente['company']; ?>
                        <?php if(!empty($cliente['vat'])): ?>
                            (<?= $cliente['vat']; ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php // Campos adicionais da tabela contabilidade_empresas que podem ser preenchidos/editados aqui ?>
        <div class="form-group">
            <label for="regime_tributario" class="control-label"><?= _l('contabilidade_regime_tributario'); ?></label>
            <input type="text" id="regime_tributario" name="regime_tributario" class="form-control" value="<?php if(isset($empresa_contabil)) echo $empresa_contabil->regime_tributario; ?>">
        </div>
        <div class="form-group">
            <label for="inscricao_estadual" class="control-label"><?= _l('contabilidade_inscricao_estadual'); ?></label>
            <input type="text" id="inscricao_estadual" name="inscricao_estadual" class="form-control" value="<?php if(isset($empresa_contabil)) echo $empresa_contabil->inscricao_estadual; ?>">
        </div>
        <div class="form-group">
            <label for="inscricao_municipal" class="control-label"><?= _l('contabilidade_inscricao_municipal'); ?></label>
            <input type="text" id="inscricao_municipal" name="inscricao_municipal" class="form-control" value="<?php if(isset($empresa_contabil)) echo $empresa_contabil->inscricao_municipal; ?>">
        </div>
         <div class="form-group">
            <label for="data_inicio_atividades" class="control-label"><?= _l('contabilidade_data_inicio_atividades'); ?></label>
            <?= render_date_input('data_inicio_atividades', '', (isset($empresa_contabil) ? _d($empresa_contabil->data_inicio_atividades) : _d(date('Y-m-d')))); ?>
        </div>
        <div class="form-group">
             <label for="contador_id" class="control-label"><?= _l('contabilidade_contador_responsavel'); ?></label>
            <select id="contador_id" name="contador_id" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                <option value=""></option>
                <?php /* foreach($contadores as $contador): ?>
                    <option value="<?= $contador->id; ?>" <?php if(isset($empresa_contabil) && $empresa_contabil->contador_id == $contador->id) echo 'selected'; ?>>
                        <?= $contador->nome_completo; ?> (CRC: <?= $contador->crc; ?>)
                    </option>
                <?php endforeach; */ ?>
                <?php // Você precisará carregar $contadores no controller ?>
            </select>
        </div>

        <div class="form-group">
            <?php $checked = (isset($empresa_contabil) && $empresa_contabil->ativo == 1) || !isset($empresa_contabil) ? true : false; ?>
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="ativo" id="ativo" <?= $checked ? 'checked' : ''; ?>>
                <label for="ativo"><?= _l('contabilidade_empresa_ativa'); ?></label>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-info"><?= _l('submit'); ?></button>
            <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/empresas'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
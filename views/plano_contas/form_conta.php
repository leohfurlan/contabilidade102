<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold">
                            <?= $title; // Título: Adicionar Nova Conta ou Editar Conta ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php
                            $form_action_url = admin_url('contabilidade102/plano_contas/manage');
                            if (isset($conta->id) && !empty($conta->id)) {
                                $form_action_url .= '/' . $conta->id;
                            }
                            echo form_open($form_action_url, ['id' => 'plano-conta-form']);
                        ?>
                        
                        <?php // Campo oculto para o ID da conta em modo de edição
                            if (isset($conta->id)) {
                                echo form_hidden('contaid', $conta->id);
                            }
                        ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                    $codigo_value = isset($conta->codigo) ? $conta->codigo : (isset($_POST['codigo']) ? $_POST['codigo'] : '');
                                    echo render_input('codigo', 'contabilidade102_conta_codigo', $codigo_value, 'text', ['required' => true]);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    $nome_value = isset($conta->nome) ? $conta->nome : (isset($_POST['nome']) ? $_POST['nome'] : '');
                                    echo render_input('nome', 'contabilidade102_conta_nome', $nome_value, 'text', ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo" class="control-label"><?= _l('contabilidade102_conta_tipo'); ?></label>
                                    <select name="tipo" id="tipo" class="selectpicker" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" required>
                                        <option value=""></option>
                                        <?php foreach($tipos_conta_options as $key => $value): ?>
                                            <option value="<?= $key; ?>" <?= (isset($conta->tipo) && $conta->tipo == $key) ? 'selected' : ((isset($_POST['tipo']) && $_POST['tipo'] == $key) ? 'selected' : ''); ?>><?= $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="natureza" class="control-label"><?= _l('contabilidade102_conta_natureza'); ?></label>
                                    <select name="natureza" id="natureza" class="selectpicker" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" required>
                                        <option value=""></option>
                                        <?php foreach($naturezas_conta_options as $key => $value): ?>
                                            <option value="<?= $key; ?>" <?= (isset($conta->natureza) && $conta->natureza == $key) ? 'selected' : ((isset($_POST['natureza']) && $_POST['natureza'] == $key) ? 'selected' : ''); ?>><?= $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="conta_pai_id"><?= _l('contabilidade102_conta_pai'); ?></label>
                            <select name="conta_pai_id" id="conta_pai_id" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                <option value=""><?= _l('contabilidade102_no_parent_account'); ?></option>
                                <?php foreach ($contas_pai_options as $cp_option) : ?>
                                    <?php // Não exibir a própria conta como opção de pai
                                        if (isset($conta->id) && $conta->id == $cp_option['id']) {
                                            continue;
                                        }
                                    ?>
                                    <option value="<?= $cp_option['id']; ?>" <?= (isset($conta->conta_pai_id) && $conta->conta_pai_id == $cp_option['id']) ? 'selected' : ((isset($_POST['conta_pai_id']) && $_POST['conta_pai_id'] == $cp_option['id']) ? 'selected' : ''); ?>>
                                        <?= htmlspecialchars($cp_option['nome_formatado_pai']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="permite_lancamentos" class="control-label"><?= _l('contabilidade102_conta_permite_lancamentos'); ?></label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="permite_lancamentos" id="permite_lanc_sim" value="1" 
                                    <?php
                                        if (isset($_POST['permite_lancamentos'])) {
                                            echo ($_POST['permite_lancamentos'] == 1 ? 'checked' : '');
                                        } elseif (isset($conta->permite_lancamentos)) {
                                            echo ($conta->permite_lancamentos == 1 ? 'checked' : '');
                                        } else { // Default para nova conta
                                            echo 'checked';
                                        }
                                    ?> required>
                                <label for="permite_lanc_sim"><?= _l('yes'); ?> (<?= _l('contabilidade102_analitica_short_label'); ?>)</label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="permite_lancamentos" id="permite_lanc_nao" value="0" 
                                    <?php
                                        if (isset($_POST['permite_lancamentos'])) {
                                            echo ($_POST['permite_lancamentos'] == 0 ? 'checked' : '');
                                        } elseif (isset($conta->permite_lancamentos)) {
                                            echo ($conta->permite_lancamentos == 0 ? 'checked' : '');
                                        }
                                    ?> required>
                                <label for="permite_lanc_nao"><?= _l('no'); ?> (<?= _l('contabilidade102_sintetica_short_label'); ?>)</label>
                            </div>
                        </div>

                        <div class="checkbox checkbox-primary">
                            <?php
                                $checked_obr_cc = false;
                                if (isset($_POST['obrigatorio_centro_custo'])) {
                                    $checked_obr_cc = true;
                                } elseif (isset($conta->obrigatorio_centro_custo) && $conta->obrigatorio_centro_custo == 1) {
                                    $checked_obr_cc = true;
                                }
                            ?>
                            <input type="checkbox" name="obrigatorio_centro_custo" id="obrigatorio_centro_custo" value="1" <?= $checked_obr_cc ? 'checked' : ''; ?>>
                            <label for="obrigatorio_centro_custo"><?= _l('contabilidade102_conta_obrigatorio_centro_custo'); ?></label>
                        </div>

                        <div class="checkbox checkbox-primary">
                             <?php
                                $checked_ativo = true; // Default para nova conta é ativo
                                if (isset($_POST['ativo']) && is_array($_POST) ) { // Check if form was submitted
                                    $checked_ativo = isset($_POST['ativo']);
                                } elseif (isset($conta->id)) { // Se é edição, usar o valor do banco
                                    $checked_ativo = ($conta->ativo == 1);
                                }
                            ?>
                            <input type="checkbox" name="ativo" id="ativo" value="1" <?= $checked_ativo ? 'checked' : ''; ?>>
                            <label for="ativo"><?= _l('contabilidade102_conta_ativa'); ?></label>
                        </div>

                        <hr />
                        <div class="text-right">
                            <button type="submit" class="btn btn-info" id="btn-submit-conta"><?= _l('submit'); ?></button>
                            <a href="<?= admin_url('contabilidade102/plano_contas'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
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
    _validate_form($('#plano-conta-form'), {
        codigo: 'required',
        nome: 'required',
        tipo: 'required',
        natureza: 'required',
        permite_lancamentos: 'required'
    });
});
</script>
</body>
</html>
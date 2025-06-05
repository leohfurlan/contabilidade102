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
                            // Determina a URL de action do formulário
                            $form_action = admin_url($this->module_name . '/plano_contas/manage');
                            if (isset($conta->id) && !empty($conta->id)) {
                                $form_action .= '/' . $conta->id;
                            }
                            echo form_open($form_action, ['id' => 'plano-conta-form']);
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
                                    echo render_input('codigo', 'contabilidade_conta_codigo', $codigo_value, 'text', ['required' => true]);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    $nome_value = isset($conta->nome) ? $conta->nome : (isset($_POST['nome']) ? $_POST['nome'] : '');
                                    echo render_input('nome', 'contabilidade_conta_nome', $nome_value, 'text', ['required' => true]);
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo" class="control-label"><?= _l('contabilidade_conta_tipo'); ?></label>
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
                                    <label for="natureza" class="control-label"><?= _l('contabilidade_conta_natureza'); ?></label>
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
                            <label for="conta_pai_id"><?= _l('contabilidade_conta_pai'); ?></label>
                            <select name="conta_pai_id" id="conta_pai_id" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                <option value=""><?= _l('no_parent_account'); ?></option>
                                <?php foreach ($contas_pai_options as $cp_option) : ?>
                                    <?php // Não exibir a própria conta como opção de pai
                                        if (isset($conta->id) && $conta->id == $cp_option['id']) {
                                            continue;
                                        }
                                    ?>
                                    <option value="<?= $cp_option['id']; ?>" <?= (isset($conta->conta_pai_id) && $conta->conta_pai_id == $cp_option['id']) ? 'selected' : ((isset($_POST['conta_pai_id']) && $_POST['conta_pai_id'] == $cp_option['id']) ? 'selected' : ''); ?>>
                                        <?= $cp_option['nome_formatado_pai']; // Ex: "1. ATIVO - ATIVO" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="permite_lancamentos" class="control-label"><?= _l('contabilidade_conta_permite_lancamentos'); ?></label>
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
                                <label for="permite_lanc_sim"><?= _l('yes'); ?> (<?= _l('contabilidade_analitica_short_label'); ?>)</label>
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
                                <label for="permite_lanc_nao"><?= _l('no'); ?> (<?= _l('contabilidade_sintetica_short_label'); ?>)</label>
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
                            <label for="obrigatorio_centro_custo"><?= _l('contabilidade_conta_obrigatorio_centro_custo'); ?></label>
                        </div>

                        <div class="checkbox checkbox-primary">
                             <?php
                                $checked_ativo = true; // Default para nova conta é ativo
                                if (isset($_POST['ativo'])) { // Se o form foi submetido, usar o valor do post
                                    $checked_ativo = true;
                                } elseif (isset($conta->id)) { // Se é edição, usar o valor do banco
                                    $checked_ativo = ($conta->ativo == 1);
                                }
                            ?>
                            <input type="checkbox" name="ativo" id="ativo" value="1" <?= $checked_ativo ? 'checked' : ''; ?>>
                            <label for="ativo"><?= _l('contabilidade_conta_ativa'); ?></label>
                        </div>

                        <hr />
                        <div class="text-right">
                            <button type="submit" class="btn btn-info" id="btn-submit-conta"><?= _l('submit'); ?></button>
                            <a href="<?= admin_url($this->module_name . '/plano_contas'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
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

    // Lógica condicional para o campo "Conta Pai" e "Permite Lançamentos"
    $('#permite_lancamentos_sim').on('change', function(){
        if($(this).is(':checked')){
            // Se analítica (permite lançamento), idealmente não deveria ter filhos
            // e, portanto, a seleção de "Conta Pai" pode ser menos relevante ou até desabilitada
            // para ser pai de outras contas. Mas ela PRECISA de um pai sintético.
            // Se for analítica, ela NÃO PODE SER PAI de ninguém.
            // Esta lógica é mais sobre o que ELA É, não sobre o que ela PODE TER como pai.
        }
    });
    $('#permite_lancamentos_nao').on('change', function(){
         if($(this).is(':checked')){
            // Se sintética (não permite lançamento), ela PODE SER PAI de outras contas.
            // Ela também precisa de um pai (a menos que seja raiz).
         }
    });

    // Exemplo: se "Permite Lançamentos" for "Sim" (Analítica),
    // a conta não poderá ser pai de ninguém. (Isso é validado no model ao tentar setar uma analítica como pai).
    // Se "Permite Lançamentos" for "Não" (Sintética), ela pode ser pai.
    // O campo "Conta Pai" sempre deve listar apenas contas sintéticas.
    // O controller já está passando apenas contas sintéticas em $contas_pai_options.

});
</script>
</body>
</html>
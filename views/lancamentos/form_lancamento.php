<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold">
                            <?= $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php if (isset($validation_errors) && $validation_errors): ?>
                            <div class="alert alert-danger">
                                <p><?= _l('contabilidade_erro_validacao_formulario_geral'); ?></p>
                                <?= $validation_errors; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                            $lancamento_id = isset($lancamento) ? $lancamento->id : '';
                            // CORREÇÃO 1: Usar a constante do nome do módulo
                            echo form_open(admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos/lancamento/' . $lancamento_id), ['id' => 'lancamento-form']);
                            if (!empty($lancamento_id)) {
                                echo form_hidden('lancamento_id', $lancamento_id);
                            }
                        ?>

                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                $data_lanc_value = (isset($lancamento) ? _d($lancamento->data_lancamento) : (isset($_POST['data_lancamento']) ? _d($_POST['data_lancamento']) : _d(date('Y-m-d'))));
                                echo render_date_input('data_lancamento', _l('contabilidade_data_lancamento'), $data_lanc_value, ['required' => 'required']);
                                ?>
                            </div>
                            <div class="col-md-9">
                                <?php
                                $desc_value = (isset($lancamento) ? $lancamento->descricao_historico : (isset($_POST['descricao_historico']) ? $_POST['descricao_historico'] : ''));
                                echo render_textarea('descricao_historico', _l('contabilidade_historico_lancamento'), $desc_value, ['required' => 'required', 'rows' => 3]);
                                ?>
                            </div>
                        </div>

                        <hr />
                        <h5 class="bold"><?= _l('contabilidade_itens_do_lancamento'); ?></h5>

                        <div class="table-responsive s_table">
                            <table class="table lancamento-items-table items table-main-pb-2" id="tblLancamentoItens">
                                <thead>
                                    <tr>
                                        <th width="35%"><?= _l('contabilidade_item_conta_contabil'); ?></th>
                                        <th width="15%"><?= _l('contabilidade_item_tipo_movimento'); ?></th>
                                        <th width="15%"><?= _l('contabilidade_item_valor'); ?></th>
                                        <th width="30%"><?= _l('contabilidade_item_historico_complementar'); ?></th>
                                        <th align="center"><i class="fa fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="lancamento-itens-body">
                                    <?php
                                    $i = 0;
                                    $default_item = [
                                        'plano_conta_id' => '',
                                        'tipo_movimento' => '',
                                        'valor' => '',
                                        'historico_complementar' => ''
                                    ];
                                    $items_to_render = isset($itens_lancamento) && count($itens_lancamento) > 0 ? $itens_lancamento : [ (object)$default_item ];
                                    
                                    if ($this->input->post('itens')) {
                                        $posted_items = $this->input->post('itens');
                                        $items_to_render = [];
                                        foreach($posted_items as $p_item){
                                            $items_to_render[] = (object)$p_item;
                                        }
                                    }

                                    foreach ($items_to_render as $item_key => $item) :
                                        $item_index = $i;
                                    ?>
                                    <tr class="main" data-item-index="<?= $item_index; ?>">
                                        <td>
                                            <?php
                                            echo render_select('itens[' . $item_index . '][plano_conta_id]', $contas_analiticas, ['id', 'nome_formatado'], '', (isset($item->plano_conta_id) ? $item->plano_conta_id : ''), ['data-width' => '100%', 'data-live-search' => 'true', 'class' => 'selectpicker item-plano-conta', 'required' => 'required'], [], '', 'item-plano-conta-select');
                                            ?>
                                        </td>
                                        <td>
                                            <select name="itens[<?= $item_index; ?>][tipo_movimento]" class="selectpicker item-tipo-movimento" data-width="100%" required>
                                                <option value=""></option>
                                                <option value="D" <?= (isset($item->tipo_movimento) && $item->tipo_movimento == 'D' ? 'selected' : ''); ?>><?= _l('contabilidade_debito_short'); ?></option>
                                                <option value="C" <?= (isset($item->tipo_movimento) && $item->tipo_movimento == 'C' ? 'selected' : ''); ?>><?= _l('contabilidade_credito_short'); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="itens[<?= $item_index; ?>][valor]" class="form-control item-valor" step="0.01" min="0.01" value="<?= (isset($item->valor) ? app_format_number($item->valor) : ''); ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="itens[<?= $item_index; ?>][historico_complementar]" class="form-control item-historico" value="<?= (isset($item->historico_complementar) ? $item->historico_complementar : ''); ?>">
                                        </td>
                                        <td>
                                            <?php if ($i > 0 || count($items_to_render) > 1) : ?>
                                                <button type="button" class="btn btn-danger btn-xs remove-lancamento-item" tabindex="-1"><i class="fa fa-times"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $i++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <button type="button" class="btn btn-info add-lancamento-item pull-left"><i class="fa fa-plus"></i> <?= _l('contabilidade_adicionar_item_lancamento'); ?></button>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-md-offset-6">
                                <table class="table text-right">
                                    <tbody>
                                        <tr>
                                            <td><span class="bold"><?= _l('contabilidade_total_debito'); ?> :</span></td>
                                            <td id="total_debito" class="total_debito">0.00</td>
                                        </tr>
                                        <tr>
                                            <td><span class="bold"><?= _l('contabilidade_total_credito'); ?> :</span></td>
                                            <td id="total_credito" class="total_credito">0.00</td>
                                        </tr>
                                        <tr>
                                            <td><span class="bold"><?= _l('contabilidade_diferenca'); ?> :</span></td>
                                            <td id="diferenca_lancamento" class="diferenca_lancamento">0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr />
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-info" id="btn-submit-lancamento"><?= _l('submit'); ?></button>
                                <?php // CORREÇÃO 2: Usar a constante do nome do módulo ?>
                                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
                            </div>
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
    // Função para calcular totais
    function calcularTotaisLancamento() {
        var totalDebito = 0;
        var totalCredito = 0;
        $('#tblLancamentoItens tbody tr').each(function() {
            var tipoMovimento = $(this).find('.item-tipo-movimento').val();
            var valor = parseFloat(accounting.unformat($(this).find('.item-valor').val())) || 0; // Usar accounting.unformat para tratar formatação
            
            if (tipoMovimento === 'D') {
                totalDebito += valor;
            } else if (tipoMovimento === 'C') {
                totalCredito += valor;
            }
        });

        $('#total_debito').text(accounting.formatMoney(totalDebito)); 
        $('#total_credito').text(accounting.formatMoney(totalCredito));
        
        var diferenca = totalDebito - totalCredito;
        $('#diferenca_lancamento').text(accounting.formatMoney(diferenca));

        if (Math.abs(diferenca) > 0.001) { 
            $('#diferenca_lancamento').addClass('text-danger').removeClass('text-success');
             $('#btn-submit-lancamento').prop('disabled', true); 
        } else {
            $('#diferenca_lancamento').addClass('text-success').removeClass('text-danger');
             $('#btn-submit-lancamento').prop('disabled', false); 
        }
    }

    // Adicionar novo item ao lançamento
    $('body').on('click', '.add-lancamento-item', function() {
        var $tbody = $('#lancamento-itens-body');
        var nextIndex = $tbody.find('tr').length > 0 ? (parseInt($tbody.find('tr:last').data('item-index')) + 1) : 0;
        
        var newRow = $('<tr class="main" data-item-index="' + nextIndex + '"></tr>');
        var cols = "";

        cols += '<td><select name="itens[' + nextIndex + '][plano_conta_id]" class="selectpicker item-plano-conta" data-width="100%" data-live-search="true" required>';
        <?php if(isset($contas_analiticas) && !empty($contas_analiticas)): ?>
            cols += '<option value=""></option>';
            <?php foreach($contas_analiticas as $conta_select): ?>
                cols += '<option value="<?= $conta_select['id'] ?>"><?= htmlspecialchars($conta_select['nome_formatado'], ENT_QUOTES) ?></option>';
            <?php endforeach; ?>
        <?php endif; ?>
        cols += '</select></td>';
        cols += '<td><select name="itens[' + nextIndex + '][tipo_movimento]" class="selectpicker item-tipo-movimento" data-width="100%" required><option value=""></option><option value="D"><?= _l('contabilidade_debito_short') ?></option><option value="C"><?= _l('contabilidade_credito_short') ?></option></select></td>';
        cols += '<td><input type="text" name="itens[' + nextIndex + '][valor]" class="form-control item-valor" value="0.00" required></td>'; // Alterado para text para usar com accounting.js
        cols += '<td><input type="text" name="itens[' + nextIndex + '][historico_complementar]" class="form-control item-historico"></td>';
        cols += '<td><button type="button" class="btn btn-danger btn-xs remove-lancamento-item" tabindex="-1"><i class="fa fa-times"></i></button></td>';
        
        newRow.append(cols);
        $tbody.append(newRow);
        newRow.find('.selectpicker').selectpicker('refresh'); 
        newRow.find('.item-valor').on('blur', function() { // Formatar ao perder o foco
            var value = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(value, 2, ".", ","));
        }).on('focus', function() { // Desformatar ao ganhar foco para facilitar edição
            var value = accounting.unformat($(this).val());
            $(this).val(value == 0 ? '' : value);
        });
        calcularTotaisLancamento(); 
    });

    // Remover item do lançamento
    $('body').on('click', '.remove-lancamento-item', function() {
         if ($('#tblLancamentoItens tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calcularTotaisLancamento(); 
        } else {
            $(this).closest('tr').find('input[type="text"].item-valor').val(accounting.formatNumber(0, 2, ".", ",")); // Limpar valor com formatação
            $(this).closest('tr').find('input[type="text"].item-historico').val('');
            $(this->closest('tr').find('select.item-plano-conta, select.item-tipo-movimento').selectpicker('val', '');
            alert("<?= _l('contabilidade_pelo_menos_um_item_necessario'); ?>");
        }
    });

    // Formatar e calcular totais ao mudar valor ou tipo de movimento
    $('body').on('change keyup blur', '.item-valor', function(event) {
        if (event.type === 'blur' || (event.type === 'keyup' && (event.key === 'Enter' || event.key === 'Tab'))) {
            var value = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(value, 2, ".", ","));
        }
        calcularTotaisLancamento();
    });
     $('body').on('focus', '.item-valor', function() {
        var value = accounting.unformat($(this).val());
        $(this).val(value == 0 ? '' : value); // Remove formatação para edição, mostra vazio se for 0
    });
    $('body').on('change', '.item-tipo-movimento', function() {
        calcularTotaisLancamento();
    });
    
    _validate_form($('#lancamento-form'), {
        data_lancamento: 'required',
        descricao_historico: {
            required: true,
            minlength: 5
        }
    }, function(form) {
        // Antes de submeter, garantir que os valores estão desformatados para o backend
        $('.item-valor').each(function(){
            $(this).val(accounting.unformat($(this).val()));
        });
        $('#btn-submit-lancamento').prop('disabled', true).find('i').remove();
        $('#btn-submit-lancamento').prepend('<i class="fa fa-spinner fa-pulse"></i> ');
        form.submit();
    });

    // Formatar valores iniciais e calcular totais na carga da página
    $('.item-valor').each(function() {
        var value = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(value, 2, ".", ","));
    }).trigger('blur'); // Disparar blur para formatar e recalcular
    
    // Inicializar selectpickers para itens já existentes na carga
    $('#lancamento-itens-body .selectpicker').selectpicker('refresh');
});
</script>

<style>
.item-plano-conta-select .bootstrap-select .dropdown-menu { min-width: 300px; }
</style>

</body>
</html>
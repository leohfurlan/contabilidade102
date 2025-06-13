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
                        <a href="javascript:history.back()" class="btn btn-default mright5">
                            <i class="fa fa-arrow-left"></i> <?= _l('go_back'); ?>
                        </a>

                        <?php if (isset($validation_errors) && $validation_errors): ?>
                            <div class="alert alert-danger">
                                <p><?= _l('contabilidade102_erro_validacao_formulario_geral'); ?></p>
                                <?= $validation_errors; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                            $lancamento_id = isset($lancamento) ? $lancamento->id : '';
                            // CORREÇÃO: Usar a constante do nome do módulo
                            echo form_open(admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos/lancamento/' . $lancamento_id), ['id' => 'lancamento-form']);
                            if (!empty($lancamento_id)) {
                                echo form_hidden('lancamento_id', $lancamento_id);
                            }
                        ?>

                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                $data_lanc_value = (isset($lancamento) ? _d($lancamento->data_lancamento) : (isset($_POST['data_lancamento']) ? _d($_POST['data_lancamento']) : _d(date('Y-m-d'))));
                                echo render_date_input('data_lancamento', _l('contabilidade102_data_lancamento'), $data_lanc_value, ['required' => 'required']);
                                ?>
                            </div>
                            <div class="col-md-9">
                                <?php
                                $desc_value = (isset($lancamento) ? $lancamento->descricao_historico : (isset($_POST['descricao_historico']) ? $_POST['descricao_historico'] : ''));
                                echo render_textarea('descricao_historico', _l('contabilidade102_historico_lancamento'), $desc_value, ['required' => 'required', 'rows' => 3]);
                                ?>
                            </div>
                        </div>

                        <hr />
                        <h5 class="bold"><?= _l('contabilidade102_itens_do_lancamento'); ?></h5>

                        <div class="table-responsive s_table">
                            <table class="table lancamento-items-table items table-main-pb-2" id="tblLancamentoItens">
                                <thead>
                                    <tr>
                                        <th width="35%"><?= _l('contabilidade102_item_conta_contabil'); ?></th>
                                        <th width="15%"><?= _l('contabilidade102_item_tipo_movimento'); ?></th>
                                        <th width="15%"><?= _l('contabilidade102_item_valor'); ?></th>
                                        <th width="30%"><?= _l('contabilidade102_item_historico_complementar'); ?></th>
                                        <th align="center"><i class="fa fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="lancamento-itens-body">
                                    <?php
                                    $i = 0;
                                    $default_item = ['plano_conta_id' => '', 'tipo_movimento' => '', 'valor' => '', 'historico_complementar' => ''];
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
                                            // Garante que $contas_analiticas é um array antes de usar
                                            $contas_options = isset($contas_analiticas) && is_array($contas_analiticas) ? $contas_analiticas : [];
                                            echo render_select('itens[' . $item_index . '][plano_conta_id]', $contas_options, ['id', 'nome_formatado'], '', (isset($item->plano_conta_id) ? $item->plano_conta_id : ''), ['data-width' => '100%', 'data-live-search' => 'true', 'class' => 'selectpicker item-plano-conta', 'required' => 'required'], [], '', 'item-plano-conta-select');
                                            ?>
                                        </td>
                                        <td>
                                            <select name="itens[<?= $item_index; ?>][tipo_movimento]" class="selectpicker item-tipo-movimento" data-width="100%" required>
                                                <option value=""></option>
                                                <option value="D" <?= (isset($item->tipo_movimento) && $item->tipo_movimento == 'D' ? 'selected' : ''); ?>><?= _l('contabilidade102_debito_short'); ?></option>
                                                <option value="C" <?= (isset($item->tipo_movimento) && $item->tipo_movimento == 'C' ? 'selected' : ''); ?>><?= _l('contabilidade102_credito_short'); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                             <input type="text" name="itens[<?= $item_index; ?>][valor]" class="form-control item-valor" value="<?= (isset($item->valor) ? app_format_number($item->valor) : '0,00'); ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="itens[<?= $item_index; ?>][historico_complementar]" class="form-control item-historico" value="<?= (isset($item->historico_complementar) ? htmlspecialchars($item->historico_complementar) : ''); ?>">
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
                            <button type="button" class="btn btn-info add-lancamento-item pull-left"><i class="fa fa-plus"></i> <?= _l('contabilidade102_adicionar_item_lancamento'); ?></button>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-md-offset-5">
                                <table class="table text-right">
                                    <tbody>
                                        <tr>
                                            <td><span class="bold"><?= _l('contabilidade102_total_debito'); ?> :</span></td>
                                            <td id="total_debito" class="total_debito">0,00</td>
                                        </tr>
                                        <tr>
                                            <td><span class="bold"><?= _l('contabilidade102_total_credito'); ?> :</span></td>
                                            <td id="total_credito" class="total_credito">0,00</td>
                                        </tr>
                                        <tr>
                                            <td><span class="bold text-danger"><?= _l('contabilidade102_diferenca'); ?> :</span></td>
                                            <td id="diferenca_lancamento" class="diferenca_lancamento text-success">0,00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr />
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-info" id="btn-submit-lancamento"><?= _l('submit'); ?></button>
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
// Um template para a nova linha, escondido. É mais robusto para clonagem.
var lancamentoItemTemplate = `
<tr class="main" data-item-index="__INDEX__">
    <td>
        <select name="itens[__INDEX__][plano_conta_id]" class="selectpicker item-plano-conta" data-width="100%" data-live-search="true" required>
            <option value=""></option>
            <?php if(isset($contas_analiticas) && !empty($contas_analiticas)): ?>
                <?php foreach($contas_analiticas as $conta_select): ?>
                    <option value="<?= $conta_select['id'] ?>"><?= htmlspecialchars($conta_select['nome_formatado'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </td>
    <td>
        <select name="itens[__INDEX__][tipo_movimento]" class="selectpicker item-tipo-movimento" data-width="100%" required>
            <option value=""></option>
            <option value="D"><?= _l('contabilidade102_debito_short') ?></option>
            <option value="C"><?= _l('contabilidade102_credito_short') ?></option>
        </select>
    </td>
    <td>
        <input type="text" name="itens[__INDEX__][valor]" class="form-control item-valor" value="0,00" required>
    </td>
    <td>
        <input type="text" name="itens[__INDEX__][historico_complementar]" class="form-control item-historico">
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-xs remove-lancamento-item" tabindex="-1"><i class="fa fa-times"></i></button>
    </td>
</tr>`;

$(function() {
    // Função para calcular totais
    function calcularTotaisLancamento() {
        var totalDebito = 0;
        var totalCredito = 0;
        $('#tblLancamentoItens tbody tr').each(function() {
            var tipoMovimento = $(this).find('.item-tipo-movimento').val();
            var valor = accounting.unformat($(this).find('.item-valor').val()) || 0;
            
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

    function init_item_events($row){
        $row.find('.item-valor').on('blur', function() {
            var value = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(value, 2, "<?= get_option('decimal_separator'); ?>", "<?= get_option('thousand_separator'); ?>"));
        }).on('focus', function() { 
            var value = accounting.unformat($(this).val());
            $(this).val(value == 0 ? '' : value);
        }).on('keyup', function(event) {
             calcularTotaisLancamento();
        });
        
        $row.find('.item-tipo-movimento').on('change', function() {
            calcularTotaisLancamento();
        });

        $row.find('.selectpicker').selectpicker('refresh');
    }

    // Adicionar novo item ao lançamento
    $('body').on('click', '.add-lancamento-item', function() {
        var $tbody = $('#lancamento-itens-body');
        var nextIndex = $tbody.find('tr').length > 0 ? (parseInt($tbody.find('tr:last').data('item-index')) + 1) : 0;
        
        var newRowHtml = lancamentoItemTemplate.replace(/__INDEX__/g, nextIndex);
        var $newRow = $(newRowHtml);
        
        $tbody.append($newRow);
        init_item_events($newRow);
    });

    // Remover item do lançamento
    $('body').on('click', '.remove-lancamento-item', function() {
         if ($('#tblLancamentoItens tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calcularTotaisLancamento(); 
        } else {
            alert("<?= _l('contabilidade102_pelo_menos_um_item_necessario'); ?>");
        }
    });
    
    _validate_form($('#lancamento-form'), {
        data_lancamento: 'required',
        descricao_historico: {
            required: true,
            minlength: 5
        }
    }, function(form) {
        $('.item-valor').each(function(){
            $(this).val(accounting.unformat($(this).val()));
        });
        $('#btn-submit-lancamento').prop('disabled', true).prepend('<i class="fa fa-spinner fa-pulse"></i> ');
        form.submit();
    });

    // Inicializar eventos e valores para itens já existentes na carga
    $('#lancamento-itens-body tr').each(function(){
        init_item_events($(this));
    });
    calcularTotaisLancamento();
});
</script>

<style>
.item-plano-conta-select .bootstrap-select .dropdown-menu { min-width: 300px; }
</style>

</body>
</html>
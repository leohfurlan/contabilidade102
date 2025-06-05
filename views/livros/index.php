<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold">
                            <?= $title; // Título passado pelo controller Livros::index() ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?= form_open('', ['method' => 'get', 'id' => 'form-filtros-livros']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_date_input('periodo_inicio', _l('contabilidade_periodo_inicio'), _d($periodo_inicio)); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('periodo_fim', _l('contabilidade_periodo_fim'), _d($periodo_fim)); ?>
                            </div>
                            <?php
                            // Se houver filtro por empresa, adicionar aqui:
                            // if(isset($empresas_vinculadas)) {
                            //     echo '<div class="col-md-4">';
                            //     echo render_select('empresa_id', $empresas_vinculadas, ['cliente_id', 'nome_cliente'], _l('contabilidade_empresa'), $this->input->get('empresa_id'), [], [], '', 'selectpicker-empresas');
                            //     echo '</div>';
                            // }
                            ?>
                        </div>
                        <hr />
                        <h5 class="bold"><?= _l('contabilidade_selecione_relatorio_desejado'); ?></h5>
                        <div class="row">
                            <div class="col-md-4 mtop15">
                                <button type="submit" formaction="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/diario'); ?>" class="btn btn-info btn-block"><?= _l('contabilidade102_livro_diario'); ?></button>
                            </div>
                            <div class="col-md-4 mtop15">
                                <button type="submit" formaction="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/razao'); ?>" class="btn btn-info btn-block"><?= _l('contabilidade_livro_razao_titulo'); ?></button>
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
$(function(){
    // Se tiver select de empresas, inicializar
    // $('.selectpicker-empresas').selectpicker('refresh');
});
</script>
</body>
</html>
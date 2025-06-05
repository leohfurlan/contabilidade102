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

                        <?= form_open('', ['method' => 'get', 'id' => 'form-filtros-livros']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                    $p_inicio_val = $this->input->get('periodo_inicio') ? $this->input->get('periodo_inicio') : $periodo_inicio;
                                    echo render_date_input('periodo_inicio', _l('contabilidade102_periodo_inicio'), _d($p_inicio_val));
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                    $p_fim_val = $this->input->get('periodo_fim') ? $this->input->get('periodo_fim') : $periodo_fim;
                                    echo render_date_input('periodo_fim', _l('contabilidade102_periodo_fim'), _d($p_fim_val));
                                ?>
                            </div>
                        </div>
                        <hr />
                        <h5 class="bold"><?= _l('contabilidade102_selecione_relatorio_desejado'); ?></h5>
                        <div class="row">
                            <div class="col-md-4 mtop15">
                                <button type="submit" formaction="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/diario'); ?>" class="btn btn-info btn-block"><?= _l('contabilidade102_livro_diario'); ?></button>
                            </div>
                            <div class="col-md-4 mtop15">
                                <button type="submit" formaction="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/livros/razao'); ?>" class="btn btn-info btn-block"><?= _l('contabilidade102_livro_razao_titulo'); ?></button>
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
</body>
</html>
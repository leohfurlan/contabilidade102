<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <h4 class="bold"><?= _l('contabilidade_datalisto_mvp_dashboard'); ?></h4>
        <?php if(isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade_datalisto_mvp_empresas'); ?></h5>
                        <?php // Original: <h2><?= count($empresas); ?></h2> ?>
                        <h2><?= isset($empresas_count) ? $empresas_count : 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade_datalisto_mvp_socios'); ?></h5>
                        <?php // Original: <h2><?= count($socios); ?></h2> ?>
                        <h2><?= isset($socios_count) ? $socios_count : 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade_datalisto_mvp_contadores'); ?></h5>
                        <?php // Original: <h2><?= count($contadores); ?></h2> ?>
                        <h2><?= isset($contadores_count) ? $contadores_count : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <?php // Adicionar aqui gráficos ou outros widgets do dashboard se necessário ?>
    </div>
</div>
<?php init_tail(); ?>
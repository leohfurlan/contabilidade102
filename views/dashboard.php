<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <h4 class="bold"><?= _l('contabilidade102_dashboard'); ?></h4>
        
        <?php if(isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade102_empresas'); ?></h5>
                        <h2 class="bold"><?= isset($empresas_count) ? $empresas_count : 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade102_socios'); ?></h5>
                        <h2 class="bold"><?= isset($socios_count) ? $socios_count : 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?= _l('contabilidade102_contadores'); ?></h5>
                        <h2 class="bold"><?= isset($contadores_count) ? $contadores_count : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin bold mbot15"><?= _l('contabilidade102_acoes_rapidas'); // Nova string de idioma ?></h4>
            </div>

<div class="col-md-3 col-xs-6">
                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/empresas'); ?>" class="btn btn-primary btn-block" style="padding: 20px; font-size: 16px;">
                    <i class="fa fa-building fa-2x"></i><br /><br />
                    <span><?= _l('contabilidade102_menu_empresas'); ?></span>
                </a>
            </div>

            <div class="col-md-3 col-xs-6">
                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/planocontas'); ?>" class="btn btn-info btn-block" style="padding: 20px; font-size: 16px;">
                    <i class="fa fa-sitemap fa-2x"></i><br /><br />
                    <span><?= _l('contabilidade102_menu_plano_contas'); ?></span>
                </a>
            </div>

            <div class="col-md-3 col-xs-6">
                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos'); ?>" class="btn btn-success btn-block" style="padding: 20px; font-size: 16px;">
                    <i class="fa fa-exchange fa-2x"></i><br /><br />
                    <span><?= _l('contabilidade102_menu_lancamentos'); ?></span>
                </a>
            </div>

            <div class="col-md-3 col-xs-6">
                <a href="<?= admin_url(CONTABILIDADE102_MODULE_NAME . '/livros'); ?>" class="btn btn-default btn-block" style="padding: 20px; font-size: 16px;">
                    <i class="fa fa-book fa-2x"></i><br /><br />
                    <span><?= _l('contabilidade102_menu_livros'); ?></span>
                </a>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>
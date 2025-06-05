<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <h4 class="bold"><?= $title; ?></h4>
        <?= form_open(admin_url('contabilidade102/cadastro/add_empresa')); ?>
        <div class="form-group">
            <label>CNPJ</label>
            <input type="text" name="cnpj" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Razão Social</label>
            <input type="text" name="razao_social" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Endereço</label>
            <input type="text" name="endereco" class="form-control">
        </div>
        <button type="submit" class="btn btn-info"><?= _l('submit'); ?></button>
        <?= form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>

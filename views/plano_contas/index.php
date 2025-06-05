<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Função recursiva para renderizar a árvore de contas
// Coloque esta função no início do arquivo da view ou em um helper do seu módulo
if (!function_exists('render_contas_tree_ul')) {
    function render_contas_tree_ul($contas_array, $module_name, $level = 0) {
        if (empty($contas_array)) {
            return '';
        }
        $html = '<ul class="plano-contas-tree list-unstyled">'; // Adicionar classe para estilização

        foreach ($contas_array as $conta) {
            $is_sintetica = ($conta['permite_lancamentos'] == 0);
            $is_ativa = ($conta['ativo'] == 1);

            $html .= '<li class="conta-item' . (!$is_ativa ? ' conta-inativa' : '') . ($is_sintetica ? ' conta-sintetica' : ' conta-analitica') . '" data-id="' . $conta['id'] . '">';
            
            $html .= '<div class="conta-info">'; // Container para a linha da conta
            $html .= '<span class="conta-codigo">' . htmlspecialchars($conta['codigo']) . '</span> - ';
            $html .= '<span class="conta-nome">' . htmlspecialchars($conta['nome']) . '</span>';
            
            // Badges para tipo e natureza (opcional, pode poluir)
            // $html .= ' <span class="label label-default">' . _l('contabilidade_tipo_short_' . $conta['tipo']) . '</span>';
            // $html .= ' <span class="label label-default">' . _l('contabilidade_natureza_short_' . $conta['natureza']) . '</span>';

            if (!$is_sintetica) {
                $html .= ' <span class="label label-info">' . _l('contabilidade_analitica_short_label') . '</span>';
            } else {
                 $html .= ' <span class="label label-warning">' . _l('contabilidade_sintetica_short_label') . '</span>';
            }
            if (!$is_ativa) {
                $html .= ' <span class="label label-danger">' . _l('contabilidade_inativo_status') . '</span>';
            }

            $html .= '<div class="conta-actions pull-right">'; // Ações à direita
            $html .= ' <a href="' . admin_url($module_name . '/plano_contas/manage/' . $conta['id']) . '" class="btn btn-default btn-xs" title="'._l('edit').'"><i class="fa fa-pencil"></i></a>';
            $html .= ' <a href="' . admin_url($module_name . '/plano_contas/delete/' . $conta['id']) . '" class="btn btn-danger btn-xs _delete" title="'._l('delete').'"><i class="fa fa-remove"></i></a>';
            $html .= '</div>';
            $html .= '</div>'; // Fim conta-info

            if (!empty($conta['children'])) {
                $html .= render_contas_tree_ul($conta['children'], $module_name, $level + 1);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?= admin_url($this->module_name . '/plano_contas/manage'); ?>" class="btn btn-info pull-left display-block">
                                <?= _l('contabilidade_adicionar_nova_conta'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <h4 class="no-margin bold">
                            <?= $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php if (isset($contas_tree) && count($contas_tree) > 0) : ?>
                            <div class="plano-contas-container">
                                <?php echo render_contas_tree_ul($contas_tree, $this->module_name); ?>
                            </div>
                        <?php else : ?>
                            <p class="no-margin"><?= _l('contabilidade_nenhuma_conta_cadastrada'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<style>
/* Estilização básica para a árvore do plano de contas */
.plano-contas-tree {
    padding-left: 0; /* Remove padding padrão do UL se for o nível raiz */
}
.plano-contas-tree ul {
    padding-left: 25px; /* Indentação para níveis filhos */
    border-left: 1px dashed #ccc;
    margin-left: 8px; /* Ajuste para alinhar a linha com o texto/ícone do pai */
}
.plano-contas-tree li {
    list-style-type: none;
    position: relative;
    padding: 5px 0;
}
/* Linha horizontal para conectar ao item (opcional) */
.plano-contas-tree li::before {
    /* content: "";
    position: absolute;
    top: 15px; 
    left: -15px; 
    border-bottom: 1px dashed #ccc;
    width: 10px;
    height: 0; */
}
.conta-item {
    /* border-bottom: 1px solid #f0f0f0; */ /* Linha separadora suave */
}
.conta-item:last-child {
    /* border-bottom: none; */
}
.conta-info {
    padding: 3px 5px;
    border-radius: 3px;
    display: flex; /* Para alinhar itens na linha */
    align-items: center; /* Alinha verticalmente no centro */
}
.conta-info:hover {
    background-color: #f9f9f9;
}
.conta-codigo { font-weight: bold; margin-right: 5px; }
.conta-nome { flex-grow: 1; } /* Faz o nome ocupar o espaço restante */
.conta-actions {
    margin-left: 10px;
    white-space: nowrap; /* Impede que os botões quebrem linha */
}
.conta-sintetica .conta-nome { font-weight: 500; } /* Destaca sintéticas */
.conta-analitica .conta-nome {}
.conta-inativa .conta-nome,
.conta-inativa .conta-codigo {
    color: #aaa;
    text-decoration: line-through;
}
.conta-info .label { margin-left: 5px; }
</style>

</body>
</html>
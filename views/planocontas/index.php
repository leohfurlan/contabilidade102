<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/**
 * Renderiza recursivamente a árvore do Plano de Contas
 *
 * @param array  $contas_array Nó ou sub-árvore atual
 * @param string $module_name  Nome do módulo (ex.: contabilidade102)
 * @param string $slug_pc      Slug usado nas rotas do plano de contas (ex.: planocontas)
 * @param int    $level        Nível de profundidade (opcional)
 *
 * @return string HTML <ul>
 */
if (!function_exists('render_contas_tree_ul')) {
    function render_contas_tree_ul($contas_array, $module_name, $slug_pc, $level = 0)
    {
        if (empty($contas_array)) {
            return '';
        }

        $html = '<ul class="plano-contas-tree list-unstyled">';

        foreach ($contas_array as $conta) {
            $is_sintetica = ($conta['permite_lancamentos'] == 0);
            $is_ativa     = ($conta['ativo'] == 1);

            $html .= '<li class="conta-item'
                   . (!$is_ativa     ? ' conta-inativa'   : '')
                   . ($is_sintetica ? ' conta-sintetica' : ' conta-analitica')
                   . '" data-id="' . $conta['id'] . '">';

            /* --- Cabeçalho da conta --- */
            $html .= '<div class="conta-info">';
            $html .= '<span class="conta-codigo">' . htmlspecialchars($conta['codigo']) . '</span> - ';
            $html .= '<span class="conta-nome">'   . htmlspecialchars($conta['nome'])   . '</span>';

            // Etiquetas (analítica/sintética)
            $html .= ' <span class="label ' . ($is_sintetica ? 'label-warning' : 'label-info') . '">'
                     . _l($is_sintetica ? 'contabilidade102_sintetica_short_label'
                                        : 'contabilidade102_analitica_short_label')
                     . '</span>';

            // Inativa?
            if (!$is_ativa) {
                $html .= ' <span class="label label-danger">' . _l('contabilidade102_inativo_status') . '</span>';
            }

            /* --- Botões de ação --- */
            $html .= '<div class="conta-actions pull-right">';
            $html .= '<a href="' . admin_url($module_name . '/' . $slug_pc . '/manage/' . $conta['id']) . '"'
                   . ' class="btn btn-default btn-xs" title="' . _l('edit') . '"><i class="fa fa-pencil"></i></a> ';
            $html .= '<a href="' . admin_url($module_name . '/' . $slug_pc . '/delete/' . $conta['id']) . '"'
                   . ' class="btn btn-danger btn-xs _delete" title="' . _l('delete') . '"><i class="fa fa-remove"></i></a>';
            $html .= '</div>'; // .conta-actions
            $html .= '</div>'; // .conta-info

            /* --- Filhos --- */
            if (!empty($conta['children'])) {
                $html .= render_contas_tree_ul($conta['children'], $module_name, $slug_pc, $level + 1);
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
                        <!-- Botão “Adicionar nova conta” -->
                        <div class="_buttons">
                            <a href="javascript:history.back()" class="btn btn-default mright5">
                                <i class="fa fa-arrow-left"></i> <?= _l('go_back'); ?>
                            </a>
                            <a href="<?= admin_url($module_name . '/' . $slug_pc . '/manage'); ?>"
                               class="btn btn-info pull-left display-block">
                                <?= _l('contabilidade102_adicionar_nova_conta'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <h4 class="no-margin bold"><?= $title; ?></h4>
                        <hr class="hr-panel-heading"/>

                        <?php if (!empty($contas_tree)) : ?>
                            <div class="plano-contas-container">
                                <?= render_contas_tree_ul($contas_tree, $module_name, $slug_pc); ?>
                            </div>
                        <?php else : ?>
                            <p class="no-margin"><?= _l('contabilidade102_nenhuma_conta_cadastrada'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<style>
/* --- Estilização da árvore --- */
.plano-contas-tree { padding-left: 0; }
.plano-contas-tree ul {
    padding-left: 25px;
    border-left: 1px dashed #ccc;
    margin-left: 8px;
}
.plano-contas-tree li { list-style-type: none; padding: 5px 0; }
.conta-info { display: flex; align-items: center; padding: 3px 5px; border-radius: 3px; }
.conta-info:hover { background-color: #f9f9f9; }
.conta-codigo { font-weight: bold; margin-right: 5px; }
.conta-actions { margin-left: 10px; white-space: nowrap; }
.conta-sintetica .conta-nome { font-weight: 500; }
.conta-inativa .conta-nome,
.conta-inativa .conta-codigo { color: #aaa; text-decoration: line-through; }
.conta-info .label { margin-left: 5px; }
</style>

</body>
</html>

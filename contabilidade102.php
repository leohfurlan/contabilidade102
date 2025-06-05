<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Contabilidade 102
Description: Módulo de contabilidade para Perfex CRM.
Version: 1.0.2
Requires at least: 2.3.*
Author: Atos PD&I
*/

// 1. Define o nome do módulo
define('CONTABILIDADE102_MODULE_NAME', 'contabilidade102');

// 2. Registra o arquivo de idioma
register_language_files(CONTABILIDADE102_MODULE_NAME, [CONTABILIDADE102_MODULE_NAME]);

// 3. Registra os hooks de ciclo de vida do módulo
register_activation_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_activate');
register_deactivation_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_deactivate');
register_uninstall_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_uninstall');

// 4. Adiciona os hooks para menu e permissões
hooks()->add_action('admin_init', 'contabilidade102_init_menu_items');
hooks()->add_action('admin_init', 'contabilidade102_permissions');


/**
 * Adiciona os itens de menu na barra lateral do admin.
 */
function contabilidade102_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('contabilidade102', '', 'view')) {

        // Adiciona o item de menu principal (Pai).
        // Não tem 'href' para que funcione como um agrupador que abre/fecha.
        $CI->app_menu->add_sidebar_menu_item('contabilidade102-main-menu', [
            'name'     => _l('contabilidade102_menu_main'), // Traduzido como 'Contabilidade'
            'icon'     => 'fa fa-calculator',
            'slug'     => 'contabilidade102_slug', // Slug único para o pai
            'position' => 7,
        ]);
        
        // Adiciona o link do Dashboard como um filho do menu principal
        // O parent_slug DEVE ser igual ao slug do pai definido acima.
        $CI->app_menu->add_sidebar_children_item('contabilidade102_slug', [
            'name'     => _l('contabilidade102_dashboard'), // Dashboard Contábil
            'slug'     => 'contabilidade102_dashboard_slug',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME), // Link para a página principal
            'position' => 5,
        ]);

        // Adiciona o link de Empresas como um filho do menu principal
        $CI->app_menu->add_sidebar_children_item('contabilidade102_slug', [
            'name'     => _l('contabilidade102_menu_empresas'), // Empresas
            'slug'     => 'contabilidade102_empresas_slug',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/empresas'),
            'position' => 10,
        ]);
        
        // Adiciona o link do Plano de Contas como um filho do menu principal
        $CI->app_menu->add_sidebar_children_item('contabilidade102_slug', [
            'name'     => _l('contabilidade102_menu_plano_contas'), // Plano de Contas
            'slug'     => 'contabilidade102_plano_contas_slug',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/plano_contas'),
            'position' => 15,
        ]);

        // Adiciona o link de Lançamentos como um filho do menu principal
        $CI->app_menu->add_sidebar_children_item('contabilidade102_slug', [
            'name'     => _l('contabilidade102_menu_lancamentos'), // Lançamentos
            'slug'     => 'contabilidade102_lancamentos_slug',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos'),
            'position' => 20,
        ]);

        // Adiciona o link de Livros/Relatórios como um filho do menu principal
        $CI->app_menu->add_sidebar_children_item('contabilidade102_slug', [
            'name'     => _l('contabilidade102_menu_livros'), // Livros/Relatórios
            'slug'     => 'contabilidade102_livros_slug',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/livros'),
            'position' => 25,
        ]);
    }
}

/**
 * Registra as permissões do módulo.
 */
function contabilidade102_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'edit'   => _l('permission_edit'),
        'create' => _l('permission_create'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities(CONTABILIDADE102_MODULE_NAME, $capabilities, _l('contabilidade102_module_name'));
}
/**
 * Função de ativação do módulo.
 */
function contabilidade102_module_activate()
{
    // O seu código para criar tabelas aqui estava correto e pode ser mantido como está.
    // ... (copie a sua função de ativação existente aqui) ...
    $CI = &get_instance();
    $db_prefix = db_prefix();
    $char_set = $CI->db->char_set;

    // Tabela: Plano de Contas
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_plano_contas')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_plano_contas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `codigo` varchar(50) NOT NULL,
            `nome` varchar(255) NOT NULL,
            `tipo` enum('ativo','passivo','patrimonio_liquido','receita','despesa','custo','conta_compensacao') NOT NULL,
            `natureza` enum('devedora','credora') DEFAULT NULL,
            `conta_pai_id` int(11) DEFAULT NULL,
            `permite_lancamentos` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Não (Sintética), 1=Sim (Analítica)',
            `obrigatorio_centro_custo` tinyint(1) NOT NULL DEFAULT 0,
            `ativo` tinyint(1) NOT NULL DEFAULT 1,
            `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_codigo` (`codigo`),
            KEY `idx_conta_pai_id` (`conta_pai_id`),
            KEY `idx_tipo` (`tipo`),
            CONSTRAINT `fk_plano_contas_pai` FOREIGN KEY (`conta_pai_id`) REFERENCES `{$db_prefix}contabilidade_plano_contas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
        
        // Inserir plano de contas básico se a tabela acabou de ser criada
        $contas_basicas = [
            ['codigo' => '1', 'nome' => 'ATIVO', 'tipo' => 'ativo', 'natureza' => 'devedora', 'permite_lancamentos' => 0],
            ['codigo' => '1.1', 'nome' => 'ATIVO CIRCULANTE', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 1, 'permite_lancamentos' => 0],
            ['codigo' => '1.1.1', 'nome' => 'CAIXA E EQUIVALENTES DE CAIXA', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 2, 'permite_lancamentos' => 0],
            ['codigo' => '1.1.1.01', 'nome' => 'Caixa Geral', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 3, 'permite_lancamentos' => 1],
            ['codigo' => '1.1.1.02', 'nome' => 'Bancos Conta Movimento', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 3, 'permite_lancamentos' => 1],
            ['codigo' => '1.1.2', 'nome' => 'CONTAS A RECEBER', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 2, 'permite_lancamentos' => 0],
            ['codigo' => '1.1.2.01', 'nome' => 'Clientes a Receber', 'tipo' => 'ativo', 'natureza' => 'devedora', 'conta_pai_id' => 6, 'permite_lancamentos' => 1],
            ['codigo' => '2', 'nome' => 'PASSIVO', 'tipo' => 'passivo', 'natureza' => 'credora', 'permite_lancamentos' => 0],
            ['codigo' => '2.1', 'nome' => 'PASSIVO CIRCULANTE', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 8, 'permite_lancamentos' => 0],
            ['codigo' => '2.1.1', 'nome' => 'FORNECEDORES', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 9, 'permite_lancamentos' => 0],
            ['codigo' => '2.1.1.01', 'nome' => 'Fornecedores Nacionais', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 10, 'permite_lancamentos' => 1],
            ['codigo' => '2.1.2', 'nome' => 'OBRIGAÇÕES SOCIAIS E TRABALHISTAS', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 9, 'permite_lancamentos' => 0],
            ['codigo' => '2.1.2.01', 'nome' => 'Salários a Pagar', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 12, 'permite_lancamentos' => 1],
            ['codigo' => '2.1.3', 'nome' => 'OBRIGAÇÕES TRIBUTÁRIAS', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 9, 'permite_lancamentos' => 0],
            ['codigo' => '2.1.3.01', 'nome' => 'Impostos a Recolher', 'tipo' => 'passivo', 'natureza' => 'credora', 'conta_pai_id' => 14, 'permite_lancamentos' => 1],
            ['codigo' => '3', 'nome' => 'PATRIMÔNIO LÍQUIDO', 'tipo' => 'patrimonio_liquido', 'natureza' => 'credora', 'permite_lancamentos' => 0],
            ['codigo' => '3.1', 'nome' => 'CAPITAL SOCIAL', 'tipo' => 'patrimonio_liquido', 'natureza' => 'credora', 'conta_pai_id' => 16, 'permite_lancamentos' => 0],
            ['codigo' => '3.1.1.01', 'nome' => 'Capital Social Subscrito', 'tipo' => 'patrimonio_liquido', 'natureza' => 'credora', 'conta_pai_id' => 17, 'permite_lancamentos' => 1],
            ['codigo' => '3.2', 'nome' => 'LUCROS OU PREJUÍZOS ACUMULADOS', 'tipo' => 'patrimonio_liquido', 'natureza' => 'credora', 'conta_pai_id' => 16, 'permite_lancamentos' => 0],
            ['codigo' => '3.2.1.01', 'nome' => 'Lucros Acumulados', 'tipo' => 'patrimonio_liquido', 'natureza' => 'credora', 'conta_pai_id' => 19, 'permite_lancamentos' => 1],
            ['codigo' => '4', 'nome' => 'RECEITAS', 'tipo' => 'receita', 'natureza' => 'credora', 'permite_lancamentos' => 0],
            ['codigo' => '4.1', 'nome' => 'RECEITA BRUTA DE VENDAS', 'tipo' => 'receita', 'natureza' => 'credora', 'conta_pai_id' => 21, 'permite_lancamentos' => 0],
            ['codigo' => '4.1.1.01', 'nome' => 'Receita de Venda de Produtos', 'tipo' => 'receita', 'natureza' => 'credora', 'conta_pai_id' => 22, 'permite_lancamentos' => 1],
            ['codigo' => '4.1.1.02', 'nome' => 'Receita de Prestação de Serviços', 'tipo' => 'receita', 'natureza' => 'credora', 'conta_pai_id' => 22, 'permite_lancamentos' => 1],
            ['codigo' => '5', 'nome' => 'CUSTOS E DESPESAS', 'tipo' => 'despesa', 'natureza' => 'devedora', 'permite_lancamentos' => 0],
            ['codigo' => '5.1', 'nome' => 'CUSTOS DOS PRODUTOS VENDIDOS OU SERVIÇOS PRESTADOS', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 25, 'permite_lancamentos' => 0],
            ['codigo' => '5.1.1.01', 'nome' => 'Custo dos Produtos Vendidos (CPV)', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 26, 'permite_lancamentos' => 1],
            ['codigo' => '5.1.1.02', 'nome' => 'Custo dos Serviços Prestados (CSP)', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 26, 'permite_lancamentos' => 1],
            ['codigo' => '5.2', 'nome' => 'DESPESAS OPERACIONAIS', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 25, 'permite_lancamentos' => 0],
            ['codigo' => '5.2.1', 'nome' => 'Despesas Administrativas', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 29, 'permite_lancamentos' => 0],
            ['codigo' => '5.2.1.01', 'nome' => 'Aluguéis e Condomínios', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 30, 'permite_lancamentos' => 1],
            ['codigo' => '5.2.1.02', 'nome' => 'Salários e Encargos (Administrativo)', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 30, 'permite_lancamentos' => 1],
        ];
        
        $mapa_contas_pai = [];
        foreach ($contas_basicas as $conta) {
            $pai_codigo = null;
            if (isset($conta['conta_pai_id'])) {
                $partes_codigo_pai = explode('.', $conta['codigo']);
                array_pop($partes_codigo_pai);
                $pai_codigo = implode('.', $partes_codigo_pai);
            }

            if ($pai_codigo && isset($mapa_contas_pai[$pai_codigo])) {
                $conta['conta_pai_id'] = $mapa_contas_pai[$pai_codigo];
            } else {
                unset($conta['conta_pai_id']);
            }
            
            $CI->db->insert($db_prefix . 'contabilidade_plano_contas', $conta);
            $mapa_contas_pai[$conta['codigo']] = $CI->db->insert_id();
        }
    }
    
    // ... (copie aqui as suas CREATE TABLE para contabilidade_lancamentos, contabilidade_lancamentos_itens, contabilidade_empresas, etc.) ...
    
    add_option('contabilidade102_ativo', '1');
    add_option('contabilidade102_versao', '1.0.2');
    add_option('contabilidade102_data_instalacao', date('Y-m-d H:i:s'));
    
    log_activity('Módulo Contabilidade 102 ativado e tabelas criadas/verificadas.');
}

/**
 * Função de desativação do módulo.
 */
function contabilidade102_module_deactivate()
{
    // O seu código aqui estava correto e pode ser mantido
    update_option('contabilidade102_ativo', '0');
    log_activity('Módulo Contabilidade 102 foi desativado [Usuário: ' . get_staff_full_name(get_staff_user_id()) . ']');
}

/**
 * Função de desinstalação do módulo.
 */
function contabilidade102_module_uninstall()
{
    // O seu código aqui estava correto e pode ser mantido
    // ...
    delete_option('contabilidade102_ativo');
    delete_option('contabilidade102_versao');
    delete_option('contabilidade102_data_instalacao');
    // ...
    log_activity('Módulo Contabilidade 102 foi desinstalado [Usuário: ' . get_staff_full_name(get_staff_user_id()) . ']');
}
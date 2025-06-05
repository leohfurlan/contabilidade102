<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Contabilidade Datalisto MVP
Description: Módulo de contabilidade construído pela Atos PD&I para Datalisto (MVP)
Version: 1.0.2
Requires at least: 2.3.*
Author: Atos PD&I
*/

// Define o nome do módulo para o Perfex identificar
define('CONTABILIDADE102_MODULE_NAME', 'contabilidade102');

// Registrar hooks de ativação/desativação/desinstalação
register_activation_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_activate');
register_deactivation_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_deactivate');
register_uninstall_hook(CONTABILIDADE102_MODULE_NAME, 'contabilidade102_module_uninstall');

// Adiciona menus, permissões e ganchos (hooks) do módulo
hooks()->add_action('admin_init', 'contabilidade102_init_menu_items');
hooks()->add_action('admin_init', 'contabilidade102_permissions');

/**
 * Adiciona item ao menu lateral do Perfex
 */
function contabilidade102_init_menu_items()
{
    $CI = &get_instance();
    if (has_permission('contabilidade102', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('contabilidade102_main', [
            'slug'     => 'contabilidade102_dashboard', // Slug para o item pai
            'name'     => _l('contabilidade102_menu_main'),
            'icon'     => 'fa fa-calculator',
            'position' => 5, // Ajuste a posição conforme necessário
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME),
        ]);

        // Submenu Cadastros
        $CI->app_menu->add_sidebar_menu_item('contabilidade102_cadastros', [
            'slug'     => 'contabilidade102_cadastros_slug',
            'name'     => _l('contabilidade102_menu_cadastros'),
            'icon'     => 'fa fa-pencil-square-o',
            'position' => 10,
            'parent_slug' => 'contabilidade102_main', // Adicionado para melhor organização se o item principal não for um link direto
             'children' => [
                [
                    'slug' => 'plano_contas',
                    'name' => _l('contabilidade102_menu_plano_contas'),
                    'href' => admin_url(CONTABILIDADE102_MODULE_NAME . '/plano_contas'),
                    'position' => 5,
                ],
                [
                    'slug' => 'empresas',
                    'name' => _l('contabilidade102_menu_empresas'),
                    'href' => admin_url(CONTABILIDADE102_MODULE_NAME . '/empresas'),
                    'position' => 10,
                ],
                 // Adicionar outros cadastros como sócios, contadores se tiverem telas dedicadas
            ],
        ]);
        
        // Submenu Lançamentos
        $CI->app_menu->add_sidebar_menu_item('contabilidade102_lancamentos_menu', [
            'slug'     => 'contabilidade102_lancamentos_slug',
            'name'     => _l('contabilidade102_menu_lancamentos'),
            'icon'     => 'fa fa-exchange',
            'position' => 15,
            'parent_slug' => 'contabilidade102_main',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/lancamentos'),
        ]);

        // Submenu Livros/Relatórios
        $CI->app_menu->add_sidebar_menu_item('contabilidade102_livros_menu', [
            'slug'     => 'contabilidade102_livros_slug',
            'name'     => _l('contabilidade102_menu_livros'),
            'icon'     => 'fa fa-book',
            'position' => 20,
            'parent_slug' => 'contabilidade102_main',
            'href'     => admin_url(CONTABILIDADE102_MODULE_NAME . '/livros'),
        ]);
    }
}

/**
 * Registra as permissões do módulo
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
    register_staff_capabilities(CONTABILIDADE102_MODULE_NAME, $capabilities, _l('CONTABILIDADE102_MODULE_NAME'));
}

/**
 * Função de ativação do módulo
 * Executa quando o módulo é ativado
 */
function contabilidade102_module_activate()
{
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
            `natureza` enum('devedora','credora') DEFAULT NULL, -- Natureza da conta (Devedora/Credora)
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

            ['codigo' => '5', 'nome' => 'CUSTOS E DESPESAS', 'tipo' => 'despesa', 'natureza' => 'devedora', 'permite_lancamentos' => 0], // Categoria ampla
            ['codigo' => '5.1', 'nome' => 'CUSTOS DOS PRODUTOS VENDIDOS OU SERVIÇOS PRESTADOS', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 25, 'permite_lancamentos' => 0],
            ['codigo' => '5.1.1.01', 'nome' => 'Custo dos Produtos Vendidos (CPV)', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 26, 'permite_lancamentos' => 1],
            ['codigo' => '5.1.1.02', 'nome' => 'Custo dos Serviços Prestados (CSP)', 'tipo' => 'custo', 'natureza' => 'devedora', 'conta_pai_id' => 26, 'permite_lancamentos' => 1],
            ['codigo' => '5.2', 'nome' => 'DESPESAS OPERACIONAIS', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 25, 'permite_lancamentos' => 0],
            ['codigo' => '5.2.1', 'nome' => 'Despesas Administrativas', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 29, 'permite_lancamentos' => 0],
            ['codigo' => '5.2.1.01', 'nome' => 'Aluguéis e Condomínios', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 30, 'permite_lancamentos' => 1],
            ['codigo' => '5.2.1.02', 'nome' => 'Salários e Encargos (Administrativo)', 'tipo' => 'despesa', 'natureza' => 'devedora', 'conta_pai_id' => 30, 'permite_lancamentos' => 1],
        ];
        
        // Mapeamento temporário para IDs de contas pai durante a inserção
        $mapa_contas_pai = [];
        foreach ($contas_basicas as $conta) {
            $pai_codigo = null;
            if (isset($conta['conta_pai_id'])) { // O ID numérico aqui refere-se ao índice do array $contas_basicas
                $partes_codigo_pai = explode('.', $conta['codigo']);
                array_pop($partes_codigo_pai);
                $pai_codigo = implode('.', $partes_codigo_pai);
            }

            if ($pai_codigo && isset($mapa_contas_pai[$pai_codigo])) {
                $conta['conta_pai_id'] = $mapa_contas_pai[$pai_codigo];
            } else {
                unset($conta['conta_pai_id']); // Garante que é nulo se não encontrado
            }
            
            $CI->db->insert($db_prefix . 'contabilidade_plano_contas', $conta);
            $mapa_contas_pai[$conta['codigo']] = $CI->db->insert_id(); // Armazena o ID real da conta inserida
        }
    }
    
    // Tabela: Lançamentos Contábeis
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_lancamentos')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_lancamentos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `numero_lancamento` varchar(50) DEFAULT NULL COMMENT 'Pode ser gerado ou manual',
            `data_lancamento` date NOT NULL,
            `historico_padrao_id` int(11) DEFAULT NULL,
            `descricao_historico` text NOT NULL,
            `lote_id` int(11) DEFAULT NULL,
            `status` enum('rascunho','integrado','cancelado') NOT NULL DEFAULT 'rascunho',
            `staff_id_criacao` int(11) NOT NULL,
            `staff_id_atualizacao` int(11) DEFAULT NULL,
            `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_numero_lancamento` (`numero_lancamento`),
            KEY `idx_data_lancamento` (`data_lancamento`),
            KEY `idx_staff_id_criacao` (`staff_id_criacao`),
            KEY `idx_status` (`status`)
            -- FOREIGN KEY (`historico_padrao_id`) REFERENCES `{$db_prefix}contabilidade_historicos_padrao` (`id`) ON DELETE SET NULL -- Se tiver tabela de históricos
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
    }
    
    // Tabela: Itens dos Lançamentos Contábeis (Partidas Dobradas)
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_lancamentos_itens')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_lancamentos_itens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `lancamento_id` int(11) NOT NULL,
            `plano_conta_id` int(11) NOT NULL,
            `tipo_movimento` enum('D','C') NOT NULL COMMENT 'D=Débito, C=Crédito',
            `valor` decimal(15,2) NOT NULL,
            `historico_complementar` text DEFAULT NULL,
            `centro_custo_id` int(11) DEFAULT NULL, 
            PRIMARY KEY (`id`),
            KEY `idx_lancamento_id` (`lancamento_id`),
            KEY `idx_plano_conta_id` (`plano_conta_id`),
            -- KEY `idx_centro_custo_id` (`centro_custo_id`), -- Se usar centro de custo
            CONSTRAINT `fk_item_lancamento` FOREIGN KEY (`lancamento_id`) REFERENCES `{$db_prefix}contabilidade_lancamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_item_plano_conta` FOREIGN KEY (`plano_conta_id`) REFERENCES `{$db_prefix}contabilidade_plano_contas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            -- CONSTRAINT `fk_item_centro_custo` FOREIGN KEY (`centro_custo_id`) REFERENCES `{$db_prefix}contabilidade_centros_custo` (`id`) ON DELETE SET NULL -- Se usar centro de custo
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
    }

    // Tabela: Empresas para Contabilidade (vinculada aos Clientes do Perfex)
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_empresas')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_empresas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cliente_id` int(11) NOT NULL COMMENT 'Referencia tblclients.userid',
            `regime_tributario` varchar(100) DEFAULT NULL,
            `inscricao_estadual` varchar(50) DEFAULT NULL,
            `inscricao_municipal` varchar(50) DEFAULT NULL,
            `data_inicio_atividades` date DEFAULT NULL,
            `contador_id` int(11) DEFAULT NULL,
            `ativo` tinyint(1) NOT NULL DEFAULT 1,
            `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_cliente_id` (`cliente_id`),
            KEY `idx_contador_id` (`contador_id`)
            -- CONSTRAINT `fk_empresa_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `{$db_prefix}clients` (`userid`) ON DELETE CASCADE, -- Perfex não usa FKs por padrão, mas seria ideal
            -- CONSTRAINT `fk_empresa_contador` FOREIGN KEY (`contador_id`) REFERENCES `{$db_prefix}contabilidade_contadores` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
    }

    // Tabela: Sócios (se aplicável, pode ser vinculada a contatos do Perfex ou ser independente)
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_socios')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_socios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `empresa_id` int(11) NOT NULL COMMENT 'Referencia contabilidade_empresas.id',
            `nome_completo` varchar(255) NOT NULL,
            `cpf` varchar(14) DEFAULT NULL,
            `percentual_participacao` decimal(5,2) DEFAULT NULL,
            `is_administrador` tinyint(1) NOT NULL DEFAULT 0,
            `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_empresa_cpf` (`empresa_id`, `cpf`),
            KEY `idx_empresa_id_socios` (`empresa_id`),
            CONSTRAINT `fk_socio_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `{$db_prefix}contabilidade_empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
    }
    
    // Tabela: Contadores (se aplicável, pode ser vinculada a staff do Perfex ou ser independente)
    if (!$CI->db->table_exists($db_prefix . 'contabilidade_contadores')) {
        $CI->db->query("CREATE TABLE `{$db_prefix}contabilidade_contadores` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) DEFAULT NULL COMMENT 'Referencia tblstaff.staffid, se for um membro da equipe',
            `nome_completo` varchar(255) NOT NULL,
            `crc` varchar(20) NOT NULL COMMENT 'Conselho Regional de Contabilidade',
            `cpf_cnpj` varchar(18) DEFAULT NULL,
            `email` varchar(191) DEFAULT NULL,
            `telefone` varchar(20) DEFAULT NULL,
            `ativo` tinyint(1) NOT NULL DEFAULT 1,
            `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_crc` (`crc`),
            UNIQUE KEY `uk_staff_id_contadores` (`staff_id`)
            -- CONSTRAINT `fk_contador_staff` FOREIGN KEY (`staff_id`) REFERENCES `{$db_prefix}staff` (`staffid`) ON DELETE SET NULL -- Perfex não usa FKs por padrão
        ) ENGINE=InnoDB DEFAULT CHARSET={$char_set};");
    }
    
    // Adicionar opções padrão do módulo
    add_option('contabilidade102_ativo', '1');
    add_option('contabilidade102_versao', '1.0.1'); // Versão atualizada
    add_option('contabilidade102_data_instalacao', date('Y-m-d H:i:s'));
    
    log_activity('Módulo Contabilidade Datalisto MVP ativado e tabelas criadas/verificadas.');
}

/**
 * Função de desativação do módulo
 * Executa quando o módulo é desativado
 */
function contabilidade102_module_deactivate()
{
    $CI = &get_instance();
    update_option('contabilidade102_ativo', '0');
    log_activity('Módulo Contabilidade Datalisto MVP foi desativado [Usuário: ' . get_staff_full_name(get_staff_user_id()) . ']');
}

/**
 * Função de desinstalação do módulo
 * Executa quando o módulo é completamente removido
 */
function contabilidade102_module_uninstall()
{
    $CI = &get_instance();
    $db_prefix = db_prefix();
    
    // Remover tabelas do módulo (descomente com cuidado - isso apagará os dados)
    // É uma boa prática manter os dados a menos que o usuário explicitamente queira removê-los.
    // Considere adicionar uma opção no módulo para limpar os dados antes da desinstalação.
    /*
    $CI->db->query('SET foreign_key_checks = 0;'); // Desabilitar verificação de FK temporariamente
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_lancamentos_itens`");
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_lancamentos`");
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_plano_contas`");
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_socios`");
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_empresas`");
    $CI->db->query("DROP TABLE IF EXISTS `{$db_prefix}contabilidade_contadores`");
    // Adicionar outras tabelas aqui se criadas
    $CI->db->query('SET foreign_key_checks = 1;'); // Reabilitar verificação de FK
    */
    
    // Remover opções do módulo
    $options_to_remove = [
        'contabilidade102_ativo',
        'contabilidade102_versao',
        'contabilidade102_data_instalacao',
    ];
    foreach ($options_to_remove as $option_name) {
        delete_option($option_name);
    }
    // Ou de forma mais genérica, mas CUIDADO para não remover opções de outros módulos se o prefixo for muito comum:
    // $CI->db->like('name', 'contabilidade102_')->delete(db_prefix() . 'options');

    log_activity('Módulo Contabilidade Datalisto MVP foi desinstalado [Usuário: ' . get_staff_full_name(get_staff_user_id()) . ']');
}
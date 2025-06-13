<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Arquivo de Rotas para o Módulo contabilidade102
 */

// A constante CONTABILIDADE102_MODULE_NAME será definida no seu arquivo principal: contabilidade102.php
// Ex: define('CONTABILIDADE102_MODULE_NAME', 'contabilidade102');
// Para este teste inicial e para garantir que a rota funcione mesmo que a constante
// não seja lida a tempo, vamos usar o nome do módulo diretamente na definição da rota.
// No entanto, a melhor prática é usar a constante.

// Rota principal para o dashboard do módulo.
// Acessada via: SEU_DOMINIO/admin/contabilidade102
// Direciona para: modules/contabilidade102/controllers/Contabilidade102.php -> método index()
$route['admin/contabilidade102'] = 'contabilidade102/admin/index';


// --- IMPORTANTE: PARA TESTAR O ERRO 404 ---
// Deixe APENAS a rota acima ativa neste arquivo.
// Comente todas as outras rotas do seu módulo temporariamente.

    //Exemplo de outras rotas que você adicionará depois:

    // Rotas para Plano de Contas
    $route['admin/contabilidade102/planocontas'] = 'contabilidade102/planocontas/index';
    $route['admin/contabilidade102/planocontas/(:any)'] = 'contabilidade102/planocontas/$1';

    // Rotas para Empresas
    $route['admin/contabilidade102/empresas'] = 'contabilidade102/empresas/index';
    $route['admin/contabilidade102/empresas/(:any)'] = 'contabilidade102/empresas/$1';

    // Rotas para Lançamentos
    $route['admin/contabilidade102/lancamentos'] = 'contabilidade102/lancamentos/index';
    $route['admin/contabilidade102/lancamentos/(:any)'] = 'contabilidade102/lancamentos/$1';

    // Rotas para Livros
    $route['admin/contabilidade102/livros'] = 'contabilidade102/livros/index';
    $route['admin/contabilidade102/livros/(:any)'] = 'contabilidade102/livros/$1';

?>
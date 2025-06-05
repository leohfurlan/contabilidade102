<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Para este teste, vamos usar CI_Controller para minimizar dependências.
// Se funcionar, depois podemos testar com AdminController.
class Teste extends CI_Controller // Ou AdminController, se CI_Controller não funcionar por alguma razão de contexto do admin
{
    public function __construct()
    {
        parent::__construct();
        // Propositalmente não carregaremos NADA aqui para o teste inicial.
        // Nem mesmo a variável $module_name ou checagem de permissão.
    }

    public function index()
    {
        echo "Módulo de Contabilidade - Controller Teste - Método Index ALCANÇADO!";
    }

    public function hello()
    {
        echo "Módulo de Contabilidade - Controller Teste - Método Hello ALCANÇADO!";
    }
}
?>
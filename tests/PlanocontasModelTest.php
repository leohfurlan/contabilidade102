<?php
use PHPUnit\Framework\TestCase;

class DummyResult {
    private $row;
    public function __construct($row) { $this->row = $row; }
    public function row() { return $this->row; }
}
class DummyDB {
    public $return_row;
    public function where($field, $value) { return $this; }
    public function get($table) { return new DummyResult($this->return_row); }
    public function insert($table, $data) { $this->insert_called = [$table, $data]; }
    public function insert_id() { return 1; }
}

class PlanocontasModelTest extends TestCase {
    public function setUp(): void {
        require_once __DIR__ . '/bootstrap.php';
    }

    public function testAddContaDuplicateCodeReturnsError() {
        $db = new DummyDB();
        $db->return_row = (object)['id' => 1];
        $model = new Planocontas_model();
        $model->db = $db;

        $data = [
            'codigo' => '1.1',
            'nome' => 'Teste',
            'tipo' => 'ativo',
            'natureza' => 'devedora',
            'permite_lancamentos' => 1
        ];
        $this->assertEquals('codigo_exists', $model->add_conta($data));
    }
}

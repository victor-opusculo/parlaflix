<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\TestsCompleted\TestCompletedId;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\PComp\Rpc\BaseFunctionsClass;
use VictorOpusculo\PComp\Rpc\HttpGetMethod;

require_once __DIR__ . '/../../../../../lib/Middlewares/AdminLoginCheck.php';

final class Functions extends BaseFunctionsClass
{
    protected $testCompletedId;

    protected array $middlewares = ['\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck'];

    #[HttpGetMethod]
    public function deleteTest(array $query) : array
    {
        $id = $this->testCompletedId;
        $conn = Connection::get();
        try
        {
            $test = new TestCompleted([ 'id' => $id ])->getSingle($conn);
            $result = $test->delete($conn);

            if ($result['affectedRows'] > 0)
                return [ 'success' => "Teste respondido excluído com sucesso!" ];
            else
                return [ 'error' => "Erro ao excluir o teste" ];
        }
        catch (Exception $e)
        {
            return [ 'error' => $e->getMessage() ];
        }
    }

}
<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VictorOpusculo\PComp\Rpc\BaseFunctionsClass;

require_once __DIR__ . "/../../../../../lib/Middlewares/AdminLoginCheck.php";

class Functions extends BaseFunctionsClass
{
    protected $courseId;

    protected array $middlewares = ['\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck'];

    public function saveTestSkel(array $data) : array
    {
        $conn = Connection::get();
        try
        {
            $id = $data['id'] ?? null;
            $lessonId = $data['lesson_id'] ?? null;

            $dataFull = [ ...$data ];
            $dataFull['test_data'] = TestData::build($data['test_data'])->toJson();

            $skel = new TestSkel([ 'lesson_id' => $lessonId ]);
            $action = match ($skel->existsByLessonId($conn))
            {
                true => function() use ($conn, $lessonId, $dataFull)
                { 
                    $updateResult = new TestSkel([ 'lesson_id' => $lessonId ])
                    ->getFromLessonId($conn)
                    ->fillPropertiesFromDataRow($dataFull)
                    ->save($conn);

                    return $updateResult['affectedRows'] > 0 
                        ? [ 'success' => "Questionário salvo com sucesso!" ] 
                        : [ 'info' => 'Nenhum dado alterado' ];
                },
                false => function() use ($conn, $lessonId, $dataFull)
                {
                    $insertResult = new TestSkel([ 'lesson_id' => $lessonId ])
                    ->fillPropertiesFromDataRow($dataFull)
                    ->save($conn);

                    return $insertResult['newId'] 
                        ? [ 'success' => "Questionário criado com sucesso!", 'newId' => $insertResult['newId'] ]
                        : [ 'error' => 'Não foi possível criar o questionário' ];
                }
            };

            return $action();
        }
        catch (Exception $e)
        {
            return [ 'error' => $e->getMessage() ];
        }
    }
}
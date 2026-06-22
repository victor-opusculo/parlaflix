<?php

use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermText;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermVersion;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use Tests\Feature\CourseCompletionContext;
use Tests\TestCase;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\LessonTests;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\PresenceMethod;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestQuestion;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestQuestionOption;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once "CourseCompletionContext.php";


test('create student', function () {
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $context = new CourseCompletionContext();

    $lgpd =  new LgpdTermText()->getSingle($conn)->value->unwrap();
    $lgpdVer = (int)(new LgpdTermVersion()->getSingle($conn)->value->unwrap());

    $stu = new Student()->setCryptKey($case->getDatabaseCrypt());

    $formFieldPrefix = "students";
    $password = "teste1234";
    $fullname = "Bruno Teste Camargo";
    $email = "bruno.camargo@teste.tst";
    $timezone = "America/Sao_Paulo";
    $telephone = "(00) 000-000-000";
    $inst = "Abel";
    $instrole = "Assistente";

    $stu->fillPropertiesFromFormInput(
    [
        "$formFieldPrefix:fullname" => $fullname,
        "$formFieldPrefix:email" => $email,
        "$formFieldPrefix:lgpdtermversion" => $lgpdVer,
        "$formFieldPrefix:lgpdTermText" => $lgpd,
        "$formFieldPrefix:timezone" => $timezone,
        "$formFieldPrefix:telephone" => $telephone,
        "$formFieldPrefix:institution" => $inst,
        "$formFieldPrefix:instrole" => $instrole,
    ]);

    $stu->hashPassword($password);

    $result = $stu->save($conn);

    expect($result)->toBeArray();
    expect($result['newId'])->toBeInt();
    expect($result['affectedRows'])->toBeInt();

    $gotten = new Student([ 'id' => $result['newId'] ])
    ->setCryptKey($case->getDatabaseCrypt())
    ->getSingle($conn);

    $context->student = $gotten;
    return $context;
});

test('has student', function(CourseCompletionContext $context)
{
    expect($context->student)->toBeInstanceOf(Student::class);
    return $context;
})
->depends('create student');

test('create course', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $courseNew = new Course([
         'name' => "Curso teste",
         'hours' => 25,
         'certificate_text' => "Texto de certificado",
         'min_points_required' => 6,
         'is_visible' => 1,
         'is_external' => 0,
         'members_only' => 0
    ]);

    $resultCourseNew = $courseNew->save($conn);
    expect($resultCourseNew)->toBeArray();
    expect($resultCourseNew['newId'])->toBeInt();
    expect($resultCourseNew['affectedRows'])->toBe(1);

    $gotten = new Course([ 'id' => $resultCourseNew['newId'] ])->getSingle($conn);
    expect($gotten)->toBeInstanceOf(Course::class);    

    $context->course = $gotten;
    return $context;
})->depends('has student');

test('has course', function(CourseCompletionContext $context)
{
    expect($context->course)->toBeInstanceOf(Course::class);
    return $context;
})->depends('create course');

test('create lessons', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $lessIndex = 0;

    $less1 = new Lesson([
        'course_id' => $context->course->id->unwrap(),
        'index' => ++$lessIndex,
        'title' => "Aula $lessIndex",
        'video_host' => 'youtube',
        'video_url' => '0000000',
        'presence_method' => PresenceMethod::Password->value,
        'completion_password' => '123abc',
        'completion_points' => 2
    ]);

    $less2 = new Lesson([
        'course_id' => $context->course->id->unwrap(),
        'index' => ++$lessIndex,
        'title' => "Aula $lessIndex",
        'video_host' => 'youtube',
        'video_url' => '0000000',
        'presence_method' => PresenceMethod::Password->value,
        'completion_password' => 'abc123',
        'completion_points' => 2
    ]);

    $less3 = new Lesson([
        'course_id' => $context->course->id->unwrap(),
        'index' => ++$lessIndex,
        'title' => "Aula $lessIndex",
        'video_host' => 'youtube',
        'video_url' => '0000000',
        'presence_method' => PresenceMethod::Test->value,
        'completion_password' => '',
        'completion_points' => 2
    ]);

    $less4 = new Lesson([
        'course_id' => $context->course->id->unwrap(),
        'index' => ++$lessIndex,
        'title' => "Aula $lessIndex",
        'video_host' => 'youtube',
        'video_url' => '0000000',
        'presence_method' => PresenceMethod::TestAndPassword->value,
        'completion_password' => '123456',
        'completion_points' => 2
    ]);

    $less5 = new Lesson([
        'course_id' => $context->course->id->unwrap(),
        'index' => ++$lessIndex,
        'title' => "Aula $lessIndex",
        'video_host' => 'youtube',
        'video_url' => '0000000',
        'presence_method' => PresenceMethod::Test->value,
        'completion_password' => '',
        'completion_points' => 2
    ]);

    //Password ==> 1, 2, 4
    //Test ==> 3, 4, 5

    $lessons = [ $less1, $less2, $less3, $less4, $less5 ];
    $newIds = [];
    foreach ($lessons as $less)
    {
        $result = $less->save($conn);
        expect($result)->toBeArray();
        expect($result['newId'])->toBeInt();
        expect($result['affectedRows'])->toBe(1);

        $newIds[] = $result['newId'];
    }

    $gottenLessons = array_map(fn(int $id) => new Lesson([ 'id' => $id ])->getSingle($conn), $newIds);

    expect($gottenLessons)->toBeArray();
    expect($gottenLessons)->each->toBeInstanceOf(Lesson::class);

    $context->lessons = $gottenLessons;
    return $context;
})->depends('has course');

test('has lessons', function(CourseCompletionContext $context)
{
    expect($context->lessons)->toBeArray();
    expect(count($context->lessons))->toBe(5);
    expect($context->lessons)->each->toBeInstanceOf(Lesson::class);
    return $context;
})->depends('create lessons');

test('create test skels', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $less3 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 3);
    $less4 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 4);
    $less5 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 5);

    $skel1 = new TestSkel([
        'lesson_id' => $less3->id->unwrap(),
        'name' => "Questionário Teste 1",
        'min_percent_for_approval' => 70,
        'test_data' => new TestData()
            ->setQuestions([
                new TestQuestion()
                    ->setText("Questão 1")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(true),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(false),
                    ]),
                new TestQuestion()
                    ->setText("Questão 2")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(true),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(true),
                    ])
            ])
            ->toJson()
    ]);

    $skel2 = new TestSkel([
        'lesson_id' => $less4->id->unwrap(),
        'name' => "Questionário Teste 2",
        'min_percent_for_approval' => 70,
        'test_data' => new TestData()
            ->setQuestions([
                new TestQuestion()
                    ->setText("Questão 1")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(true),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(false),
                    ]),
                new TestQuestion()
                    ->setText("Questão 2")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(true),
                    ])
            ])
            ->toJson()
    ]);

    $skel3 = new TestSkel([
        'lesson_id' => $less5->id->unwrap(),
        'name' => "Questionário Teste 2",
        'min_percent_for_approval' => 70,
        'test_data' => new TestData()
            ->setQuestions([
                new TestQuestion()
                    ->setText("Questão 1")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(true),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(false),
                    ]),
                new TestQuestion()
                    ->setText("Questão 2")
                    ->setOptions([
                        new TestQuestionOption()->setText("Opção A")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção B")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção C")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção D")->setIsCorrect(false),
                        new TestQuestionOption()->setText("Opção E")->setIsCorrect(true),
                    ])
            ])
            ->toJson()
    ]);

    $skels = [ $skel1, $skel2, $skel3 ];
    $newIds = [];
    foreach ($skels as $sk)
    {
        $result = $sk->save($conn);
        expect($result)->toBeArray();
        expect($result['newId'])->toBeInt();
        expect($result['affectedRows'])->toBe(1);

        $newIds[] = $result['newId'];
    }

    $gottenSkels = array_map(fn(int $id) => new TestSkel([ 'id' => $id ])->getSingle($conn), $newIds);
    expect($gottenSkels)->toBeArray();
    expect($gottenSkels)->each->toBeInstanceOf(TestSkel::class);

    $context->testsSkel = $gottenSkels;
    return $context;
})->depends('has lessons');

test('has test skels', function(CourseCompletionContext $context)
{
    expect($context->testsSkel)->toBeArray();
    expect(count($context->testsSkel))->toBe(3);
    expect($context->testsSkel)->each->toBeInstanceOf(TestSkel::class);

    return $context;
})->depends('create test skels');

test('subscribe student', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $student = $context->student;
    $course = $context->course;

    $subs = new Subscription([ 
        'student_id' => $student->id->unwrap(),
        'course_id' => $course->id->unwrap()
    ]);

    $result = $subs->save($conn);
    expect($result)->toBeArray();
    expect($result['newId'])->toBeInt();
    expect($result['affectedRows'])->toBe(1);

    $gottenSubscription = new Subscription([ 'id' => $result['newId'] ])->getSingle($conn);
    expect($gottenSubscription)->toBeInstanceOf(Subscription::class);

    $context->subscription = $gottenSubscription;
    return $context;
})->depends('has test skels');

test('has subscription', function(CourseCompletionContext $context)
{

    expect($context->subscription)->toBeInstanceOf(Subscription::class);
    return $context;
})->depends('subscribe student');

test('student fills passwords', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $less1 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 1);
    $less2 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 2);
    $less4 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 4);

    expect($less1)->toBeInstanceOf(Lesson::class);
    expect($less2)->toBeInstanceOf(Lesson::class);
    expect($less4)->toBeInstanceOf(Lesson::class);

    $student = $context->student;

    $_SESSION['user_timezone'] = $context->student->timezone->unwrap();

    $wrongPasswordLess1 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less1->id->unwrap(),
        'given_password' => 'wrong-pass',
        'is_correct' => 0
    ]);

    $correctPasswordLess1 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less1->id->unwrap(),
        'given_password' => $less1->completion_password->unwrap(),
        'is_correct' => 1
    ]);


    $wrongPasswordLess2 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less2->id->unwrap(),
        'given_password' => 'wrong-pass',
        'is_correct' => 0
    ]);

    $wrongPasswordLess2_2 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less2->id->unwrap(),
        'given_password' => 'wrong-pass2',
        'is_correct' => 0
    ]);

    $correctPasswordLess2 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less2->id->unwrap(),
        'given_password' => $less2->completion_password->unwrap(),
        'is_correct' => 1
    ]);


    $wrongPasswordLess4 = new StudentLessonPassword([ 
        'student_id' => $student->id->unwrap(),
        'lesson_id' => $less4->id->unwrap(),
        'given_password' => 'wrong',
        'is_correct' => 0
    ]);


    $passordsGiven = [
        $correctPasswordLess1,
        $correctPasswordLess2,
    ];

    $newIds = array_map(function(StudentLessonPassword $lp) use ($conn)
    {
        $result = $lp->save($conn);
        expect($result)->toBeArray();
        expect($result['newId'])->toBeInt();
        expect($result['affectedRows'])->toBe(1);

        return $result['newId'];
    }, $passordsGiven);


    $gottenPasswords = array_map(fn(int $id) => new StudentLessonPassword([ 'id' => $id ])->getSingle($conn), $newIds);
    expect($gottenPasswords)->toBeArray();
    expect($gottenPasswords)->each->toBeInstanceOf(StudentLessonPassword::class);

    $context->passwordsGiven = $gottenPasswords;
    return $context;
})->depends('has subscription');

test('has passwords', function(CourseCompletionContext $context)
{
    expect($context->passwordsGiven)->toBeArray();
    expect($context->passwordsGiven)->each->toBeInstanceOf(StudentLessonPassword::class);
    return $context;
})->depends('student fills passwords');

test('student fills tests', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $less3 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 3);
    $less4 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 4);
    $less5 = array_find($context->lessons, fn(Lesson $less) => $less->index->unwrap() === 5);

    expect([ $less3, $less4, $less5 ])->each->toBeInstanceOf(Lesson::class);

    $skel1_l3 = array_find($context->testsSkel, fn(TestSkel $skel) => $skel->lesson_id->unwrap() === $less3->id->unwrap());
    $skel2_l4 = array_find($context->testsSkel, fn(TestSkel $skel) => $skel->lesson_id->unwrap() === $less4->id->unwrap());
    $skel3_l5 = array_find($context->testsSkel, fn(TestSkel $skel) => $skel->lesson_id->unwrap()=== $less5->id->unwrap());

    expect([ $skel1_l3, $skel2_l4, $skel3_l5 ])->each->toBeInstanceOf(TestSkel::class);


    $skel1 = $skel1_l3->buildStructure(null);

    // All correct
    $answersArr = [];
    foreach ($skel1->questions as $quest)
    {
        $qArr = [];
        foreach ($quest->options as $opt)
            $qArr[] = $opt->isCorrect;

        $answersArr[] = $qArr;
    }

    [ $skel, $correctQuestions ] = LessonTests::setAndCalculateCorrectAnswers($skel1, $answersArr);

    $minRequired = $skel1_l3->min_percent_for_approval->unwrap();
    $grade = $correctQuestions / count($skel->questions) * 100;

    $newTestCompletedLesson3 = new TestCompleted([
        'subscription_id' => $context->subscription->id->unwrap(),
        'test_skel_id' => $skel1_l3->id->unwrap(),
        'lesson_id' => $skel1_l3->lesson_id->unwrap(),
        'test_data' => $skel->toJson(),
        'is_approved' => $grade >= $minRequired
    ]);

    expect($newTestCompletedLesson3->is_approved->unwrap())->toBeTruthy();


    // Test 2 lesson 4
    $skel2 = $skel2_l4->buildStructure(null);

    // All correct
    $answersArr = [];
    foreach ($skel2->questions as $quest)
    {
        $qArr = [];
        foreach ($quest->options as $opt)
            $qArr[] = $opt->isCorrect;

        $answersArr[] = $qArr;
    }

    [ $skel, $correctQuestions ] = LessonTests::setAndCalculateCorrectAnswers($skel2, $answersArr);

    $minRequired = $skel2_l4->min_percent_for_approval->unwrap();
    $grade = $correctQuestions / count($skel->questions) * 100;

    $newTestCompletedLesson4 = new TestCompleted([
        'subscription_id' => $context->subscription->id->unwrap(),
        'test_skel_id' => $skel2_l4->id->unwrap(),
        'lesson_id' => $skel2_l4->lesson_id->unwrap(),
        'test_data' => $skel->toJson(),
        'is_approved' => $grade >= $minRequired
    ]);

    expect($newTestCompletedLesson4->is_approved->unwrap())->toBeTruthy();

    // Test 3 lesson 5
    $skel3 = $skel3_l5->buildStructure(null);

    // All incorrect
    $answersArr = [];
    foreach ($skel1->questions as $quest)
    {
        $qArr = [];
        foreach ($quest->options as $opt)
            $qArr[] = !$opt->isCorrect;

        $answersArr[] = $qArr;
    }

    [ $skel, $correctQuestions ] = LessonTests::setAndCalculateCorrectAnswers($skel3, $answersArr);

    $minRequired = $skel3_l5->min_percent_for_approval->unwrap();
    $grade = $correctQuestions / count($skel->questions) * 100;

    $newTestCompletedLesson5 = new TestCompleted([
        'subscription_id' => $context->subscription->id->unwrap(),
        'test_skel_id' => $skel3_l5->id->unwrap(),
        'lesson_id' => $skel3_l5->lesson_id->unwrap(),
        'test_data' => $skel->toJson(),
        'is_approved' => $grade >= $minRequired
    ]);

    expect($newTestCompletedLesson5->is_approved->unwrap())->toBeFalsy();


    $completedTests = [ $newTestCompletedLesson3, $newTestCompletedLesson4, $newTestCompletedLesson5 ];
    $newIds = [];
    foreach ($completedTests as $compTest)
    {
        $result = $compTest->save($conn);
        expect($result)->toBeArray();
        expect($result['newId'])->toBeInt();
        expect($result['affectedRows'])->toBe(1);
        $newIds[] = $result['newId'];
    }

    $gottenTests = array_map(fn(int $id) => new TestCompleted([ 'id' => $id ])->getSingle($conn), $newIds);
    expect($gottenTests)->toBeArray();
    expect($gottenTests)->each->toBeInstanceOf(TestCompleted::class);

    $context->testsCompleted = $gottenTests;
    return $context;
})->depends('has passwords');

test('has tests completed', function(CourseCompletionContext $context)
{
    expect($context->testsCompleted)->toBeArray();
    expect($context->testsCompleted)->each->toBeInstanceOf(TestCompleted::class);
    return $context;
})->depends('student fills tests');

test('expects approvation', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    $data = $context->subscription->getSingleFromStudentAndCourse($conn);
    $lessCount = (int)$data->getOtherProperties()->lessonCount;
    $doneLessCount = (int)$data->getOtherProperties()->doneLessonCount;
    $maxPoints = (int)$data->getOtherProperties()->maxPoints;
    $studentPoints = (int)$data->getOtherProperties()->studentPoints;

    expect([ $lessCount, $doneLessCount, $maxPoints, $studentPoints ])->each->toBeInt();

    $lc = count($context->lessons);
    $mpoints = array_reduce($context->lessons, fn(int $c, Lesson $l) => $c + $l->completion_points->unwrap(), 0);
    expect($lessCount)->toBe($lc);
    expect($doneLessCount)->toBe($lc - 2); //Two lessons incomplete
    expect($maxPoints)->toBe($mpoints); // 10
    expect($studentPoints)->toBe($mpoints - 4); // 6 points made

    expect($context->course->min_points_required->unwrap())->toBeLessThanOrEqual($studentPoints);
    return $context;
})->depends('has tests completed');

test('clean up', function(CourseCompletionContext $context)
{
    /** @var TestCase*/
    $case = $this;
    $conn = $case->getDatabaseConn();

    foreach ($context->testsCompleted as $tc)
    {
        $result = $tc->delete($conn);
        expect($result)->toBeArray();
        expect($result['affectedRows'])->toBe(1);
    }

    foreach ($context->testsSkel as $ts)
    {
        $result = $ts->delete($conn);
        expect($result)->toBeArray();
        expect($result['affectedRows'])->toBe(1);
    }

    foreach ($context->passwordsGiven as $pass)
    {
        $result = $pass->delete($conn);
        expect($result)->toBeArray();
        expect($result['affectedRows'])->toBe(1);
    }

    $result = $context->subscription->delete($conn);
    expect($result)->toBeArray();
    expect($result['affectedRows'])->toBe(1);

    foreach ($context->lessons as $less)
    {
        $result = $less->delete($conn);
        expect($result)->toBeArray();
        expect($result['affectedRows'])->toBe(1);
    }

    $result = $context->course->delete($conn);
    expect($result)->toBeArray();
    expect($result['affectedRows'])->toBe(1);

    $result = $context->student->delete($conn);
    expect($result)->toBeArray();
    expect($result['affectedRows'])->toBe(1);

    expect(fn() => $context->student->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);
    expect(fn() => $context->subscription->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);
    expect(fn() => $context->course->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);

    foreach ($context->lessons as $less)
        expect(fn() => $less->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);

    foreach ($context->testsCompleted as $tc)
        expect(fn() => $tc->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);

    foreach ($context->testsSkel as $ts)
        expect(fn() => $ts->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);

    foreach ($context->passwordsGiven as $pass)
        expect(fn() => $pass->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);
})->depends('expects approvation');
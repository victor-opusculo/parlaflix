<?php

use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermText;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermVersion;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

test('create a student and delete it', function()
{
    /** @var TestCase */
    $case = $this;
    $conn = $case->getDatabaseConn();

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

    expect($gotten->id->unwrap())->toBe($result['newId']);
    expect($gotten->full_name->unwrap())->toBe($fullname);
    expect($gotten->email->unwrap())->toBe($email);
    expect($gotten->other_data->unwrap()->telephone->unwrap())->toBe($telephone);
    expect($gotten->other_data->unwrap()->institution->unwrap())->toBe($inst);
    expect($gotten->other_data->unwrap()->instRole->unwrap())->toBe($instrole);
    expect($gotten->timezone->unwrap())->toBe($timezone);
    expect($gotten->is_abel_member->unwrap())->toBeFalsy();
    expect($gotten->lgpd_term_version->unwrap())->toBe($lgpdVer);
    expect($gotten->lgpd_term->unwrap())->toBe($lgpd);
    expect($gotten->checkPassword($password))->toBeTrue();

    //Delete it

    $oldId = $result['newId'];

    $result = $gotten->delete($conn);
    expect($result)->toBeArray();
    expect($result['affectedRows'])->toBe(1);

    //Check

    expect(fn() => new Student([ 'id' => $oldId ])->getSingle($conn))->toThrow(DatabaseEntityNotFound::class);
});

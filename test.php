<?php

require_once __DIR__ . "\src\Client.php";
require_once __DIR__ . "\src\HTTPFormat.php";
require_once __DIR__ . "\src\HTTPRequester.php";

use fy403\aurora;

date_default_timezone_set("PRC");

$loginURl        = "http://localhost/auth";
$tasksUrl        = "http://localhost/tasks/send";
$touchUrl        = "http://localhost/tasks/touch";
$username        = "admin";
$password        = "password";
$timeoutDuration = 100 + rand(0, 4900);
$sleepDuration   = 50 + rand(0, 450);
$sendConcurrency = 3;

$addTask0;
$addTask1;
$addTask2;
$multiplyTask0;
$multiplyTask1;

function TestSendSyncWithTask()
{
    $client = new aurora\Client($GLOBALS['loginURl'], $GLOBALS['tasksUrl'], $GLOBALS['touchUrl']);
    $client->Init($GLOBALS['username'], $GLOBALS['password']);
    $requestOBJ = new aurora\CenterRequest;
    $requestOBJ->TaskType = "task";
    $requestOBJ->Timestamp = time();
    $requestOBJ->Signatures = array(
        $GLOBALS['addTask0'],
    );
    $requestOBJ->TimeoutDuration = $GLOBALS['timeoutDuration'];
    $requestOBJ->SleepDuration = $GLOBALS['sleepDuration'];
    $ret = $client->SendSync($requestOBJ);
    var_dump($ret);
}
function TestSendSyncWithGroup()
{
    $client = new aurora\Client($GLOBALS['loginURl'], $GLOBALS['tasksUrl'], $GLOBALS['touchUrl']);
    $client->Init($GLOBALS['username'], $GLOBALS['password']);
    $requestOBJ = new aurora\CenterRequest;
    $requestOBJ->TaskType = "group";
    $requestOBJ->Timestamp = time();
    $requestOBJ->Signatures = array(
        $GLOBALS['addTask0'],
        $GLOBALS['addTask1'],
        $GLOBALS['addTask2'],
    );
    $requestOBJ->TimeoutDuration = $GLOBALS['timeoutDuration'];
    $requestOBJ->SleepDuration = $GLOBALS['sleepDuration'];
    $requestOBJ->SendConcurrency = $GLOBALS['sendConcurrency'];
    $ret = $client->SendSync($requestOBJ);
    var_dump($ret);
}

function TestSendSyncWithChord()
{
    $client = new aurora\Client($GLOBALS['loginURl'], $GLOBALS['tasksUrl'], $GLOBALS['touchUrl']);
    $client->Init($GLOBALS['username'], $GLOBALS['password']);
    $requestOBJ = new aurora\CenterRequest;
    $requestOBJ->TaskType = "chord";
    $requestOBJ->Timestamp = time();
    $requestOBJ->Signatures = array(
        $GLOBALS['addTask0'],
        $GLOBALS['addTask1'],
    );
    $requestOBJ->TimeoutDuration = $GLOBALS['timeoutDuration'];
    $requestOBJ->SleepDuration = $GLOBALS['sleepDuration'];
    $requestOBJ->SendConcurrency = $GLOBALS['sendConcurrency'];
    $requestOBJ->CallBack = $GLOBALS['multiplyTask1'];
    $ret = $client->SendSync($requestOBJ);
    var_dump($ret);
}

function TestSendSyncWithChain()
{
    $client = new aurora\Client($GLOBALS['loginURl'], $GLOBALS['tasksUrl'], $GLOBALS['touchUrl']);
    $client->Init($GLOBALS['username'], $GLOBALS['password']);
    $requestOBJ = new aurora\CenterRequest;
    $requestOBJ->TaskType = "chain";
    $requestOBJ->Timestamp = time();
    $requestOBJ->Signatures = array(
        $GLOBALS['addTask0'],
        $GLOBALS['addTask1'],
        $GLOBALS['addTask2'],
        $GLOBALS['multiplyTask0'],
    );
    $requestOBJ->TimeoutDuration = $GLOBALS['timeoutDuration'];
    $requestOBJ->SleepDuration = $GLOBALS['sleepDuration'];
    $requestOBJ->SendConcurrency = $GLOBALS['sendConcurrency'];
    $ret = $client->SendSync($requestOBJ);
    var_dump($ret);
}

function initTasks()
{
    $GLOBALS['addTask0'] = new aurora\Signature;
    $GLOBALS['addTask0']->Name = "add";
    $arg1 = new aurora\Arg;
    $arg1->Type = "int64";
    $arg1->Value = 1;
    $arg2 = new aurora\Arg;
    $arg2->Type = "int64";
    $arg2->Value = 1;
    $GLOBALS['addTask0']->Args = array($arg1, $arg2);

    $GLOBALS['addTask1'] = new aurora\Signature;
    $GLOBALS['addTask1']->Name = "add";
    $arg1 = new aurora\Arg;
    $arg1->Type = "int64";
    $arg1->Value = 2;
    $arg2 = new aurora\Arg;
    $arg2->Type = "int64";
    $arg2->Value = 2;
    $GLOBALS['addTask1']->Args = array($arg1, $arg2);

    $GLOBALS['addTask2'] = new aurora\Signature;
    $GLOBALS['addTask2']->Name = "add";
    $arg1 = new aurora\Arg;
    $arg1->Type = "int64";
    $arg1->Value = 5;
    $arg2 = new aurora\Arg;
    $arg2->Type = "int64";
    $arg2->Value = 6;
    $GLOBALS['addTask2']->Args = array($arg1, $arg2);

    $GLOBALS['multiplyTask0'] = new aurora\Signature;
    $GLOBALS['multiplyTask0']->Name = "multiply";
    $arg1 = new aurora\Arg;
    $arg1->Type = "int64";
    $arg1->Value = 4;
    $GLOBALS['multiplyTask0']->Args = array($arg1);

    $GLOBALS['multiplyTask1'] = new aurora\Signature;
    $GLOBALS['multiplyTask1']->Name = "multiply";
}

initTasks();
TestSendSyncWithTask();
TestSendSyncWithGroup();
TestSendSyncWithChain();
TestSendSyncWithChain();

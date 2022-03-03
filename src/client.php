<?php

namespace fy403\aurora;

class Client
{
    public $loginUrl;
    public $tasksUrl;
    public $touchUrl;
    public $AuthRequest;
    public $requester;
    public $sleepDuration = 2;
    public $taskTimeoutDuration = 1600;
    public $taskSleepDuration = 50;
    public $taskSendConcurrency = 5;

    public function __construct($loginUrl, $tasksUrl, $touchUrl, $sleepDuration = 2, $taskTimeoutDuration = 1600, $taskSleepDuration = 50, $taskSendConcurrency = 5)
    {
        $this->loginUrl = $loginUrl;
        $this->tasksUrl = $tasksUrl;
        $this->touchUrl = $touchUrl;
        $this->sleepDuration = $sleepDuration; // 2s
        $this->taskTimeoutDuration = $taskTimeoutDuration; // 1600ms
        $this->taskSleepDuration = $taskSleepDuration; // 50ms
        $this->taskSendConcurrency = $taskSendConcurrency; // 5
    }

    public function Init($userName, $password)
    {
        $AuthRequest = new AuthRequest;
        $AuthRequest->Name = $userName;
        $AuthRequest->Password = $password;
        $this->requester = new HTTPRequester;
        $this->AuthRequest = $AuthRequest;
    }

    private function login(): Ret
    {
        $response = $this->requester->HTTPPost($this->loginUrl, $this->AuthRequest);
        if (!$response) {
            return new Ret(null, "Send Auth failed");
        }
        if ($response["Header"]["http_code"] != '200') {
            return new Ret(null, "Auth failed and StausCode is " . $response["Header"]["http_code"]);
        }
        return new Ret(null, false);
    }

    public function SendSync(CenterRequest $centerRequest): Ret
    {
        $response = $this->requester->HTTPPost($this->tasksUrl, $centerRequest);
        if (!$response) {
            return new Ret(null, "Send tasks failed");
        }
        switch ($response["Header"]["http_code"]) {
            case '200':
                $BodyObj = json_decode($response["Body"]);
                $responseOBJ = xclone($BodyObj, __NAMESPACE__ . "\\" . 'CenterResponse');
                return new Ret($responseOBJ, false);
            case '206':
                $BodyObj = json_decode($response["Body"]);
                $responseOBJ = xclone($BodyObj, __NAMESPACE__ . "\\" . 'CenterResponse');
                $signatures = array();
                if ($responseOBJ->TaskType == "group") {
                    foreach ($responseOBJ->TaskResponses as $taskResponse) {
                        $signatures[] = $taskResponse->Signatures;
                    }
                } else {
                    $signatures = $responseOBJ->TaskResponses[0]->Signatures;
                }
                if ($responseOBJ->TaskType == "chord") {
                    $callback = $responseOBJ->TaskResponses[0]->CallBack;
                } else {
                    $callback = null;
                }
                $requestOBJ = new CenterRequest;
                $requestOBJ->BatchId = $responseOBJ->BatchId;
                date_default_timezone_set("PRC");
                $requestOBJ->Timestamp = time();
                $requestOBJ->TaskType = $responseOBJ->TaskType;
                $requestOBJ->Signatures = $signatures;
                $requestOBJ->TimeoutDuration = $this->taskTimeoutDuration;
                $requestOBJ->SleepDuration = $this->taskSleepDuration;
                $requestOBJ->SendConcurrency = $this->taskSendConcurrency;
                $requestOBJ->CallBack = $callback;

                return $this->sendTouchWithTimeout($requestOBJ, $this->sleepDuration);
            case '403':
                $ret = $this->login();
                if (!$ret->Error) {
                    return $this->SendSync($centerRequest);
                }
                return $ret;
            default:
                return new Ret(null, "Unknow StatusCode " . $response["Header"]["http_code"]);
        }
    }

    private function sendTouchWithTimeout(CenterRequest $centerRequest, int $sleepDuration): Ret
    {
        while (1) {
            $ret = $this->sendTouch($centerRequest);
            if (!$ret->Result && !$ret->Error) {
                sleep($sleepDuration);
            } else {
                return $ret;
            }
        }
    }

    private function sendTouch(CenterRequest $centerRequest): Ret
    {
        $response = $this->requester->HTTPPost($this->touchUrl, $centerRequest);
        if (!$response) {
            return new Ret(null, "Send touch failed");
        }
        switch ($response["Header"]["http_code"]) {
            case '200':
                break;
            case '400':
                return new Ret(null, "Bad request");
            case '502':
                return new Ret(null, "Task has failed");
            default:
                return new Ret(null, "Unknow StatusCode " . $response["Header"]["http_code"]);
        }

        $BodyObj = json_decode($response["Body"]);
        $centerResponse = xclone($BodyObj, __NAMESPACE__ . "\\" . "CenterResponse");
        foreach ($centerResponse->TaskResponse as $taskResponse) {
            if (count($taskResponse->Result) == 0) {
                return new Ret(null, false);
            }
        }
        return new Ret($centerResponse, false);
    }
}

<?php

namespace fy403\aurora;

class AuthRequest
{
    public $Name;
    public $Password;
}

class AuthReponse
{
    public $Message;
    public $Name;
    public $UUID;
}

class CenterRequest
{
    public $UUID;
    public $User;
    public $BatchID;
    public $Timestamp;
    public $TaskType;
    public $Signatures;
    public $TimeoutDuration;
    public $SleepDuration;
    public $SendConcurrency;
    public $CallBack;
}

class CenterResponse
{
    public $UUID;
    public $User;
    public $BatchID;
    public $Timestamp;
    public $TaskType;
    public $TaskResponses;
}

class TaskResponse
{
    public $Results;
    public $Signatures;
    public $CallBack;
}

class Signature
{
    public $UUID;
    public $Name;
    public $RoutingKey;
    public $ETA;
    public $GroupUUID;
    public $GroupTaskCount;
    public $Args;
    public $Headers;
    public $Priority;
    public $Immutable;
    public $RetryCount;
    public $RetryTimeout;
    public $OnSuccess;
    public $OnError;
    public $ChordCallback;
    //MessageGroupId for Broker, e.g. SQS
    public $BrokerMessageGroupId;
    //ReceiptHandle of SQS Message
    public $SQSReceiptHandle;
    // StopTaskDeletionOnError used with sqs when we want to send failed messages to dlq,
    // and don't want aurora to delete from source queue
    public $StopTaskDeletionOnError;
    // IgnoreWhenTaskNotRegistered auto removes the request when there is no handeler available
    // When this is true a task with no handler will be ignored and not placed back in the queue
    public $IgnoreWhenTaskNotRegistered;
}

class Arg
{
    public $Name;
    public $Type;
    public $Value;
}

class Ret
{
    public $Result;
    public $Error;
    public function __construct($result, $error)
    {
        $this->Result = $result;
        $this->Error = $error;
    }
}

function xclone($object, $class)
{
    $new = new $class();
    foreach ($object as $attribute => $attributeValue) {
        $new->$attribute = $attributeValue;
    }
    return $new;
}

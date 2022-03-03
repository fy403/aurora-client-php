<?php

namespace fy403\aurora;

class HTTPRequester
{
    private $cookies;

    public function HTTPPost($url, $params)
    {
        $headers = array(
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );
        // 检查Cookie的有效性
        if (!empty($this->cookies)) {
            $headers[] = "Cookie: " . $this->cookies;
        }
        // 禁止发送http_code=100
        $headers[] = "Expect: ";
        $data = json_encode($params);
        $ch    = curl_init();
        // 开启curl Debug
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            return false;
        } else if (strpos($response, "\r\n\r\n")) {
            list($headers, $bodys) = $this->spliteResponse($response);
        } else {
            $headers = $this->getHeaders($response);
        }
        isset($headers["Set-Cookie"]) && $this->setCookies($headers["Set-Cookie"]);
        return [
            "Header" => $headers,
            "Body" => $bodys,
        ];
    }

    public function HTTPGet($url, $params)
    {
        $headers = array(
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );
        $data = json_encode($params);
        $ch    = curl_init($url . '?' . $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response && strpos($response, "\r\n\r\n")) {
            return $this->spliteResponse($response);
        }
        return $response;
    }

    public function HTTPPut($url, $params)
    {
        $data = json_encode($params);
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerStr = substr($response, 0, $headerSize);
        $bodyStr = substr($response, $headerSize);
        curl_close($ch);
        return array(
            "Header" => $headerStr,
            "Body" => $bodyStr,
        );
    }

    public function HTTPDelete($url, $params)
    {
        $data = json_encode($params);
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function getHeaders($headerStr)
    {
        $headers = array();
        foreach (explode("\r\n", $headerStr) as $idx => $line) {
            if ($idx === 0) {
                list($headers["version"], $headers["http_code"], $headers["reason"]) = explode(" ", $line, 3);
            } else {
                list($key, $value) = explode(': ', $line);
                // !重复字段替换
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    private function spliteResponse($response)
    {
        list($headers, $bodys) = explode("\r\n\r\n", $response, 2);
        $headers = $this->getHeaders($headers);
        return [$headers, $bodys];
    }

    private function setCookies($cookies)
    {
        if (!empty($cookies) && !strpos($cookies, "max-age=-1")) {
            $this->cookies = $cookies;
        } else {
            $this->cookies = "";
        }
    }
}

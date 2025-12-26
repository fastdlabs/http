<?php

namespace FastD\Http;

use FastD\Http\Exception\HttpException;
use FastD\Http\Request\Request;
use FastD\Http\Response\Response;

class Client
{
    const USER_AGENT = 'PHP Curl/1.1 (+https://github.com/fastdlabs/http)';

    protected array $options = [];

    public function withoutOption(int $key): Client
    {
        unset($this->options[$key]);

        return $this;
    }

    public function withAddedOption(int $key, mixed $value): Client
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function withOptions(array $options): Client
    {
        $this->options = $options;

        return $this;
    }

    public function request(Request $request, array $payload = []): Response
    {
        $ch = curl_init();
        $url = (string) $request->getUri();

        // Handle query parameters for GET and other methods that support query strings
        if (!empty($payload['query'])) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($payload['query']);
        }

        // Handle request body for methods that support it
        if (in_array(strtoupper($request->getMethod()), ['PUT', 'POST', 'DELETE', 'PATCH'])) {
            if (isset($payload['body'])) {
                $this->withAddedOption(CURLOPT_POSTFIELDS, $payload['body']);
            }
        }

        if (!array_key_exists(CURLOPT_USERAGENT, $this->options)) {
            $this->withAddedOption(CURLOPT_USERAGENT, static::USER_AGENT);
        }

        // Handle headers, removing any Expect header and adding a blank one to disable 100-continue behavior
        $headers = $payload['headers'] ?? [];
        $filteredHeaders = [];
        foreach ($headers as $header) {
            if (!str_starts_with(strtolower(trim($header)), 'expect:')) {
                $filteredHeaders[] = $header;
            }
        }
        // Add blank Expect header to disable 100-continue behavior
        $filteredHeaders[] = 'Expect:';

        $this->withAddedOption(CURLOPT_HTTPHEADER, $filteredHeaders);
        $this->withAddedOption(CURLOPT_URL, $url);
        $this->withAddedOption(CURLOPT_CUSTOMREQUEST, $request->getMethod());
        $this->withAddedOption(CURLINFO_HEADER_OUT, true);
        $this->withAddedOption(CURLOPT_HEADER, true);
        $this->withAddedOption(CURLOPT_RETURNTRANSFER, true);

        // Apply all user-defined options
        foreach ($this->options as $key => $option) {
            curl_setopt($ch, $key, $option);
        }

        $response = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMsg = curl_error($ch);

        if ($errorCode !== 0) {
            curl_close($ch);
            throw new HttpException($errorMsg, $errorCode);
        }

        if ($response === false) {
            curl_close($ch);
            throw new HttpException('cURL request failed');
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Split response headers and body
        $responseParts = explode("\r\n\r\n", $response, 2);
        if (count($responseParts) !== 2) {
            throw new HttpException('Invalid response format');
        }

        list($responseHeaders, $responseBody) = $responseParts;
        $responseHeaders = preg_split('/\r\n/', $responseHeaders, -1, PREG_SPLIT_NO_EMPTY);

        // Skip the first line (HTTP status line)
        array_shift($responseHeaders);
        $headers = [];
        foreach ($responseHeaders as $headerLine) {
            if (str_contains($headerLine, ':')) {
                list($key, $value) = explode(':', $headerLine, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        // Handle content encoding if needed
        if (isset($headers['Content-Encoding']) && in_array(strtolower($headers['Content-Encoding']), ['gzip', 'deflate'])) {
            $decoded = zlib_decode($responseBody);
            if ($decoded !== false) {
                $responseBody = $decoded;
            }
        }

        $response = new Response($responseBody, $statusCode);

        $response->withHeaders($headers);

        return $response;
    }
}
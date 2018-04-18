<?php
/**
 * Created by PhpStorm.
 * User: igore
 * Date: 17.04.18
 * Time: 8:50
 */

namespace Core\Container;


class Socket extends AbstractContainerItem
{

    /**
     * @param SocketRequest $request
     * @return SocketResponse
     * @throws \Exception
     */
    public function call(SocketRequest $request): SocketResponse
    {
        //todo tothink - every call will create a new socket??
        $connect = stream_socket_client($request->getHost().':'. $request->getPort(), $errno, $errstr);
        stream_set_timeout($connect,$request->getTimeout());
        if (false === $connect) {
            throw new \RuntimeException($request->getHost().':'.$request->getPort().' - not connect');
        }
        $out = $request->getMethod() . ' ' . $request->getUrl() . ' ' . $request->getHttpVersion() . "\r\n";
        $out .= 'Host: ' . $request->getHost() . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $response = new SocketResponse();
        fwrite($connect, $out);
        while (!feof($connect)) {
            $response->add(fread($connect, 128));
        }
        fclose($connect);
        return $response;
    }

    public function init(): void
    {
        // TODO: Implement init() method.
    }
}
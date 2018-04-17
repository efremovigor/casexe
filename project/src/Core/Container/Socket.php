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
        $connect = fsockopen($request->getHost(), $request->getPort(), $errno, $errstr, $request->getTimeout());
        if ($connect === false) {
            throw new \RuntimeException($request->getHost().':'.$request->getPort().' - not connect');
        }
        stream_set_timeout($connect,$request->getTimeout());
        $out = $request->getMethod() . ' ' . $request->getUrl() . ' ' . $request->getHttpVersion() . "\r\n";
        $out .= 'Host: ' . $request->getHost() . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $response = new SocketResponse();
        fwrite($connect, $out);
        while (!feof($connect)) {
            stream_get_meta_data($connect);
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
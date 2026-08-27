<?php

namespace App\Support;

class Network
{
    /**
     * Best-effort guess at this machine's LAN IP address, so links shared
     * with other devices on the network don't point at "localhost". Falls
     * back to 127.0.0.1 when nothing better can be determined.
     */
    public static function localIp(): string
    {
        if (function_exists('socket_create') && function_exists('socket_connect')) {
            $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            if ($socket !== false) {
                try {
                    if (@socket_connect($socket, '8.8.8.8', 53)) {
                        socket_getsockname($socket, $ip);

                        if (! empty($ip) && $ip !== '0.0.0.0') {
                            return $ip;
                        }
                    }
                } finally {
                    socket_close($socket);
                }
            }
        }

        $hostIp = gethostbyname(gethostname());

        if ($hostIp !== gethostname() && $hostIp !== '127.0.0.1') {
            return $hostIp;
        }

        return '127.0.0.1';
    }

    /**
     * Rewrite a fully-qualified URL to use the LAN IP instead of its
     * original host, keeping the scheme, port, and path intact.
     */
    public static function toLanUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return $url;
        }

        $host = self::localIp();
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return "{$parts['scheme']}://{$host}{$port}{$path}{$query}";
    }
}

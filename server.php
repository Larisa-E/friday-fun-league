<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$requestedPath = realpath($publicPath.$uri);

if ($uri !== '/' && $requestedPath && str_starts_with($requestedPath, $publicPath) && is_file($requestedPath)) {
    serveStaticFile($requestedPath, $uri);

    return true;
}

if (canGzipResponse() && ! ini_get('zlib.output_compression')) {
    header('Vary: Accept-Encoding');
    ob_start('ob_gzhandler');
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';

function serveStaticFile(string $path, string $uri): void
{
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimeType = mimeTypeForExtension($extension);
    $lastModified = gmdate('D, d M Y H:i:s', filemtime($path)).' GMT';
    $etag = '"'.sha1($path.'|'.filemtime($path).'|'.filesize($path)).'"';

    header('Content-Type: '.$mimeType);
    header('Last-Modified: '.$lastModified);
    header('ETag: '.$etag);

    if (isImmutableAsset($uri, $extension)) {
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');
    } else {
        header('Cache-Control: public, max-age=3600');
    }

    if ((($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag)
        || strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') === filemtime($path)) {
        http_response_code(304);

        return;
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        http_response_code(404);

        return;
    }

    if (canGzipResponse() && isCompressibleMimeType($mimeType)) {
        $gzipContents = gzencode($contents, 6);

        if ($gzipContents !== false) {
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
            header('Content-Length: '.strlen($gzipContents));

            echo $gzipContents;

            return;
        }
    }

    header('Content-Length: '.strlen($contents));

    echo $contents;
}

function canGzipResponse(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip');
}

function isImmutableAsset(string $uri, string $extension): bool
{
    if (str_starts_with($uri, '/build/assets/')) {
        return true;
    }

    return in_array($extension, ['css', 'js', 'woff', 'woff2', 'svg'], true);
}

function isCompressibleMimeType(string $mimeType): bool
{
    foreach ([
        'text/',
        'application/javascript',
        'application/json',
        'application/xml',
        'image/svg+xml',
    ] as $prefix) {
        if (str_starts_with($mimeType, $prefix)) {
            return true;
        }
    }

    return false;
}

function mimeTypeForExtension(string $extension): string
{
    return match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js', 'mjs' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'txt' => 'text/plain; charset=UTF-8',
        'html' => 'text/html; charset=UTF-8',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        default => 'application/octet-stream',
    };
}
<?php

namespace twin\common;

class Exception extends \Exception
{
    /**
     * Сообщения об ошибках по-умолчанию.
     * @var array
     */
    protected $errors = [
        300 => 'Multiple choices',
        301 => 'Moved permanently',
        302 => 'Moved temporarily',
        303 => 'See other',
        304 => 'Not modified',
        305 => 'Use proxy',
        307 => 'Temporary redirect',
        308 => 'Permanent redirect',

        400 => 'Bad request',
        401 => 'Unauthorized',
        402 => 'Payment required',
        403 => 'Forbidden',
        404 => 'Not found',
        405 => 'Method not allowed',
        406 => 'Not acceptable',
        407 => 'Proxy authentication required',
        408 => 'Request timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length required',
        412 => 'Precondition failed',
        413 => 'Payload too large',
        414 => 'Url too long',
        415 => 'Unsupported media type',
        416 => 'Range not satisfiable',
        417 => 'Expectation failed',
        418 => 'I’m a teapot',
        419 => 'Authentication timeout',
        421 => 'Misdirected request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Failed dependency',
        426 => 'Upgrade required',
        428 => 'Precondition required',
        429 => 'Too many requests',
        431 => 'Request header fields too large',
        449 => 'Retry with',
        451 => 'Unavailable for legal reasons',
        499 => 'Client closed request',

        500 => 'Internal server error',
        501 => 'Not implemented',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
        504 => 'Gateway timeout',
        505 => 'HTTP version not supported',
        506 => 'Variant also negotiates',
        507 => 'Insufficient storage',
        508 => 'Loop detected',
        509 => 'Bandwidth limit exceeded',
        510 => 'Not extended',
        511 => 'Network authentication required',
        520 => 'Unknown error',
        521 => 'Web server is down',
        522 => 'Connection timed out',
        523 => 'Origin is unreachable',
        524 => 'A timeout occurred',
        525 => 'SSL handshake failed',
        526 => 'Invalid SSL certificate',
    ];

    /**
     * @param int $code - код ошибки
     * @param string|null $message - сообщение об ошибке
     */
    public function __construct(int $code, ?string $message = null)
    {
        $this->code = $code;
        $this->message = $message ?: $this->getDefaultMessage();
    }

    /**
     * Вернуть сообщение об ошибке по-умолчанию.
     * @return string|null
     */
    private function getDefaultMessage(): ?string
    {
        return $this->errors[$this->code] ?? null;
    }
}

<?php

declare(strict_types=1);

namespace Zaphyr\Framework\Http\Utils;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class StatusCode
{
    /**
     * @const int
     */
    public const CONTINUE = 100;

    /**
     * @const int
     */
    public const SWITCHING_PROTOCOLS = 101;

    /**
     * @const int
     */
    public const PROCESSING = 102;

    /**
     * @const int
     */
    public const EARLY_HINTS = 103;

    /**
     * @const int
     */
    public const OK = 200;

    /**
     * @const int
     */
    public const CREATED = 201;

    /**
     * @const int
     */
    public const ACCEPTED = 202;

    /**
     * @const int
     */
    public const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * @const int
     */
    public const NO_CONTENT = 204;

    /**
     * @const int
     */
    public const RESET_CONTENT = 205;

    /**
     * @const int
     */
    public const PARTIAL_CONTENT = 206;

    /**
     * @const int
     */
    public const MULTI_STATUS = 207;

    /**
     * @const int
     */
    public const ALREADY_REPORTED = 208;

    /**
     * @const int
     */
    public const IM_USED = 226;

    /**
     * @const int
     */
    public const MULTIPLE_CHOICES = 300;

    /**
     * @const int
     */
    public const MOVED_PERMANENTLY = 301;

    /**
     * @const int
     */
    public const FOUND = 302;

    /**
     * @const int
     */
    public const SEE_OTHER = 303;

    /**
     * @const int
     */
    public const NOT_MODIFIED = 304;

    /**
     * @const int
     */
    public const USE_PROXY = 305;

    /**
     * @const int
     */
    public const TEMPORARY_REDIRECT = 307;

    /**
     * @const int
     */
    public const PERMANENT_REDIRECT = 308;

    /**
     * @const int
     */
    public const BAD_REQUEST = 400;

    /**
     * @const int
     */
    public const UNAUTHORIZED = 401;

    /**
     * @const int
     */
    public const PAYMENT_REQUIRED = 402;

    /**
     * @const int
     */
    public const FORBIDDEN = 403;

    /**
     * @const int
     */
    public const NOT_FOUND = 404;

    /**
     * @const int
     */
    public const METHOD_NOT_ALLOWED = 405;

    /**
     * @const int
     */
    public const NOT_ACCEPTABLE = 406;

    /**
     * @const int
     */
    public const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * @const int
     */
    public const REQUEST_TIMEOUT = 408;

    /**
     * @const int
     */
    public const CONFLICT = 409;

    /**
     * @const int
     */
    public const GONE = 410;

    /**
     * @const int
     */
    public const LENGTH_REQUIRED = 411;

    /**
     * @const int
     */
    public const PRECONDITION_FAILED = 412;

    /**
     * @const int
     */
    public const CONTENT_TOO_LARGE = 413;

    /**
     * @const int
     */
    public const URI_TOO_LONG = 414;

    /**
     * @const int
     */
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @const int
     */
    public const RANGE_NOT_SATISFIABLE = 416;

    /**
     * @const int
     */
    public const EXPECTATION_FAILED = 417;

    /**
     * @const int
     */
    public const I_AM_A_TEAPOT = 418;

    /**
     * @const int
     */
    public const MISDIRECTION_REQUIRED = 421;

    /**
     * @const int
     */
    public const UNPROCESSABLE_ENTITY = 422;

    /**
     * @const int
     */
    public const LOCKED = 423;

    /**
     * @const int
     */
    public const FAILED_DEPENDENCY = 424;

    /**
     * @const int
     */
    public const TOO_EARLY = 425;

    /**
     * @const int
     */
    public const UPGRADE_REQUIRED = 426;

    /**
     * @const int
     */
    public const PRECONDITION_REQUIRED = 428;

    /**
     * @const int
     */
    public const TOO_MANY_REQUESTS = 429;

    /**
     * @const int
     */
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    /**
     * @const int
     */
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * @const int
     */
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * @const int
     */
    public const NOT_IMPLEMENTED = 501;

    /**
     * @const int
     */
    public const BAS_GATEWAY = 502;

    /**
     * @const int
     */
    public const SERVICE_UNAVAILABLE = 503;

    /**
     * @const int
     */
    public const GATEWAY_TIMEOUT = 504;

    /**
     * @const int
     */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @const int
     */
    public const VARIANT_ALSO_NEGOTIATES = 506;

    /**
     * @const int
     */
    public const INSUFFICIENT_STORAGE = 507;

    /**
     * @const int
     */
    public const LOOP_DETECTED = 508;

    /**
     * @const int
     */
    public const NOT_EXTENDED = 510;

    /**
     * @const int
     */
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @var array<int, string>
     */
    protected static array $messages = [
        self::CONTINUE => 'Continue',
        self::SWITCHING_PROTOCOLS => 'Switching Protocols',
        self::PROCESSING => 'Processing',
        self::EARLY_HINTS => 'Early Hints',
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',
        self::MULTI_STATUS => 'Multi-status',
        self::ALREADY_REPORTED => 'Already Reported',
        self::IM_USED => 'IM Used',
        self::MULTIPLE_CHOICES => 'Multiple Choices',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found',
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::PERMANENT_REDIRECT => 'Permanent Redirect',
        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT => 'Request Timeout',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PRECONDITION_FAILED => 'Precondition Failed',
        self::CONTENT_TOO_LARGE => 'Content Too Large',
        self::URI_TOO_LONG => 'URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',
        self::I_AM_A_TEAPOT => 'I\'m a teapot',
        self::MISDIRECTION_REQUIRED => 'Misdirected Request',
        self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::LOCKED => 'Locked',
        self::FAILED_DEPENDENCY => 'Failed Dependency',
        self::TOO_EARLY => 'Too Early',
        self::UPGRADE_REQUIRED => 'Upgrade Required',
        self::PRECONDITION_REQUIRED => 'Precondition Required',
        self::TOO_MANY_REQUESTS => 'Too Many Requests',
        self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        self::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::BAS_GATEWAY => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        self::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        self::INSUFFICIENT_STORAGE => 'Insufficient Storage',
        self::LOOP_DETECTED => 'Loop Detected',
        self::NOT_EXTENDED => 'Not Extended',
        self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    /**
     * @param int $statusCode
     *
     * @return string|null
     */
    public static function getMessage(int $statusCode): string|null
    {
        return self::$messages[$statusCode] ?? null;
    }
}

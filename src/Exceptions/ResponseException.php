<?php

namespace Takatost\PubSub\CMQ\Exceptions;

/*
 * 返回异常
 */
use Illuminate\Support\Collection;

class ResponseException extends BaseException
{
    /**
     * @var Collection
     */
    protected $body;

    /**
     * FailedResponseException constructor.
     * @param string            $message
     * @param int               $code
     * @param                   $body
     */
    public function __construct($message = "", $code = 0, Collection $body = null)
    {
        parent::__construct($message, $code);

        $this->body = $body;
    }

    /**
     * @return Collection|null
     * @author         JohnWang <takato@vip.qq.com>
     */
    public function getBody()
    {
        return $this->body;
    }
}
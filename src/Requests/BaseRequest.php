<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2017/3/3
 * Time: 15:56
 */

namespace Takatost\PubSub\CMQ\Requests;

use Illuminate\Support\Collection;

/**
 * Class BaseRequest
 * @package Takatost\PubSub\CMQ\Requests
 */
class BaseRequest extends Collection
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @return string
     * @author         JohnWang <takato@vip.qq.com>
     */
    public function getMethod()
    {
        return $this->method;
    }
}
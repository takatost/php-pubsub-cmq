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
    const TYPE_QUEUE = 'queue';
    const TYPE_TOPIC = 'topic';

    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var
     */
    protected $type = self::TYPE_QUEUE;

    /**
     * @var string
     */
    protected $method;

    public function __construct(array $items)
    {
        parent::__construct($items);

        $this->put('Action', $this->action);
    }

    /**
     * @return string
     * @author         JohnWang <takato@vip.qq.com>
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 23:24
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker;


use BD\EzPlatformGraphQLBundle\DomainContent\NameHelper;

class BaseWorker
{
    /**
     * @var \BD\EzPlatformGraphQLBundle\DomainContent\NameHelper
     */
    private $nameHelper;

    public function setNameHelper(NameHelper $nameHelper)
    {
        $this->nameHelper = $nameHelper;
    }

    protected function getNameHelper()
    {
        return $this->nameHelper;
    }
}
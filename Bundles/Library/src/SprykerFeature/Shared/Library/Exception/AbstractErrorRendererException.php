<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Shared\Library\Exception;

abstract class AbstractErrorRendererException extends \Exception
{

    /**
     * @var string
     */
    protected $extra;

    /**
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param $extra
     *
     * @return void
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

}

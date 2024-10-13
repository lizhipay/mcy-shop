<?php
declare (strict_types=1);

namespace Kernel\Context\Interface;

interface Command
{
    /**
     * @return mixed
     */
    public function getCommand(): string;


    /**
     * @return string
     */
    public function getClass(): string;


    /**
     * @return string
     */
    public function getMethod(): string;


    /**
     * @return mixed
     */
    public function getExtend(): mixed;
}
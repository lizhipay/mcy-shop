<?php
declare (strict_types=1);

namespace Kernel\Exception;


class RedirectException extends \Exception
{
    private string $url;
    private int $time = 0;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return RedirectException
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     * @return RedirectException
     */
    public function setTime(int $time): self
    {
        $this->time = $time;
        return $this;
    }
}
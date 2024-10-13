<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

class Pay
{
    private ?string $payUrl = null;
    private int $renderMode;
    private array $option = [];
    private int $timeout = 3600;

    public function getPayUrl(): string
    {
        return $this->payUrl;
    }

    public function setPayUrl(string $payUrl): void
    {
        $this->payUrl = $payUrl;
    }

    public function getRenderMode(): int
    {
        return $this->renderMode;
    }

    public function setRenderMode(int $renderMode): void
    {
        $this->renderMode = $renderMode;
    }

    public function getOption(): array
    {
        return $this->option;
    }

    public function setOption(array $option): void
    {
        $this->option = $option;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
}
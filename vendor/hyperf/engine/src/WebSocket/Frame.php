<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Engine\WebSocket;

use Hyperf\Engine\Contract\WebSocket\FrameInterface;
use Hyperf\Engine\Exception\InvalidArgumentException;
use Hyperf\Engine\Http\Stream;
use Psr\Http\Message\StreamInterface;
use Swoole\WebSocket\Frame as SwooleFrame;

use function Hyperf\Engine\swoole_get_flags_from_frame;

class Frame implements FrameInterface
{
    /**
     * @deprecated
     */
    public const PING = '27890027';

    /**
     * @deprecated
     */
    public const PONG = '278a0027';

    protected ?StreamInterface $payloadData = null;

    public function __construct(
        protected bool $fin = true,
        protected bool $rsv1 = false,
        protected bool $rsv2 = false,
        protected bool $rsv3 = false,
        protected int $opcode = Opcode::TEXT,
        protected int $payloadLength = 0,
        protected string $maskingKey = '',
        mixed $payloadData = '',
    ) {
        if ($payloadData !== null && $payloadData !== '') {
            $this->setPayloadData($payloadData);
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function setOpcode(int $opcode): static
    {
        $this->opcode = $opcode;
        return $this;
    }

    public function withOpcode(int $opcode): static
    {
        return (clone $this)->setOpcode($opcode);
    }

    public function getFin(): bool
    {
        return $this->fin;
    }

    public function setFin(bool $fin): static
    {
        $this->fin = $fin;
        return $this;
    }

    public function withFin(bool $fin): static
    {
        return (clone $this)->setFin($fin);
    }

    public function getRSV1(): bool
    {
        return $this->rsv1;
    }

    public function setRSV1(bool $rsv1): static
    {
        $this->rsv1 = $rsv1;
        return $this;
    }

    public function withRSV1(bool $rsv1): static
    {
        return (clone $this)->setRSV1($rsv1);
    }

    public function getRSV2(): bool
    {
        return $this->rsv2;
    }

    public function setRSV2(bool $rsv2): static
    {
        $this->rsv2 = $rsv2;
        return $this;
    }

    public function withRSV2(bool $rsv2): static
    {
        return (clone $this)->setRSV2($rsv2);
    }

    public function getRSV3(): bool
    {
        return $this->rsv3;
    }

    public function setRSV3(bool $rsv3): static
    {
        $this->rsv3 = $rsv3;
        return $this;
    }

    public function withRSV3(bool $rsv3): static
    {
        return (clone $this)->setRSV3($rsv3);
    }

    public function getPayloadLength(): int
    {
        return $this->payloadData?->getSize() ?? 0;
    }

    public function setPayloadLength(int $payloadLength): static
    {
        $this->payloadLength = $payloadLength;
        return $this;
    }

    public function withPayloadLength(int $payloadLength): static
    {
        return (clone $this)->setPayloadLength($payloadLength);
    }

    public function getMask(): bool
    {
        return ! empty($this->maskingKey);
    }

    public function getMaskingKey(): string
    {
        return $this->maskingKey;
    }

    public function setMaskingKey(string $maskingKey): static
    {
        $this->maskingKey = $maskingKey;
        return $this;
    }

    public function withMaskingKey(string $maskingKey): static
    {
        return (clone $this)->setMaskingKey($maskingKey);
    }

    public function getPayloadData(): StreamInterface
    {
        return $this->payloadData;
    }

    public function setPayloadData(mixed $payloadData): static
    {
        $this->payloadData = new Stream((string) $payloadData);
        return $this;
    }

    public function withPayloadData(mixed $payloadData): static
    {
        return (clone $this)->setPayloadData($payloadData);
    }

    public function toString(bool $withoutPayloadData = false): string
    {
        return SwooleFrame::pack(
            (string) $this->getPayloadData(),
            $this->getOpcode(),
            swoole_get_flags_from_frame($this)
        );
    }

    public static function from(mixed $frame): static
    {
        if (! $frame instanceof SwooleFrame) {
            throw new InvalidArgumentException('The frame is invalid.');
        }

        return new Frame(
            (bool) ($frame->flags & SWOOLE_WEBSOCKET_FLAG_FIN),
            (bool) ($frame->flags & SWOOLE_WEBSOCKET_FLAG_RSV1),
            (bool) ($frame->flags & SWOOLE_WEBSOCKET_FLAG_RSV2),
            (bool) ($frame->flags & SWOOLE_WEBSOCKET_FLAG_RSV3),
            $frame->opcode,
            strlen($frame->data),
            $frame->flags & SWOOLE_WEBSOCKET_FLAG_MASK ? '258E' : '',
            $frame->data
        );
    }
}

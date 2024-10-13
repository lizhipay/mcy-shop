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

namespace Hyperf\Engine\Contract\WebSocket;

use Psr\Http\Message\StreamInterface;
use Stringable;

interface FrameInterface extends Stringable
{
    public function getOpcode(): int;

    public function setOpcode(int $opcode): static;

    public function withOpcode(int $opcode): static;

    public function getFin(): bool;

    public function setFin(bool $fin): static;

    public function withFin(bool $fin): static;

    public function getRSV1(): bool;

    public function setRSV1(bool $rsv1): static;

    public function withRSV1(bool $rsv1): static;

    public function getRSV2(): bool;

    public function setRSV2(bool $rsv2): static;

    public function withRSV2(bool $rsv2): static;

    public function getRSV3(): bool;

    public function setRSV3(bool $rsv3): static;

    public function withRSV3(bool $rsv3): static;

    public function getPayloadLength(): int;

    public function setPayloadLength(int $payloadLength): static;

    public function withPayloadLength(int $payloadLength): static;

    public function getMask(): bool;

    public function getMaskingKey(): string;

    public function setMaskingKey(string $maskingKey): static;

    public function withMaskingKey(string $maskingKey): static;

    public function getPayloadData(): StreamInterface;

    public function setPayloadData(mixed $payloadData): static;

    public function withPayloadData(mixed $payloadData): static;

    public function toString(bool $withoutPayloadData = false): string;

    public static function from(mixed $frame): static;
}

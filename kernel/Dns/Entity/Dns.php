<?php
declare (strict_types=1);

namespace Kernel\Dns\Entity;

use Kernel\Component\ToArray;

class Dns
{
    use ToArray;

    public ?string $type = null;
    public ?string $domain = null;
    public ?string $value = null;
    public ?int $ttl = null;

    /**
     * @param object $record
     */
    public function __construct(object $record)
    {
        if ($record instanceof \Net_DNS2_RR_A) {
            $this->type = "A";
            $this->domain = $record->name;
            $this->value = $record->address;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_AAAA) {
            $this->type = "AAAA";
            $this->domain = $record->name;
            $this->value = $record->address;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_CNAME) {
            $this->type = "CNAME";
            $this->domain = $record->name;
            $this->value = $record->cname;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_MX) {
            $this->type = "MX";
            $this->domain = $record->name;
            $this->value = $record->exchange;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_NS) {
            $this->type = "NS";
            $this->domain = $record->name;
            $this->value = $record->nsdname;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_PTR) {
            $this->type = "PTR";
            $this->domain = $record->name;
            $this->value = $record->ptrdname;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_SOA) {
            $this->type = "SOA";
            $this->domain = $record->name;
            $this->value = $record->mname;
            $this->ttl = $record->ttl;
        } elseif ($record instanceof \Net_DNS2_RR_TXT) {
            $this->type = "TXT";
            $this->domain = $record->name;
            $this->value = $record->text;
            $this->ttl = $record->ttl;
        }
    }
}
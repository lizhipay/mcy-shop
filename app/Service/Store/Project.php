<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\UpdateLog;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Project::class)]
interface Project
{
    /**
     * @return array
     */
    public function getNotice(): array;


    /**
     * @return array
     */
    public function getVersionLatest(): array;


    /**
     * @return array
     */
    public function getVersionList(): array;


    /**
     * @return void
     */
    public function update(): void;


    /**
     * @param string $hash
     * @return UpdateLog
     */
    public function getUpdateLog(string $hash): UpdateLog;
}
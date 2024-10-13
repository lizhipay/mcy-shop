<?php
declare(strict_types=1);

namespace Kernel\Context;


use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Util\Context;

class Upload extends \Kernel\Context\Abstract\File
{
    /**
     * @param string $name
     * @throws JSONException
     */
    public function __construct(string $name = 'file')
    {
        $this->name = $name;
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        $this->files = $request->file();
        parent::__construct();
    }
}
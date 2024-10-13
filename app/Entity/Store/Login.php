<?php
declare (strict_types=1);

namespace App\Entity\Store;

use Kernel\Component\ToArray;

class Login
{

    use ToArray;

    public int $id;
    public string $key;
    public string $username;
    public string $avatar;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->key = $data['key'];
        $this->username = $data['username'];
        $this->avatar = (string)$data['avatar'];
    }
}
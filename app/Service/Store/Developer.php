<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Developer::class)]
interface Developer
{

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return array
     */
    public function pluginList(array $post, Authentication $authentication): array;


    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     */
    public function createOrUpdatePlugin(array $post, Authentication $authentication): void;


    /**
     * @param string $name
     * @param Authentication $authentication
     * @return void
     */
    public function publishPlugin(string $name, Authentication $authentication): void;


    /**
     * @param string $name
     * @return array
     */
    public function getPluginTrackedFiles(string $name): array;


    /**
     * @param string $name
     * @param string $content
     * @param Authentication $authentication
     * @return void
     */
    public function updatePlugin(string $name, string $content, Authentication $authentication): void;


    /**
     * @param int $pluginId
     * @param int $page
     * @param int $limit
     * @param Authentication $authentication
     * @return array
     */
    public function getPluginVersionList(int $pluginId, int $page, int $limit, Authentication $authentication): array;


    /**
     * @param int $pluginId
     * @param array $post
     * @param Authentication $authentication
     * @return array
     */
    public function getPluginAuthorizationList(int $pluginId, array $post, Authentication $authentication): array;


    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     */
    public function addPluginAuthorization(array $post, Authentication $authentication): void;


    /**
     * @param int $authId
     * @param Authentication $authentication
     * @return void
     */
    public function removePluginAuthorization(int $authId, Authentication $authentication): void;
}
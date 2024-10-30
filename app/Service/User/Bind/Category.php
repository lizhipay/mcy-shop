<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\Category as Model;
use App\Model\User;
use Hyperf\Database\Model\Builder;
use Kernel\Util\Tree;


class Category implements \App\Service\User\Category
{

    /**
     * @param User|null $user
     * @return array[]
     */
    public function only(?User $user): array
    {
        $query = Model::query()->withCount(['item as item_count' => function (Builder $relation) {
            $relation->where("status", 1);
        }]);
        if ($user) {
            $query = $query->where("user_id", $user->id);
        } else {
            $query = $query->whereNull("user_id");
        }

        $category = $query->orderBy("sort", "asc")->where("status", 1)->get(["id", "name", "icon", "pid"])->toArray();

        return $this->updateItemCount(Tree::generate($category));
    }


    /**
     * @param array $category
     * @return array
     */
    private function updateItemCount(array $category): array
    {
        $updateItemCount = function (&$node) use (&$updateItemCount) {
            if (!isset($node['children']) || count($node['children']) == 0) {
                return $node['item_count'];
            }

            $newChildren = [];
            foreach ($node['children'] as &$child) {
                $childCount = $updateItemCount($child);
                if ($childCount > 0) {
                    $newChildren[] = $child; // 仅保留 item_count > 0 的子节点
                }
                $node['item_count'] += $childCount;
            }
            $node['children'] = $newChildren; // 更新子节点
            return $node['item_count'];
        };

        foreach ($category as &$rootNode) {
            $updateItemCount($rootNode);
        }
        return array_filter($category, fn($node) => $node['item_count'] > 0); // 过滤根节点
    }

    /*    private function updateItemCount(array $category): array
        {
            $updateItemCount = function (&$node) use (&$updateItemCount) {
                if (!isset($node['children']) || count($node['children']) == 0) {
                    return $node['item_count'];
                }
                foreach ($node['children'] as &$child) {
                    $node['item_count'] += $updateItemCount($child);
                }
                return $node['item_count'];
            };
            foreach ($category as &$rootNode) {
                $updateItemCount($rootNode);
            }
            return $category;
        }*/
}
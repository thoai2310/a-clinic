<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\CustomerTag;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function filter($params)
    {
        try {
            $tags = Tag::query();
            $pageSize = $params['pageSize'] ?? 2;

            $tags = $tags->with(['customers']);

            return $tags->paginate($pageSize);
        } catch (\Exception $exception) {
            return collect([]);
        }
    }

    public function create($data)
    {
        try {
            DB::beginTransaction();
            $tag = new Tag();
            $tag->name = $data['name'];
            $tag->key = $data['key'];
            $tag->save();

            $id = $tag->id;
            if ($id) {
                $customerIDs = $data['customerIds'] ?? [];
                foreach ($customerIDs as $customerID) {
                    $customerTag = new CustomerTag();
                    $customerTag->customer_id = $customerID;
                    $customerTag->tag_id = $id;
                    $customerTag->save();
                }
            }

            DB::commit();
            return $tag;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
}

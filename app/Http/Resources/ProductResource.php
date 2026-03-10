<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // image url ကို absolute url ပြောင်းပေးတယ် can show in frontend  by URL

        // $imageUrl = $this->image_url;

        // if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
        //     $imageUrl = Storage::disk('public')->url(ltrim($imageUrl, '/'));
        // }
        // image_url ကို absolute url ပြောင်းပေးတယ် အတွက် frontend မှာ show လုပ်ရန်  URL လိုအပ်တယ်။ image_url ကို absolute url ပြောင်းပေးခြင်းဖြင့် frontend မှာ image ကိုပြသနိုင်ပါသည်။

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $this->image_url, // image url ကို absolute url ပြောင်းပေးတယ် to call browser by https
            'category_name' => $this->category?->name, // category မရှိလည်း error မတက်အောင် null-safe
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

// 'category' => new CategoryResource($this->whenLoaded('category')), //obj

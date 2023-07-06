<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string|null $image
 * @property int|null $is_deleted
 * @property StoreProduct[] $storeProducts
 */
class Product extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'product';
    }

    public function rules(): array
    {
        return [
            [['is_deleted'], 'integer'],
            [['image'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'image' => 'Image',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function getStoreProducts(): ActiveQuery
    {
        return $this->hasMany(StoreProduct::class, ['product_id' => 'id']);
    }
}

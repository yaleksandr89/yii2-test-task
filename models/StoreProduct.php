<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $product_id
 * @property string|null $product_image
 * @property Product $product
 */
class StoreProduct extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'store_product';
    }

    public function rules(): array
    {
        return [
            [['product_id'], 'integer'],
            [['product_image'], 'string', 'max' => 255],
            [
                ['product_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Product::class,
                'targetAttribute' => ['product_id' => 'id']
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'product_image' => 'Product Image',
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}

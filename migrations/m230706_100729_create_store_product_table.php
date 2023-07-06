<?php

use yii\db\Migration;

class m230706_100729_create_store_product_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%store_product}}', [
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'product_id' => $this->integer()->unsigned(),
            'product_image' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk-store_product-product_id',
            '{{%store_product}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE',
            'CASCADE'
        );


        // Вставка тестовых данных
        $this->batchInsert(
            '{{%store_product}}',
            ['product_id', 'product_image'],
            [
                [1, 'images/products/image1.jpg'],
                [2, 'images/products/image2.jpg'],
                [5, 'images/products/image5.jpg'],
                [7, 'images/products/image7.jpg'],
            ]
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-store_product-product_id', '{{%store_product}}');
        $this->dropTable('{{%store_product}}');
    }
}

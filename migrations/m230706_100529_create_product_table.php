<?php

use yii\db\Migration;

class m230706_100529_create_product_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'image' => $this->string(),
            'is_deleted' => $this->boolean()->defaultValue(false),
        ]);

        // Вставка тестовых данных
        $this->batchInsert(
            '{{%product}}',
            ['image', 'is_deleted'],
            [
                ['images/products/image1.jpg', false],
                ['images/products/image2.jpg', true],
                ['images/products/image3.jpg', true],
                ['images/products/image4.jpg', false],
                ['images/products/image5.jpg', true],
                ['images/products/image6.jpg', false],
                ['images/products/image7.jpg', true],
            ]
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%product}}');
    }
}

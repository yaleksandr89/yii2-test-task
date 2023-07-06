<?php

namespace app\commands;

use app\components\Images;
use app\models\Product;
use app\models\StoreProduct;
use Exception;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

class GenerateThumbnailsCommand extends Controller
{
    protected $size;
    protected $watermarked;
    protected $publishedOnly;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'size',
            'watermarked',
            'publishedOnly',
        ]);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            's' => 'size',
            'w' => 'watermarked',
            'p' => 'publishedOnly',
        ]);
    }

    public function actionIndex(): int
    {
        $this->preparePassedOptionValues();

        $productQuery = Product::find()
            ->andWhere(['is_deleted' => false])
            ->andWhere(['not', ['image' => null]]);
        $storeProductQuery = StoreProduct::find()
            ->innerJoinWith('product')
            ->andWhere(['product.is_deleted' => false])
            ->andWhere(['not', ['store_product.product_image' => null]]);

        if ($this->publishedOnly) {
            $productQuery->innerJoinWith('storeProducts');
            $storeProductQuery->innerJoinWith('product');
        }

        $totalThumbnailsGenerated = 0;
        $failedThumbnailsCount = 0;

        try {
            $productModels = $productQuery->all();
            $storeProductModels = $storeProductQuery->all();

            foreach ($productModels as $productModel) {
                if ($this->generateThumbnails($productModel, 'image')) {
                    $totalThumbnailsGenerated++;
                }
            }

            foreach ($storeProductModels as $storeProductModel) {
                if ($this->generateThumbnails($storeProductModel, 'product_image')) {
                    $totalThumbnailsGenerated++;
                }
            }
        } catch (Exception $e) {
            $this->stdout("Error occurred: " . $e->getMessage() . "\n", BaseConsole::FG_RED);
            $failedThumbnailsCount++;
        }

        $this->stdout("Total Thumbnails Generated: $totalThumbnailsGenerated\n", BaseConsole::FG_GREEN);
        $this->stdout("Failed to Generate Thumbnails: $failedThumbnailsCount\n", BaseConsole::FG_RED);
        return ExitCode::OK;
    }

    private function parseSizes(string $size): array
    {
        if (!preg_match('/^[0-9x,]+$/', $size)) {
            throw new InvalidArgumentException('Invalid size format. Use digits, "x" and "," only');
        }

        $sizesToArray = [];

        $sizeParts = explode(',', trim($size, ','));

        foreach ($sizeParts as $sizePart) {
            $dimensions = explode('x', $sizePart);

            switch (true) {
                case count($dimensions) === 1 && !in_array('', $dimensions, true):
                    $sizesToArray[] = [
                        'width' => (int)$dimensions[0],
                        'height' => (int)$dimensions[0],
                    ];
                    break;
                case count($dimensions) === 2 && !in_array('', $dimensions, true):
                    $sizesToArray[] = [
                        'width' => (int)$dimensions[0],
                        'height' => (int)$dimensions[1],
                    ];
                    break;
                default:
                    throw new InvalidArgumentException('Invalid size format. Use "width" or "widthxheight"');
            }
        }

        return $sizesToArray;
    }

    private function preparePassedOptionValues(): void
    {
        $this->setOptionValue(name: 'size', required: true);
        $this->setOptionValue(name: 'watermarked', defaultValue: false);
        $this->setOptionValue(name: 'publishedOnly', defaultValue: true);
    }

    private function setOptionValue(string $name, $defaultValue = null, bool $required = false): void
    {
        if (!array_key_exists($name, $this->passedOptionValues) && $required) {
            $this->stdout('Error: ', BaseConsole::FG_RED);
            $this->stdout("Missing required arguments: '$name'\n", BaseConsole::FG_YELLOW);
            return;
        }

        if (!array_key_exists($name, $this->passedOptionValues) && null !== $defaultValue) {
            $this->$name = $defaultValue;
        } else {
            $value = in_array($this->passedOptionValues[$name], ['true', 'false'])
                ? filter_var($this->passedOptionValues[$name], FILTER_VALIDATE_BOOLEAN)
                : $this->passedOptionValues[$name];

            $this->$name = $value;
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function generateThumbnails(Product|StoreProduct $model, string $attribute): bool
    {
        $imagePath = $model->$attribute;

        if ($imagePath !== null) {
            /** @var Images $imageHandler */
            $imageHandler = Yii::$app->get('images');

            $stringSizesToArray = $this->parseSizes($this->size);

            if ($this->watermarked) {
                $imageHandler::generateWatermarkedMiniature($imagePath, $stringSizesToArray);
            } else {
                $imageHandler::generateMiniature($imagePath, $stringSizesToArray);
            }

            return true;
        }

        return false;
    }
}

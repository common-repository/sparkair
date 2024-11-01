<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

use Sparkair\SparkPlugins\SparkWoo\Common\Collections\AbstractCollection;
use Sparkair\SparkPlugins\SparkWoo\Common\Collections\CollectionInterface;
class TrainingDataRecordCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * @var TrainingDataRecord[]
     */
    protected array $items;
    protected string $since;
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }
    public function setSince(string $since) : void
    {
        $this->since = $since;
    }
    private function getItemByUserIdAndItemId(int $userId, int $itemId) : ?TrainingDataRecord
    {
        foreach ($this->items as $item) {
            if ($item->getUserId() === $userId && $item->getItemId() === $itemId) {
                return $item;
            }
        }
        return null;
    }
    private function getOrCreateItemByUserIdAndItemId(int $userId, int $itemId) : TrainingDataRecord
    {
        $trainingDataRecord = $this->getItemByUserIdAndItemId($userId, $itemId);
        if (!$trainingDataRecord) {
            $trainingDataRecord = new TrainingDataRecord($userId, $itemId);
            $this->items[] = $trainingDataRecord;
        }
        return $trainingDataRecord;
    }
    public function setUserItemOrders(int $userId, int $itemId, int $itemInOrdersCount) : void
    {
        $trainingDataRecord = $this->getOrCreateItemByUserIdAndItemId($userId, $itemId);
        $trainingDataRecord->setItemInOrdersCount($itemInOrdersCount);
    }
    public function setUserItemViews(int $userId, int $itemId, int $itemInViewsCount) : void
    {
        $trainingDataRecord = $this->getOrCreateItemByUserIdAndItemId($userId, $itemId);
        $trainingDataRecord->setItemViewedCount($itemInViewsCount);
    }
    public function getUserIds() : array
    {
        $userIds = array();
        foreach ($this->items as $item) {
            $userIds[] = $item->getUserId();
        }
        return \array_unique($userIds);
    }
    public function getSince() : string
    {
        return $this->since;
    }
}

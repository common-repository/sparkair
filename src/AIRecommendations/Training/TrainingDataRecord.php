<?php

namespace Sparkair\SparkPlugins\SparkWoo\AIRecommendations\Training;

class TrainingDataRecord
{
    protected int $userId;
    protected int $itemId;
    protected int $itemViewedCount = 0;
    protected int $itemInOrdersCount = 0;
    public function __construct(int $userId, int $itemId)
    {
        $this->userId = $userId;
        $this->itemId = $itemId;
    }
    public function getUserId() : int
    {
        return $this->userId;
    }
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getItemId() : int
    {
        return $this->itemId;
    }
    public function setItemId(int $itemId) : void
    {
        $this->itemId = $itemId;
    }
    public function getItemViewedCount() : int
    {
        return $this->itemViewedCount;
    }
    public function setItemViewedCount(int $itemViewedCount) : void
    {
        $this->itemViewedCount = $itemViewedCount;
    }
    public function getItemInOrdersCount() : int
    {
        return $this->itemInOrdersCount;
    }
    public function setItemInOrdersCount(int $itemInOrdersCount) : void
    {
        $this->itemInOrdersCount = $itemInOrdersCount;
    }
    public function toArray() : array
    {
        return ['userId' => $this->userId, 'itemId' => $this->itemId, 'itemViewedCount' => $this->itemViewedCount, 'itemInOrdersCount' => $this->itemInOrdersCount];
    }
}

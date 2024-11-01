<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Repositories;

use Sparkair\SparkPlugins\SparkWoo\Common\Models\ModelInterface;
abstract class AbstractRepository
{
    protected static string $model;
    public function all() : array
    {
        $this->checkModel();
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $sql = "SELECT * FROM {$table}";
        $results = $wpdb->get_results($sql, ARRAY_A);
        $posts = [];
        foreach ($results as $result) {
            $posts[] = $this::$model::fromArray($result);
        }
        return $posts;
    }
    public function find($id) : ModelInterface
    {
        $this->checkModel();
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id);
        $result = $wpdb->get_row($sql, ARRAY_A);
        return $this::$model::fromArray($result);
    }
    public function save(ModelInterface $model)
    {
        $this->checkModel();
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $wpdb->insert($table, $model->toArray());
        $model->set('id', $wpdb->insert_id);
        return $model;
    }
    public function update($id, ModelInterface $model)
    {
        $this->checkModel();
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $wpdb->update($table, $model->toArray(), array('id' => $id));
        return $model;
    }
    public function delete($id)
    {
        $this->checkModel();
        global $wpdb;
        $table = esc_sql($this::$model::getTable());
        $wpdb->delete($table, array('id' => $id));
    }
    protected function checkModel()
    {
        if (empty($this::$model)) {
            throw new \Exception('Model not set');
        }
    }
    public static function getTable() : string
    {
        $class = \get_called_class();
        return $class::$model::getTable();
    }
}

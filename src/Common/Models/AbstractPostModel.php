<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common\Models;

abstract class AbstractPostModel implements PostModelInterface, \JsonSerializable
{
    protected const POST_PROPERTIES = array('SKIP_JSON_PROPERTIES', 'POST_PROPERTIES', 'id', 'name', 'status');
    protected const SKIP_JSON_PROPERTIES = array('SKIP_JSON_PROPERTIES', 'POST_PROPERTIES');
    protected int $id = 0;
    protected string $name = '';
    protected string $status = 'publish';
    public function fromArray(array $data) : ?PostModelInterface
    {
        $class = \get_called_class();
        $object = $this->create();
        foreach (\get_class_vars($class) as $name => $_) {
            if (!isset($data[$name])) {
                continue;
            }
            $object->set($name, $data[$name]);
        }
        return $object;
    }
    public function load(int $id) : ?PostModelInterface
    {
        $post = get_post($id);
        if (!\is_a($post, 'WP_Post')) {
            return null;
        }
        if ($post->post_type != $this::postType()) {
            return null;
        }
        return $this->postToObject($post);
    }
    public function postToObject(\WP_Post $post) : ?PostModelInterface
    {
        $class = \get_called_class();
        $meta = get_post_meta($post->ID);
        $object = $this->create();
        $object->set('id', $post->ID);
        $object->set('name', $post->post_title);
        $object->set('status', $post->post_status);
        foreach (\get_class_vars($class) as $name => $_) {
            if (\in_array($name, self::POST_PROPERTIES)) {
                continue;
            }
            if (\in_array($name, self::computedProperties())) {
                continue;
            }
            $rp = new \ReflectionProperty($class, $name);
            $type = $rp->getType()->getName();
            if ('array' === $type) {
                $value = isset($meta[$name][0]) ? \json_decode($meta[$name][0], \true) : array();
            } else {
                if ('string' === $type) {
                    $value = isset($meta[$name][0]) ? \strval($meta[$name][0]) : '';
                } else {
                    if ('int' === $type) {
                        $value = isset($meta[$name][0]) ? \intval($meta[$name][0]) : 0;
                    } else {
                        if ('float' === $type) {
                            $value = isset($meta[$name][0]) ? \floatval($meta[$name][0]) : 0;
                        } else {
                            if ('bool' === $type) {
                                $value = isset($meta[$name][0]) ? \filter_var($meta[$name][0], \FILTER_VALIDATE_BOOLEAN) : 0;
                            }
                        }
                    }
                }
            }
            $object->set($name, $value);
        }
        return $object;
    }
    public function persist() : ?PostModelInterface
    {
        $post_data = array('ID' => $this->id, 'post_type' => $this::postType(), 'post_title' => \wp_strip_all_tags($this->name), 'post_status' => $this->status);
        $this->id = \wp_insert_post($post_data);
        $class = \get_class($this);
        foreach (\get_class_vars($class) as $name => $_) {
            if (\in_array($name, self::POST_PROPERTIES)) {
                continue;
            }
            if (\in_array($name, self::computedProperties())) {
                continue;
            }
            $rp = new \ReflectionProperty($class, $name);
            $type = $rp->getType()->getName();
            $value = $this->get($name);
            if ('array' === $type) {
                $value = \wp_slash(\wp_json_encode($value));
            }
            update_post_meta($this->id, $name, $value);
        }
        return $this;
    }
    public function delete()
    {
        \wp_delete_post($this->id);
    }
    public function jsonSerialize() : array
    {
        $json = array();
        $class = \get_class($this);
        foreach (\get_class_vars($class) as $name => $_) {
            if (\in_array($name, $this::SKIP_JSON_PROPERTIES)) {
                continue;
            }
            $json[$name] = $this->get($name);
        }
        return $json;
    }
    public function set(string $property, $value)
    {
        $value = $this->sanitize($property, $value);
        $value = $this->validate($property, $value);
        $this->{$property} = $value;
    }
    public function get(string $property, $default = null)
    {
        if (null !== $default && !$this->{$property}) {
            return $default;
        }
        return $this->{$property};
    }
    public function sanitize(string $property, $value)
    {
        $class = \get_class($this);
        $rp = new \ReflectionProperty($class, $property);
        $type = $rp->getType()->getName();
        if ('array' === $type) {
            $value = $this->sanitizeArrayProperty($property, $value);
        } else {
            if ('string' === $type) {
                $value = sanitize_text_field($value);
            } else {
                if ('int' === $type) {
                    $value = \filter_var($value, \FILTER_VALIDATE_INT);
                } else {
                    if ('float' === $type) {
                        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);
                    } else {
                        if ('bool' === $type) {
                            $value = \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                }
            }
        }
        return $value;
    }
    public function validate(string $property, $value)
    {
        if ('status' === $property) {
            if (!\in_array($value, \array_keys(get_post_statuses()))) {
                $value = '';
            }
        }
        return $value;
    }
    public static function computedProperties() : array
    {
        return array();
    }
}

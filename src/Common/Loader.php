<?php

namespace Sparkair\SparkPlugins\SparkWoo\Common;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 */
class Loader
{
    /**
     * The array of actions registered with WordPress.
     *
     * @var array $actions The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;
    /**
     * The array of actions to remove from WordPress.
     *
     * @var array $actionsRemoved The actions to be removd.
     */
    protected $actionsRemoved;
    /**
     * The array of filters registered with WordPress.
     *
     * @var array $filters The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;
    /**
     * The array of filters to remove from WordPress.
     *
     * @var array $filtersRemoved The filters to be removd.
     */
    protected $filtersRemoved;
    /**
     * Initialize the collections used to maintain the actions and filters.
     */
    public function __construct()
    {
        $this->actions = array();
        $this->actionsRemoved = array();
        $this->filters = array();
        $this->filtersRemoved = array();
    }
    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook The name of the WordPress action that is being registered.
     * @param object $component A reference to the instance of the object on which the action is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function addAction($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        if (!$hook) {
            return;
        }
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    public function removeAction($hook, $component, $callback, $priority = 10)
    {
        if (!$hook) {
            return;
        }
        $this->actionsRemoved = $this->remove($this->actionsRemoved, $hook, $component, $callback, $priority);
    }
    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook The name of the WordPress filter that is being registered.
     * @param object $component A reference to the instance of the object on which the filter is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function addFilter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        if (!$hook) {
            return;
        }
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    public function removeFilter($hook, $component, $callback, $priority = 10)
    {
        if (!$hook) {
            return;
        }
        $this->filtersRemoved = $this->remove($this->filtersRemoved, $hook, $component, $callback, $priority);
    }
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @param array $hooks The collection of hooks that is being registered (that is, actions or filters).
     * @param string $hook The name of the WordPress filter that is being registered.
     * @param object $component A reference to the instance of the object on which the filter is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority The priority at which the function should be fired.
     * @param int $accepted_args The number of arguments that should be passed to the $callback.
     * @return array The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = array('hook' => $hook, 'component' => $component, 'callback' => $callback, 'priority' => $priority, 'accepted_args' => $accepted_args);
        return $hooks;
    }
    private function remove($hooks, $hook, $component, $callback, $priority)
    {
        $hooks[] = array('hook' => $hook, 'component' => $component, 'callback' => $callback, 'priority' => $priority);
        return $hooks;
    }
    /**
     * Register the filters and actions with WordPress.
     */
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
        foreach ($this->filtersRemoved as $hook) {
            remove_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority']);
        }
        foreach ($this->actionsRemoved as $hook) {
            remove_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority']);
        }
    }
}

<?php

/*
 * This file is part of Fixhub.
 *
 * Copyright (C) 2016 Fixhub.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixhub\Services\Settings;

use Fixhub\Models\Setting;

/**
 * This is the settings repository class.
 *
 */
class Repository
{
    /**
     * The eloquent model instance.
     *
     * @var \Fixhub\Models\Setting
     */
    protected $model;

    /**
     * Is the config state stale?
     *
     * @var bool
     */
    protected $stale = false;

    /**
     * Create a new settings repository instance.
     *
     * @param \Fixhub\Models\Setting $model
     *
     * @return void
     */
    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    /**
     * Returns a setting from the database.
     *
     * @return array
     */
    public function all()
    {
        return $this->model->all(['name', 'value'])->pluck('value', 'name')->toArray();
    }

    /**
     * Updates a setting value.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return void
     */
    public function set($name, $value)
    {
        $this->stale = true;

        if ($value === null) {
            $this->model->where('name', $name)->delete();
        } else {
            $this->model->updateOrCreate(compact('name'), compact('value'));
        }
    }

    /**
     * Get a setting, or the default value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($setting = $this->model->where('name', $name)->first()) {
            return $setting->value;
        }

        return $default;
    }

    /**
     * Deletes a setting.
     *
     * @param string $name
     *
     * @return void
     */
    public function delete($name)
    {
        $this->stale = true;

        $this->model->where('name', $name)->delete();
    }

    /**
     * Clear all settings.
     *
     * @return void
     */
    public function clear()
    {
        $this->stale = true;

        $this->model->query()->delete();
    }

    /**
     * Is the config state stale?
     *
     * @return bool
     */
    public function stale()
    {
        return $this->stale;
    }
}

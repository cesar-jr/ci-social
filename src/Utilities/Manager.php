<?php

namespace CesarJr\Social\Utilities;

use InvalidArgumentException;

abstract class Manager
{
    /**
     * The array of created "drivers".
     * @var \CesarJr\Social\Providers\AbstractProvider[]
     */
    protected $drivers = [];

    /**
     * Get a driver instance.
     *
     * @param  string|null  $driver
     *
     * @throws \InvalidArgumentException
     */
    public function driver($driver = null)
    {
        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].',
                static::class
            ));
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     *
     * @template TProvider of \CesarJr\Social\Providers\AbstractProvider
     * @param  string  $driver
     * @return TProvider
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        $method = 'create' . Str::studly($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }
}

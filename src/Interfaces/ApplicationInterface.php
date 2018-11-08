<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */

namespace Spark\Framework\Interfaces;

use Psr\Container\ContainerInterface;
use Spark\Framework\Helper\DotArray;

interface ApplicationInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @param $id
     * @return mixed
     */
    public function get($id);

    /**
     * @param $configPath
     * @return $this
     */
    public function loadConfig($configPath);

    /**
     * @param $configPath
     * @return $this
     */
    public function readConfig($configPath);

    /**
     * @return DotArray
     */
    public function getSettings();

    /**
     * @return $this
     */
    public function bootstrap();
}

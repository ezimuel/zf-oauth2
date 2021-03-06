<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Adapter\MongoAdapter;
use ZF\OAuth2\Controller\Exception;

/**
 * Class MongoAdapterFactory
 *
 * @package ZF\OAuth2\Factory
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     * @return \ZF\OAuth2\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config  = $services->get('Config');

        $dbLocatorName = isset($config['zf-oauth2']['mongo']['locator_name'])
            ? $config['zf-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($services->has($dbLocatorName)) {
            $connection = $services->get($dbLocatorName);
        } else {
            if (!isset($config['zf-oauth2']['mongo']) || empty($config['zf-oauth2']['mongo']['database'])) {
                throw new Exception\RuntimeException(
                    'The database configuration [\'zf-oauth2\'][\'mongo\'] for OAuth2 is missing'
                );
            }

            $server     = isset($config['zf-oauth2']['mongo']['dsn']) ? $config['zf-oauth2']['mongo']['dsn'] : null;
            $mongo      = new \MongoClient($server, array('connect' => false));
            $connection = $mongo->{$config['zf-oauth2']['mongo']['database']};
        }

        $oauth2ServerConfig = array();
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        return new MongoAdapter($connection, $oauth2ServerConfig);
    }
}

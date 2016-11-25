<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Invertus\Brad\Config\Setting;

/**
 * Class ClientBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class ClientBuilder
{
    /**
     * @var \Core_Business_ConfigurationInterface
     */
    private $configuration;

    /**
     * ClientBuilder constructor.
     *
     * @param \Core_Business_ConfigurationInterface $configuration
     */
    public function __construct(\Core_Business_ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Build elasticserach client
     *
     * @return \Elasticsearch\Client
     */
    public function buildClient()
    {
        $elasticsearchHost1 = $this->configuration->get(Setting::ELASTICSEARCH_HOST_1);

        if (false === strpos($elasticsearchHost1, 'http://') &&
            false === strpos($elasticsearchHost1, 'https://')
        ) {
            $elasticsearchHost1 = 'http://'.$elasticsearchHost1;
        }

        $hosts = [$elasticsearchHost1];

        $clientBuilder = \Elasticsearch\ClientBuilder::create();
        $clientBuilder->setHosts($hosts);
        $client = $clientBuilder->build();

        return $client;
    }
}
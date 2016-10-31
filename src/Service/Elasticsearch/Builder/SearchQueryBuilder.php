<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Context;
use Core_Business_ConfigurationInterface;
use Invertus\Brad\Config\Setting;

/**
 * Class SearchQueryBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class SearchQueryBuilder extends AbstractQueryBuilder
{
    /**
     * @var Core_Business_ConfigurationInterface
     */
    private $configuration;

    /**
     * SearchQueryBuilder constructor.
     *
     * @param Core_Business_ConfigurationInterface $configuration
     * @param Context $context
     */
    public function __construct(Core_Business_ConfigurationInterface $configuration, Context $context)
    {
        parent::__construct($context);

        $this->configuration = $configuration;
    }

    /**
     * Build search query
     *
     * @param string $query
     * @param int|null $from
     * @param int|null $size
     * @param string|null $orderBy
     * @param string|null $orderWay
     *
     * @return array
     */
    public function buildProductsQuery($query, $from = null, $size = null, $orderBy = null, $orderWay = null)
    {
        $idLang = (int) $this->context->language->id;
        $isFuzzySeearchEnabled = (bool) $this->configuration->get(Setting::FUZZY_SEARCH);

        $elasticsearchQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match_phrase_prefix' => [
                                'name_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 3,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'description_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'short_description_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'manufacturer_name' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'reference' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'category_name' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($isFuzzySeearchEnabled) {
            $elasticsearchQuery['query']['bool']['should'][] = [
                'multi_match' => [
                    'fields' => ['name_lang_'.$idLang, 'category_name'],
                    'query' => $query,
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                    'max_expansions' => 15,
                ],
            ];
        }

        if (null !== $from) {
            $elasticsearchQuery['from'] = (int) $from;
        }

        if (null !== $size) {
            $elasticsearchQuery['size'] = (int) $size;
        }

        if (null !== $orderBy && null != $orderWay) {
            $elasticsearchQuery['sort'] = $this->buildOrderQuery($orderBy, $orderWay);
        }

        return $elasticsearchQuery;
    }
}

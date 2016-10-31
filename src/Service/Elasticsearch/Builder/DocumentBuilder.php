<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Attribute;
use Category;
use Core_Foundation_Database_EntityManager;
use Country;
use Currency;
use Feature;
use FeatureValue;
use Group;
use Language;
use Link;
use Manufacturer;
use Product;
use Shop;
use StockAvailable;

/**
 * Class DocumentBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class DocumentBuilder
{
    /**
     * @var Link
     */
    private $link;

    /**
     * DocumentBuilder constructor.
     *
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Build product fields for indexing
     *
     * @param Product $product
     *
     * @return array
     */
    public function buildProductBody(Product $product)
    {
        $body = [];
        $body['id_product'] = $product->id;
        $body['id_supplier'] = $product->id_supplier;
        $body['id_manufacturer'] = $product->id_manufacturer;
        $body['manufacturer_name'] = Manufacturer::getNameById($product->id_manufacturer);
        $body['id_category_default'] = $product->id_category_default;
        $body['on_sale'] = $product->on_sale;
        $body['ean13'] = $product->ean13;
        $body['reference'] = $product->reference;
        $body['upc'] = $product->upc;
        $body['price'] = $product->price;
        $body['show_price'] = $product->show_price;
        $body['quantity'] = $product->quantity;
        $body['customizable'] = $product->customizable;
        $body['minimal_quantity'] = $product->minimal_quantity;
        $body['available_for_order'] = $product->available_for_order;
        $body['condition'] = $product->condition;
        $body['weight'] = $product->weight;
        $body['out_of_stock'] = $product->out_of_stock;
        $body['is_virtual'] = $product->is_virtual;
        $body['on_sale'] = $product->on_sale;
        $body['id_image'] = Product::getCover($product->id)['id_image'];
        $body['id_combination_default'] = $product->getDefaultIdProductAttribute();
        $body['categories'] = $product->getCategories();
        $body['total_quantity'] = StockAvailable::getQuantityAvailableByProduct($product->id);

        $defaultCategory = new Category($product->id_category_default);

        foreach ($product->name as $idLang => $name) {
            $body['name_lang_'.$idLang] = $name;
            $body['description_lang_'.$idLang] = $product->description[$idLang];
            $body['short_description_lang_'.$idLang] = $product->description_short[$idLang];
            $body['link_rewrite_lang_'.$idLang] = $product->link_rewrite[$idLang];
            $body['link_lang_'.$idLang] = $this->link->getProductLink($product, $product->link_rewrite[$idLang]);
            $body['default_category_name_lang_'.$idLang] = $defaultCategory->name[$idLang];
        }

        $features = $product->getFeatures();
        $attributes = Product::getAttributesInformationsByProduct($product->id);

        if ($features) {
            foreach ($features as $feature) {
                $featureObj = new Feature($feature['id_feature']);
                $featureValueObj = new FeatureValue($feature['id_feature_value']);

                foreach ($featureObj->name as $idLang => $name) {
                    $body['feature_'.$featureObj->id.'_lang_'.$idLang] = $name;
                    $body['feature_value_'.$featureValueObj->id.'_lang_'.$idLang] = $featureValueObj->value[$idLang];
                }

                $body['feature_'.$featureObj->id] = $featureValueObj->id;
            }
        }

        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeObj = new Attribute($attribute['id_attribute']);

                foreach ($attributeObj->name as $idLang => $name) {
                    $body['attribute_' . $attributeObj->id . '_lang_' . $idLang] = $name;
                }

                $body['attribute_group_' . $attribute['id_attribute_group']][] = $attributeObj->id;
            }
        }

        return $body;
    }

    /**
     * Build product prices body
     *
     * @param Product $product
     * @param int $idShop
     * @param bool $useTax
     * @param array $groups
     * @param array $currencies
     * @param array $countries
     *
     * @return array
     */
    public function buildProductPriceBody(Product $product, $idShop, $useTax, array $groups, array $currencies, array $countries)
    {
        $body = [];

        // Must be passed to price calculation
        $specificPrice = null;

        foreach ($groups as $idGroup) {
            foreach ($countries as $idCountry) {
                foreach ($currencies as $idCurrency) {
                    $body['price_group_'.$idGroup.'_country_'.$idCountry.'_currency_'.$idCurrency] =
                        Product::priceCalculation(
                            $idShop,
                            $product->id,
                            null,
                            $idCountry,
                            null,
                            null,
                            $idCurrency,
                            $idGroup,
                            $product->minimal_quantity,
                            $useTax,
                            6,
                            false,
                            true,
                            true,
                            $pr,
                            true
                        );
                }
            }
        }

        return $body;
    }
}

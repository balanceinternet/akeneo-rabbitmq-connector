<?php

namespace Balance\Bundle\RabbitMQBundle\RabbitMQ\Normalizer;

use Pim\Bundle\TransformBundle\Normalizer\Structured\ProductNormalizer as BaseProductNormalizer;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends BaseProductNormalizer
{
    const FIELD_FAMILY = 'attribute_set';
    /** @var array */
    protected $supportedFormats = ['rabbit_json'];

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context['entity'] = 'product';
        $data = [];

        if (isset($context['resource'])) {
        //    $data['resource'] = $context['resource'];
        }

        $data[self::FIELD_FAMILY]        = $product->getFamily() ? $product->getFamily()->getCode() : null;
        //$data[self::FIELD_GROUPS]        = $this->getGroups($product);
        //$data[self::FIELD_VARIANT_GROUP] = $product->getVariantGroup() ? $product->getVariantGroup()->getCode() : null;
        //$data[self::FIELD_CATEGORY]      = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED]       = $product->isEnabled();
        $data[self::FIELD_VALUES]        = $this->normalizeValues($product->getValues(), $format, $context);
        $data[self::FIELD_ASSOCIATIONS]  = $this->normalizeAssociations($product->getAssociations());

        $data['created_at']     = $product->getCreated()->format('Y-m-d\TH:i:sP');
        $data['modified_at']    = $product->getUpdated()->format('Y-m-d\TH:i:sP');
        $data['attribute_set']  = 4;
        $data['id']             = $product->getIdentifier()->getData();
        $data['sku']            = $product->getIdentifier()->getData();

        if ($data[self::FIELD_VALUES]['visibility']['0']['visibility'] == 'catalog_search') {
            $data['visibility'][0]  = "catalog";
            $data['visibility'][1]  = "search";
        } else {
            $data['visibility'][0] = $data[self::FIELD_VALUES]['visibility']['0']['visibility'];
        }

        $data['name'][0]['value']   = $data[self::FIELD_VALUES]['name']['0'];
        $data['name'][0]['channel'] = NULL;
        $data['name'][0]['locale']  = NULL;

        $data['description'][0]['value']   = $data[self::FIELD_VALUES]['description']['0'];
        $data['description'][0]['channel'] = NULL;
        $data['description'][0]['locale']  = NULL;

        unset($data[self::FIELD_VALUES]);
        unset($data['description']);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize associations
     *
     * @param Association[] $associations
     */
    protected function normalizeAssociations($associations = [])
    {
        $data = [];
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();


            $products = [];
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier()->getData();
            }

            $data[] = [
                'products' => $products,
                'type' => $columnPrefix
            ];
        }
        return $data;
    }

}

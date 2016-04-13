<?php
namespace Balance\Bundle\RabbitMQBundle\RabbitMQ\Normalizer;

use Pim\Bundle\TransformBundle\Normalizer\Flat\ProductValueNormalizer as BaseProductValueNormalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer extends BaseProductValueNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['rabbit_json'];

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $data = $entity->getData();
        $fieldName = $this->getFieldValue($entity);
        if ($this->filterLocaleSpecific($entity)) {
            return [];
        }

        $result = null;

        if (is_array($data)) {
            $data = new ArrayCollection($data);
        }

        $type = $entity->getAttribute()->getAttributeType();

        if (AttributeTypes::BOOLEAN === $type) {
            $result = [$fieldName => (string) (int) $data];
        } elseif (is_null($data)) {
            $result = [$fieldName => ''];
        } elseif (is_int($data)) {
            $result = [$fieldName => (string) $data];
        } elseif (is_float($data) || 'decimal' === $entity->getAttribute()->getBackendType()) {
            $pattern = $entity->getAttribute()->isDecimalsAllowed() ? sprintf('%%.%sF', $this->precision) : '%d';
            $result = [$fieldName => sprintf($pattern, $data)];
        } elseif (is_string($data)) {
            $result = $data;
        } elseif (is_object($data)) {
            // TODO: Find a way to have proper currency-suffixed keys for normalized price data
            // even when an empty collection is passed
            $backendType = $entity->getAttribute()->getBackendType();
            if ('prices' === $backendType && $data instanceof Collection && $data->isEmpty()) {
                $result = [];
            } elseif ('options' === $backendType && $data instanceof Collection && $data->isEmpty() === false) {
                $data = $this->sortOptions($data);
                $context['field_name'] = $fieldName;
                $result = $this->serializer->normalize($data, 'flat', $context);
            } else {
                $context['field_name'] = $fieldName;
                if ('metric' === $backendType) {
                    $context['decimals_allowed'] = $entity->getAttribute()->isDecimalsAllowed();
                } elseif ('media' === $backendType) {
                    $context['value'] = $entity;
                }

                $result = $this->serializer->normalize($data, 'flat', $context);
            }
        }
        if (null === $result) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot normalize product value "%s" which data is a(n) "%s"',
                    $fieldName,
                    is_object($data) ? get_class($data) : gettype($data)
                )
            );
        }

        $localizer = $this->localizerRegistry->getLocalizer($type);
        if (null !== $localizer) {
            foreach ($result as $field => $data) {
                $result[$field] = $localizer->localize($data, $context);
            }
        }

        return $result;
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        // TODO : should be extracted
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
        //    $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
        //    $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode() . $suffix;
    }

}

<?php
namespace Balance\Bundle\RabbitMQBundle\Processor;

use Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor as BaseProductToFlatArrayProcessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Process a product to an array
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends BaseProductToFlatArrayProcessor
{

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $contextChannel = $this->channelManager->getChannelByCode($this->channel);
        $this->productBuilder->addMissingProductValues($product, [$contextChannel],
            $contextChannel->getLocales()->toArray());

//        $data['media'] = [];
//        $mediaValues   = $this->getMediaProductValues($product);
//
//        foreach ($mediaValues as $mediaValue) {
//            $data['media'][] = $this->serializer->normalize(
//                $mediaValue->getMedia(),
//                'flat',
//                ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
//            );
//        }

        $data['product'] = $this->serializer->normalize($product, 'rabbit_json', $this->getNormalizerContext());
        
        return json_encode($data);
    }

}

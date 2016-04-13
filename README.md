Balance RabbitMQ Product Export Bundle - Overview
=================================================
This is a prototype bundle for demonstrating how Akeneo PIM could communicate with RabbitMQ, specifically for trialing the JSON over AMQP integration for Magento Commerce and Order Management Systems.

This bundle is created to export product data in JSON format based on the Magento defined template.

more information can be found at [Magento DevDocs](http://devdocs.magento.com/guides/v2.0/config-guide/mq/rabbitmq-overview.html)

How does it work? 
=================
The bundle generates a message about a single product in the predefined format and pushes the message 
to the queue. The relevant configuration for the queue is set in the export profile.

What is the format?
===================
```
{
    "product": {
        "associations": [
            {
                "products": [
                    "WB-WD7S",
                    "WB-WD10S"
                ],
                "type": "upsell"
            },
            {
                "products": [
                    "WB-WD7S",
                    "WB-WD10S"
                ],
                "type": "crosssell"
            }
        ],
        "attribute_set": "4",
        "created_at": "2016-03-03T08:56:46+00:00",
        "enabled": true,
        "id": "WB-WD13S",
        "modified_at": "2016-03-03T08:58:33+00:00",
        "name": [
            {
                "channel": null,
                "locale": null,
                "value": "Washburn WD13S"
            }
        ],
        "sku": "WB-WD13S",
        "visibility": [
            "catalog",
            "search"
        ]
    }
}
```

How to define what fields should be exported?
=============================================
Array list can be managed in the file Balance/Bundle/RabbitMQBundle/RabbitMQ/Normalizer/ProductNormalizer.php

Installation
============
 1. Put the code into "scr" folder Install 
 2. Add to composer.json "php-amqplib/rabbitmq-bundle" and run composer update.
 3. Enable the bundle in the app/AppKernel.php file:
```
    public function registerBundles()
    {
        $bundles = [
            new Balance\Bundle\RabbitMQBundle\BalanceRabbitMQBundle(),
        ]
        return $bundles;
    }
```
4. Clear cache

5. Go to "Export profiles" and create a new export using "Products Export RabbitMQ" connector.

6. Configure the export profile, the variables responsible for RabbitMQ connection are:
 
 
 * AMQP Host
 * AMQP Port
 * AMQP Username
 * AMQP Password
 * AMQP Virtualhost
 * AMQP Exchange
 * AMQP Route-key
 * AMQP Header "to"


Run the export profile and see how your messages appears in the RabbitMQ UI.

Roadmap
=======
1. Create more flexible template.
2. Adjust default template once Magento can support more fields.
3. Create export profiles for other entities like categories, attributes, attribute sets etc.
4. Add media support.

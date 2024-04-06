define(function () {
    return {
        templates: {
            // Docs https://support.google.com/merchants/answer/7052112?hl=ru
            google_shopping: function() {
                return  "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" +
                    "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n" +
                    "  <channel>\n" +
                    "    <title>Data feed Example</title>\n" +
                    "    <link>{{ template.web_url }}</link>\n" +
                    "    <description>Data feed description.</description>\n" +
                    "    {% for product in set %}\n" +
                    "       <item>\n" +
                                // From 1 to 50 chars. SKU code is recommended. Required.
                    "           <g:id><![CDATA[{{ product.sku | substr(0, 50)}}]]></g:id>\n" +

                                // From 1 to 150 chars. Required.
                    "           <g:title><![CDATA[{{ product.name | substr(0, 150) }}]]></g:title>\n" +

                                // From 1 to 5000 chars. HTML tags should be encoded. Required.
                    "           <g:description><![CDATA[{{ product.description | htmlspecialchars() }}]]></g:description>\n" +

                                // From 1 to 2000 chars. Absolute url. Required.
                    "           <link><![CDATA[{{ template.web_url }}{{ product.url_key }}]]></link>\n" +

                                // From 1 to 2000 chars. Only gif, jpg/.jpeg, png, bmp, tif/tiff. Required.
                    "           <g:image_link><![CDATA[{{ template.media_url }}catalog/product{{ product.base_image }}]]></g:image_link>\n" +

                                // Max "additional_image_link" 10 rows. Rules the same as for image_link. Optional.
                    "           {% for image in set %}\n" +
                    "               <g:additional_image_link><![CDATA[{{ template.media_url }}catalog/product{{ src }}]]></g:additional_image_link>\n" +
                    "           {% endforImage %}\n" +

                                // Same rules as for "link". Optional.
                    "           <g:mobile_link><![CDATA[{{ template.web_url }}{{ product.url_key }}]]></g:mobile_link>\n" +

                                // Can be: "in stock", "out of stock", "preorder". Required.
                    "           {% if product.is_in_stock == 1 %}\n" +
                    "               <g:availability><![CDATA[in stock]]></g:availability>\n" +
                    "           {% endif %}\n" +
                    "           {% if product.is_in_stock == 0 %}\n" +
                    "               <g:availability><![CDATA[out of stock]]></g:availability>\n" +
                    "           {% endif %}\n" +

                                // Product price. Currency format ISO 4217. Example 1500.00 RUB. Required.
                    "           <g:price>{{ product.price }} {{ template.store_currency }}</g:price>\n" +

                                // Category id or category named path. List of values is predefined by google:
                                // https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt
                                // There can be only one category per product. We should pick the most suitable
                    "           <g:google_product_category>{{ product.categories }}</g:google_product_category>\n" +

                                // From 1 to 70 chars. Required.
                    "           <g:brand><![CDATA[{{ product.name }}]]></g:brand>\n" +

                                // From 1 to 70 chars. Required for products without gtin.
                    "           <g:mpn><![CDATA[{{ product.sku }}]]></g:mpn>\n" +

                                // Valid values: "new", "refurbished", "used". Optional for new products
                    "           <g:condition>new</g:condition>\n" +
                    "       </item>\n" +
                    "    {% endforProduct %}\n" +
                    "  </channel>\n" +
                    "</rss>";
            },

            // Docs https://developer.ebay.com/devzone/merchant-products/mipng//quick-start/content/user-guide/sample-files.html
            // Template https://developer.ebay.com/devzone/merchant-products/mip-samples/ebay-feed-xml/product-combined-compatibility.xml
            // Info upload feeds: https://developer.ebay.com/devzone/merchant-products/mipng/user-guide-en/default.html#getting-started.html#MIP
            ebay_shopping: function() {
                return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" +
                        "<productRequest>\n" +
                        "    {% for product in set %}\n" +
                        "       <product>\n" +
                        "           <SKU>{{ product.sku | substr(0, 50)}}</SKU>\n" +
                        "           <productInformation localizedFor=\"en_US\">\n" +
                        "               <title>{{ product.name | substr(0, 150) }}</title>\n" +
                        "               <subtitle></subtitle>\n" +
                        "               <description>\n" +
                        "                   <productDescription><![CDATA[{{product.description | htmlspecialchars() }}]]></productDescription>\n" +
                        "                   <additionalInfo></additionalInfo>\n" +
                        "               </description>\n" +
                        "               <UPC>716838224876</UPC>\n" +
                        "               <ISBN></ISBN>\n" +
                        "               <EAN></EAN>\n" +
                        "               <MPN></MPN>\n" +
                        "               <Brand>Adidas</Brand>\n" +
                        "               <ePID></ePID>\n" +
                        "               <pictureURL>{{ template.media_url }}catalog/product{{ product.base_image }}</pictureURL>\n" +
                        "               <conditionInfo>\n" +
                        "                   <condition>New</condition>\n" +
                        "                   <conditionDescription>New</conditionDescription>\n" +
                        "               </conditionInfo>\n" +
                        "               <shippingDetails measurementSystem=\"ENGLISH\">\n" +
                        "                   <weightMajor>{{ product.weight }}</weightMajor>\n" +
                        "                   <weightMinor>{{ product.weight }}</weightMinor>\n" +
                        "                   <length>{{ product.ts_dimensions_length }}</length>\n" +
                        "                   <width>{{ product.ts_dimensions_width }}</width>\n" +
                        "                   <height>{{ product.ts_dimensions_height }}</height>\n" +
                        "                   <packageType></packageType>\n" +
                        "               </shippingDetails>\n" +
                        "           </productInformation>\n" +
                        "           <inventory>\n" +
                        "               <totalShipToHomeQuantity>{{ product.qty }}</totalShipToHomeQuantity>\n" +
                        "           </inventory>\n" +
                        "       </product>\n" +
                        "    {% endforProduct %}\n" +
                        "</productRequest>\n";
            },

            // https://www.bronco.co.uk/our-ideas/using-the-php-amazon-api-to-update-stock/
            amazon_marketplace: function() {
                return "<?xml version=\"1.0\"?>\n" +
                    "<AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">\n" +
                    "    <Header>\n" +
                    "        <DocumentVersion>1.01</DocumentVersion>\n" +
                    "        <MerchantIdentifier>XXXXXXXX</MerchantIdentifier>\n" +
                    "    </Header>\n" +
                    "    <MessageType>Product</MessageType>\n" +
                    "    <PurgeAndReplace>false</PurgeAndReplace>\n" +
                    "    {% for product in set %}\n" +
                    "       <Message>\n" +
                    "           <MessageID>{{ product.product_id }}</MessageID>\n" +
                    "           <OperationType>Update</OperationType>\n" +
                    "           <Product>\n" +
                    "               <SKU><![CDATA[{{ product.sku }}]]></SKU>\n" +
                                    // Tax codes information: https://help.sellercloud.com/article/tRlMiWthAB-amazon-tax-code
                    "               <ProductTaxCode>A_GEN_TAX</ProductTaxCode>\n" +
                    "               <LaunchDate>{{ template.date }}T{{ template.time }}</LaunchDate>\n" +
                    "               <Condition>\n" +
                    "                   <ConditionType>New</ConditionType>\n" +
                    "               </Condition>\n" +
                    "               <DescriptionData>\n" +
                    "                   <Title><![CDATA[{{ product.name}}]]></Title>\n" +
                    "                   {% if product.manufacturer %}\n" +
                    "                       <Brand><![CDATA[{{ product.manufacturer }}]]></Brand>\n" +
                    "                   {% endif %}\n" +
                    "                   {% if empty(product.manufacturer) %}\n" +
                    "                       <Brand>Default</Brand>\n" +
                    "                   {% endif %}\n" +
                    "                   <Description>{{ product.description | strip_tags }}</Description>\n" +
                    "                   <BulletPoint>Best seller {{ product.name}}</BulletPoint>\n" +
                    "                   {% if product.manufacturer %}\n" +
                    "                       <BulletPoint>Made by {{ product.manufacturer }}</BulletPoint>\n" +
                    "                   {% endif %}\n" +
                    "                   <ItemDimensions>\n" +
                    "                       <Weight unitOfMeasure=\"LB\">{{ product.weight | ceil }}</Weight>\n" +
                    "                   </ItemDimensions>\n" +
                    "                   <MSRP currency=\"CAD\">{{ product.price }}</MSRP>\n" +
                    "                   {% if product.manufacturer %}\n" +
                    "                       <Manufacturer><![CDATA[{{ product.manufacturer }}]]></Manufacturer>\n" +
                    "                   {% endif %}\n" +
                    "                   {% if empty(product.manufacturer) %}\n" +
                    "                       <Manufacturer><![CDATA[Default]]></Manufacturer>\n" +
                    "                   {% endif %}\n" +
                    "                   <SearchTerms><![CDATA[{{ product.meta_keywords }}]]></SearchTerms>\n" +
                    "                   <ItemType>handmade-rugs</ItemType>\n" +
                    "                   <OtherItemAttributes>Rectangular</OtherItemAttributes>\n" +
                    "                   <TargetAudience>Adults</TargetAudience>\n" +
                    "                   <TargetAudience>Children</TargetAudience>\n" +
                    "                   <TargetAudience>Men</TargetAudience>\n" +
                    "                   <TargetAudience>Women</TargetAudience>\n" +
                    "               </DescriptionData>\n" +
                    "               <ProductData>\n" +
                    "               </ProductData>\n" +
                    "           </Product>\n" +
                    "       </Message>\n" +
                    "    {% endforProduct %}\n" +
                    "</AmazonEnvelope>";
            },

            // https://www.bronco.co.uk/our-ideas/using-the-php-amazon-api-to-update-stock/
            amazon_inventory: function() {
                return "<AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">\n" +
                    "    <Header>\n" +
                    "        <DocumentVersion>1.01</DocumentVersion>\n" +
                    "        <MerchantIdentifier>M_SELLER_123456</MerchantIdentifier>\n" +
                    "    </Header>\n" +
                    "    <MessageType>Inventory</MessageType>\n" +
                    "    {% for product in set %}\n" +
                    "       <Message>\n" +
                    "           <MessageID>{{ product.product_id }}</MessageID>\n" +
                    "           <OperationType>Update</OperationType>\n" +
                    "           <Inventory>\n" +
                    "               <SKU><![CDATA[{{ product.sku }}]]></SKU>\n" +
                    "               <Quantity>{{ product.qty }}.0</Quantity>\n" +
                    "               <FulfillmentLatency>3</FulfillmentLatency>\n" +
                    "           </Inventory>\n" +
                    "       </Message>\n" +
                    "    {% endforProduct %}\n" +
                    "</AmazonEnvelope>\n";
            },

            amazon_ads: function() {
                return "Category\tTitle\tLink\tSKU\tPrice\tBrand\tDepartment\tUPC\tImage\tDescription\tManufacturer\tMfr part number\tAge\tColor\tShipping Weight\tSize\n" +
                    "{% for product in set %}\n" +
                    "   {{ product.categories }}\t{{ product.name }}\t{{ template.web_url }}{{ product.url_key }}\t{{ product.sku }}\t{{ product.price }}\t{{ product.manufacturer }}\t\t\t{{ template.media_url }}catalog/product{{ product.base_image }}\t{{ product.description | removeLineBreaks() }}\t{{ product.manufacturer }}\t\t\t{{ product.color }}\t{{ product.weight }}\t{{ product.size }}\n" +
                    "{% endforProduct %}\n";
            },

            facebook_shopping: function() {
                return "<?xml version=\"1.0\"?>\n" +
                       "    <rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n" +
                       "        <channel>\n" +
                       "        <title>Test Store</title>\n" +
                       "        <link>http://www.example.com</link>\n" +
                       "        <description>An example item from the feed</description>\n" +
                       "            {% for product in set %}\n" +
                       "            <item>\n" +
                       "                <g:id>{{ product.sku }}</g:id>\n" +
                       "                <g:title>{{ product.name | substr(0, 150) }}</g:title>\n" +
                       "                <g:description><![CDATA[{{ product.description | htmlspecialchars() }}]]></g:description>\n" +
                       "                <g:link>{{ template.web_url }}{{ product.url_key }}</g:link>\n" +
                       "                <g:image_link>{{ template.media_url }}catalog/product{{ product.base_image }}</g:image_link>\n" +
                       "                <g:brand>{{ product.brand }}</g:brand>\n" +
                       "                <g:gtin/>\n" +
                       "                <g:mpn>{{ product.mpn }}</g:mpn>\n" +
                       "                <g:condition>new</g:condition>\n" +
                       "                {% if product.is_in_stock == 1 %}\n" +
                       "                    <g:availability>in stock</g:availability>\n" +
                       "                {% endif %}\n" +
                       "                {% if product.is_in_stock == 0 %}\n" +
                       "                    <g:availability>out of stock</g:availability>\n" +
                       "                {% endif %}\n" +
                       "                <g:price>{{ product.price }}</g:price>\n" +
                       "                <g:shipping>\n" +
                       "                    <g:country>US</g:country>\n" +
                       "                    <g:service>{{ product.shipment_type }}</g:service>\n" +
                       "                    <g:price/>\n" +
                       "                </g:shipping>\n" +
                       "                <g:google_product_category>{{ product.categories | str_replace(&,&#38;) }}</g:google_product_category>\n" +
                       "                <g:custom_label_0>Made in Waterford, IE</g:custom_label_0>\n" +
                       "            </item>\n" +
                       "            {% endforProduct %}\n" +
                       "        </channel>\n" +
                       "    </rss>\n";
            },

            yandex: function() {
                return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" +
                       "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" +
                       "<yml_catalog date=\"2020-06-10 07:01\">\n" +
                       "  <shop>\n" +
                       "    <name/>\n" +
                       "    <company/>\n" +
                       "    <url/>\n" +
                       "    <email/>\n" +
                       "    <currencies>\n" +
                       "      <currency id=\"USD\" rate=\"1\"/>\n" +
                       "    </currencies>>\n" +
                       "    <categories>\n" +
                       "      <category id=\"1\" parentId=\"0\">Catalog</category>\n" +
                       "      <category id=\"111\" parentId=\"0\">Catalog</category>\n" +
                       "    </categories>\n" +
                       "    <local_delivery_cost>0</local_delivery_cost>\n" +
                       "    <offers>\n" +
                       "     {% for product in set %}" +
                       "      <offer {% if product.is_in_stock == 1 %}available=\"true\"{% endif %} {% if product.is_in_stock == 0 %}available=\"false\" {% endif %}id=\"{{ product.product_id }}\">\n" +
                       "        <name>{{ product.name }}</name>\n" +
                       "        <url>{{ template.web_url }}{{ product.url_key }}</url>\n" +
                       "        <price>{{ product.price }}</price>\n" +
                       "        <currencyId>RUR</currencyId>\n" +
                       "        <categoryId>111</categoryId>\n" +
                       "        <picture>{{ template.media_url }}catalog/product{{ product.base_image }}</picture>\n" +
                       "        <pickup>false</pickup>\n" +
                       "        <store>false</store>\n" +
                       "        <delivery>true</delivery>\n" +
                       "        <vendor/>\n" +
                       "        <model/>\n" +
                       "        <description>{{ product.description | htmlspecialchars() }}</description>\n" +
                       "        <market_category>{{ product.categories }}</market_category>\n" +
                       "      </offer>\n" +
                       "     {% endforProduct %}" +
                       "    </offers>\n" +
                       "  </shop>\n" +
                       "</yml_catalog>\n";
            }
        },

        // Variable modifiers
        modifiers: [
            {
                label: "trim",
                value: "trim(' ')",
                tooltip: "Remove listed chars from beginning and end of the string"
            },
            {
                label: "ltrim",
                value: "ltrim(' ')",
                tooltip: "Remove listed chars from beginning of the string"
            },
            {
                label: "rtrim",
                value: "rtrim(' ')",
                tooltip: "Remove listed chars from end of the string"
            },
            {
                label: "htmlspecialchars",
                value: "htmlspecialchars()",
                tooltip: "Encode html by doing replace of HTML tag braces: > <"
            },
            {
                label: "strip_tags",
                value: "strip_tags('<ul><li>')",
                tooltip: "Remove all HTML tags, except listed"
            },
            {
                label: "substr",
                value: "substr(0, 50)",
                tooltip: "Take substring of longer string. 2 arguments: start char and end char"
            },
            {
                label: "ucfirst",
                value: "ucfirst()",
                tooltip: "Make first letter of string capitalized"
            },
            {
                label: "str_replace",
                value: "str_replace()",
                tooltip: "Find and replace"
            },
            {
                label: "floor",
                value: "floor()",
                tooltip: "Round fractions down"
            },
            {
                label: "ceil",
                value: "ceil()",
                tooltip: "Round fractions up"
            },
            {
                label: "round",
                value: "round()",
                tooltip: "Rounds a float"
            },
            {
                label: "nl2br",
                value: "nl2br()",
                tooltip: "Text line break into &lt;br&lt; tag"
            },
            {
                label: "removeLineBreaks",
                value: "removeLineBreaks()",
                tooltip: "Remove all line breaks from string"
            }
        ],
    };
});

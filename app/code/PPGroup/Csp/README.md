# PPGroup_Csp

## <a href="https://developer.adobe.com/commerce/php/development/security/content-security-policies/" target="_blank">Content security policies</a>

### Configure a module's CSP mode

Example config.xml:

````
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <csp>
            <mode>
                <storefront>
                    <report_only>0</report_only>
                </storefront>
                <admin>
                    <report_only>0</report_only>
                </admin>
            </mode>
        </csp>
    </default>
</config>
````

The following table describes each type of CSP:
Display Table in README.md file in Git

| POLICY NAME              | DESCRIPTION                                                              |
|--------------------------|--------------------------------------------------------------------------|
| `default-src`            | The default policy.                                                      |
| `base-uri`               | Defines which URLs can appear in a page's < base> element.               |
| `child-src`              | Defines the sources for workers and embedded frame contents.             |
| `font-src`               | Defines which sources can serve fonts.                                   |
| `form-action`            | Defines valid endpoints for submission from < form> tags.                |
| `frame-ancestors`        | Defines the sources that can embed the current page.                     |
| `frame-src`              | Defines the sources for elements such as < frame> and < iframe>.         |
| `img-src`                | Defines the sources from which images can be loaded.                     |
| `manifest-src`           | Defines the allowable contents of web app manifests.                     |
| `media-src`              | Defines the sources from which images can be loaded.                     |
| `object-src`             | Defines the sources for the < object>, < embed>, and < applet> elements. |
| `script-src`             | Defines the sources for JavaScript < script> elements.                   |
| `style-src`              | Defines the sources for stylesheets.                                     |

### Add a domain to the whitelist
You can add a domain to the whitelist for a policy (like script-src, style-src, font-src and others) by adding a csp_whitelist.xml to your custom module's etc folder.
````
<?xml version="1.0"?>
<csp_whitelist xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Csp:etc/csp_whitelist.xsd">
    <policies>
        <policy id="script-src">
            <values>
                <value id="devdocs-base" type="host">https://developer.adobe.com</value>
                <value id="magento" type="host">https://magento.com</value>
            </values>
        </policy>
        <policy id="connect-src">
            <values>
                <value id="devdocs" type="host">https://developer.adobe.com</value>
            </values>
        </policy>
    </policies>
</csp_whitelist>
````

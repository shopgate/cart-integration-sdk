# Shopgate Cart Integration SDK

[![GitHub license](http://dmlc.github.io/img/apache2.svg)](LICENSE.md)
[![Run CS fixer & deploy](https://github.com/shopgate/cart-integration-sdk/actions/workflows/check_and_deploy.yml/badge.svg)](https://github.com/shopgate/cart-integration-sdk/actions/workflows/check_and_deploy.yml)

The Shopgate Cart Integration SDK (formerly: Shopgate Library) is a compilation of classes to manage the communication between your shop system and Shopgate via the Shopgate Plugin API and the Shopgate Merchant API. The SDK provides methods for processing incoming and outgoing requests, configuration options and for handling errors. 

## Getting Started
#### Via Composer
```composer require shopgate/cart-integration-sdk```

#### Manually
Download and unzip the [latest releases](https://github.com/shopgate/cart-integration-sdk/releases/latest).

Include ```shopgate.php``` from the root folder of the package:

```require_once 'shopgate-cart-integration-sdk/shopgate.php';```

## Developing a Shopgate Integration
If you want to know more about how the Shopgate Cart Integration SDK works have a look into the [documentation](https://developer.shopgate.com/guides/commerce/cart-integration/sdk).

Wanna see all the code? Try the [example plugin](https://developer.shopgate.com/guides/commerce/cart-integration/example-plugin).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) file for more information.

## Contributing

See [CONTRIBUTING.md](docs/CONTRIBUTING.md) file for more information.

## About Shopgate

Shopgate is the leading mobile commerce platform.

Shopgate offers everything online retailers need to be successful in mobile. Our leading software-as-a-service (SaaS) enables online stores to easily create, maintain and optimize native apps and mobile websites for the iPhone, iPad, Android smartphones and tablets.

## License

The Shopgate Cart Integration SDK is available under the Apache License, Version 2.0.

See the [LICENSE.md](LICENSE.md) file for more information.

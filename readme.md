# Amazon FPS for Laravel 3.x

A package for working w/ Amazon Flexible Payments (also works for Amazon Simple Payments).  See the [API Documentation](http://docs.aws.amazon.com/AmazonFPS/latest/FPSAPIReference/AWSFPSAPIDetails.html) for available methods.

## Install

Normal bundle install.

### Configuration

Copy the sample config to ``application/config/amazonfps.php`` and input the necessary information.

### Dependencies

* [XML](https://github.com/swt83/laravel-xml) - A package for working w/ XML.

## Using the API

Use desired method name and pass necessary params.  See the [documentation](http://docs.aws.amazon.com/AmazonFPS/latest/FPSAPIReference/AWSFPSAPIDetails.html) for more information.

```php
$result = AmazonFPS::get_account_activity(array(

));
```

The method name will be converted to camelcase for you, to match the API, which gives you the freedom to write code properly -- without camelcase, which is an abomination.

## Making Buttons

Make Amazon Simple Payments buttons, passing the necessary parameters.  See the [documentation](http://docs.aws.amazon.com/AmazonSimplePay/latest/ASPAdvancedUserGuide/button-html-example.html) for more information.

```php
echo AmazonFPS::button(array(
    #'abandonUrl' => '',
    'amount' => 'USD 20',
    #'cobrandingStyle' => '',
    #'collectShippingAddress' => '',
    #'description' => 'Annual One-Time Subscription Fee',
    'immediateReturn' => 1,
    #'ipnUrl' => '',
    #'noOfPromotionTransactions' => '', // no idea what this is
    'processImmediate' => 1,
    #'promotionAmount' => '',
    #'recurringFrequency' => '1 month',
    #'recurringStartDate' => '',
    'referenceId' => md5(SignupForm::populate('email')), // whatever you want
    #'returnUrl' => '',
    #'subscriptionPeriod' => 'forever',
));
```

You can ignore the ``accessKey`` and ``amazonPaymentsAccountId`` parameters, as they will automatically be added via the config file.  Also, the security signature and related params will automatically be calculated and added.

## Notes

Working w/ Amazon FPS is not a pleasant experience.
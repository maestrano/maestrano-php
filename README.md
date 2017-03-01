<p align="center">
<img src="https://raw.github.com/maestrano/maestrano-php/master/maestrano.png" alt="Maestrano Logo">
<br/>
<br/>
</p>

Maestrano Cloud Integration is currently in closed beta. Want to know more? Send us an email to <developers@maestrano.com>.



- - -

1. [Getting Setup](#getting-setup)
2. [Getting Started](#getting-started)
  * [Installation](#installation)
  * [Configuration](#configuration)
  * [Metadata Endpoint](#metadata-endpoint)
3. [Single Sign-On Setup](#single-sign-on-setup)
  * [User Setup](#user-setup)
  * [Group Setup](#group-setup)
  * [Controller Setup](#controller-setup)
  * [Other Controllers](#other-controllers)
  * [Redirecting on logout](#redirecting-on-logout)
  * [Redirecting on error](#redirecting-on-error)
4. [Account Webhooks](#account-webhooks)
  * [Groups Controller](#groups-controller-service-cancellation)
  * [Group Users Controller](#group-users-controller-business-member-removal)
5. [API](#api)
  * [Payment API](#payment-api)
    * [Bill](#bill)
    * [Recurring Bill](#recurring-bill)
  * [Membership API](#membership-api)
    * [User](#user)
    * [Group](#group)
6. [Connec!™ Data Sharing](#connec-data-sharing)
  * [Making Requests](#making-requests)
  * [Webhook Notifications](#webhook-notifications)

- - -

## Getting Setup
Before integrating with us you will need an to create your app on the developer platform and link it to a marketplace. Maestrano Cloud Integration being still in closed beta you will need to contact us beforehand to gain production access.

We provide a Sandbox environment where you can freely launch your app to test your integration. The sandbox is great to test single sign-on and API integration (e.g: Connec! API). This Sandbox is available on the developer platform on your app technical page.

To get started just go to: https://developer.maestrano.com. You will find the developer platform documentation here: [Documentation](https://maestrano.atlassian.net/wiki/display/DEV/Integrate+your+app+on+partner%27s+marketplaces).

A **php demo application** is also available: https://github.com/maestrano/demoapp-php

Do not hesitate to shoot us an email at <developers@maestrano.com> if you have any question.

## Getting Started

### Installation

To install maestrano-php using Composer, add this dependency to your project's composer.json:
```
{
  "require": {
    "maestrano/maestrano-php": "1.0.0"
  }
}
```

Then install via:
```
composer install
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):
```php
require_once('vendor/autoload.php');
```

### Configuration

The [developer platform](https://dev-platform.maestrano.com) is the easiest way to configure Maestrano. The only actions needed from your part is to create your application and environments on the developer platform and to create a config file or use environment variable to configure the SDK. The SDK will then contact the developer platform and retrieve the marketplaces configuration for your app environment.

A `dev-platform.json` config file is loaded using:
```php
Maestrano::autoConfigure('/path/to/dev-platform.json');
```

The json file may look like this:
```php
{
  # ===> Developer Platform Configuration
  # This is the host and base path that should be used by your environment to connect to the developer platform API.
  "dev-platform": {
    "host": "https://developer.maestrano.com",
    "api_path": "/api/config/v1/"
  },
  # => Environment credentials
  # These are your environment credentials, you can get them by connecting on the developer platform, then go on your app, they will be display under the technical view on each environment.
  "environment": {
    "api_key": "<your environment key>",
    "api_secret": "<your environment secret>"
  }
}
```

You can also use environment variables to configure your app environment:
```
export MNO_DEVPL_HOST=<developer platform host>
export MNO_DEVPL_API_PATH=<developer platform host>
export MNO_DEVPL_ENV_KEY=<your environment key>
export MNO_DEVPL_ENV_SECRET=<your environment secret>
```

To use configure the Developer Platform using environment variables, omit the file argument:
```php
Maestrano::autoConfigure();
```

## Single Sign-On Setup

It will require you to write a controller for the init phase and consume phase of the single sign-on handshake. You will receive 3 informations when logging in a user: the user, his group and the merketplace he's coming from.

You might wonder why we need a 'group' on top of a user. Well Maestrano works with businesses and as such expects your service to be able to manage groups of users. A group represents 1) a billing entity 2) a collaboration group. During the first single sign-on handshake both a user and a group should be created. Additional users logging in via the same group should then be added to this existing group (see controller setup below)

For more information, please consult [Multi-Marketplace Ingration](https://maestrano.atlassian.net/wiki/display/DEV/Multi-Marketplace+Integration).

### User Setup
Let's assume that your user model is called 'User'. The best way to get started with SSO is to define a class method on this model called 'findOrCreateForMaestrano' accepting a Maestrano.Sso.User and aiming at either finding an existing maestrano user in your database or creating a new one. Your user model should also have a 'Provider' property and a 'Uid' property used to identify the source of the user - Maestrano, LinkedIn, AngelList etc..

### Group Setup
The group setup is similar to the user one. The mapping is a little easier though. Your model should also have the 'Provider' property and a 'Uid' properties. Also your group model could have a AddMember method and also a hasMember method (see controller below)

### Controller Setup
You will need two controller action init and consume. The init action will initiate the single sign-on request and redirect the user to Maestrano. The consume action will receive the single sign-on response, process it and match/create the user and the group.

The init action is all handled via Maestrano methods and should look like this:
```php
<?php
  require_once '../../../init.php';
  
  // Build SSO request - Make sure GET parameters gets passed to the constructor
  $marketplace = $_GET['marketplace'];
  $req = Maestrano_Saml_Request::with($marketplace)->new($_GET);
  
  // Redirect the user to Maestrano Identity Provider
  header('Location: ' . $req->getRedirectUrl());
```

Based on your application requirements the consume action might look like this:
```php
<?php
  session_start();

  // Build SSO Response using SAMLResponse parameter value sent via POST request
  $marketplace = $_GET['marketplace'];
  $resp = Maestrano_Saml_Response::with($marketplace)->new($_POST['SAMLResponse']);
  
  if ($resp->isValid()) {
    // Get the user as well as the user group
    $user = new Maestrano_Sso_User($resp);
    $group = new Maestrano_Sso_Group($resp);
    
    //-----------------------------------
    // No database model in this project. We just keep the
    // relevant details in session
    //-----------------------------------
    $_SESSION["loggedIn"] = true;
    $_SESSION["firstName"] = $user->getFirstName();
    $_SESSION["lastName"] = $user->getLastName();
    $_SESSION["marketplace"] = $_GET['marketplace'];
    
    // Important - toId() and toEmail() have different behaviour compared to
    // getId() and getEmail(). In you maestrano configuration file, if your sso > creation_mode 
    // is set to 'real' then toId() and toEmail() return the actual id and email of the user which
    // are only unique across users.
    // If you chose 'virtual' then toId() and toEmail() will return a virtual (or composite) attribute
    // which is truly unique across users and groups
    $_SESSION["id"] = $user->toId();
    $_SESSION["email"] = $user->toEmail();
    
    // Store group details
    $_SESSION["groupName"] = $group->getName();
    $_SESSION["groupId"] = $group->getId();
    
    // Once the user is created/identified, we store the maestrano
    // session. This session will be used for single logout
    $mnoSession = new Maestrano_Sso_Session($_SESSION, $user);
    $mnoSession->save();
    
    // Redirect the user to home page
    header('Location: /');
  } else {
    echo "Holy Banana! Saml Response does not seem to be valid";
  }
```

Note that for the consume action you should disable CSRF authenticity if your framework is using it by default. If CSRF authenticity is enabled then your app will complain on the fact that it is receiving a form without CSRF token.

### Other Controllers
If you want your users to benefit from single logout then you should define the following filter in a module and include it in all your controllers except the one handling single sign-on authentication.

```php
$mnoSession = new Maestrano_Sso_Session($_SESSION);

// Trigger SSO handshake if session not valid anymore
if (!$mnoSession->isValid()) {

  // Redirect to the init URL of current marketplace
  header('Location: ' . Maestrano::with($_SESSION['marketplace'])->sso()->getInitUrl());
  
}
```

The above piece of code makes at most one request every 3 minutes (standard session duration) to the Maestrano website to check whether the user is still logged in Maestrano. Therefore it should not impact your application from a performance point of view.

If you start seing session check requests on every page load it means something is going wrong at the http session level. In this case feel free to send us an email and we'll have a look with you.

### Redirecting on logout
When Maestrano users sign out of your application you can redirect them to the Maestrano logout page. You can get the url of this page by calling:

```php
// With a configuration preset
Maestrano::with($_SESSION['marketplace'])->sso()->getLogoutUrl()
```

### Redirecting on error
If any error happens during the SSO handshake, you can redirect users to the following URL:

```php
Maestrano::sso()->getUnauthorizedUrl()

// With a configuration preset
// Maestrano::with('my-config-preset')->sso()->getUnauthorizedUrl()
```

## Account Webhooks
Single sign on has been setup into your app and Maestrano users are now able to use your service. Great! Wait what happens when a business (group) decides to stop using your service? Also what happens when a user gets removed from a business? Well the controllers describes in this section are for Maestrano to be able to notify you of such events.

### Groups Controller (service cancellation)
Sad as it is a business might decide to stop using your service at some point. On Maestrano billing entities are represented by groups (used for collaboration & billing). So when a business decides to stop using your service we will issue a DELETE request to the webhook.account.groups_path endpoint (typically /maestrano/account/groups/:id).

Maestrano only uses this controller for service cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

### Group Users Controller (business member removal)
A business might decide at some point to revoke access to your services for one of its member. In such case we will issue a DELETE request to the webhook.account.group_users_path endpoint (typically /maestrano/account/groups/:group_id/users/:id).

Maestrano only uses this controller for user membership cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

## API
The maestrano package also provides bindings to its REST API allowing you to access, create, update or delete various entities under your account (e.g: billing).

### Payment API

#### Bill
A bill represents a single charge on a given group.

```php
Maestrano_Account_Bill
```

##### Attributes
All attributes are available via their getter/setter counterpart. E.g:
```php
// for priceCents field
$bill->getPriceCents();
$bill->setPriceCents(2000);
```

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the bill</td>
<tr>

<tr>
<td><b>groupId</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The id of the group you are charging</td>
<tr>

<tr>
<td><b>priceCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The amount in cents to charge to the customer</td>
<tr>

<tr>
<td><b>description</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>A description of the product billed as it should appear on customer invoice</td>
<tr>

<tr>
<td><b>createdAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the the bill was created</td>
<tr>

<tr>
<td><b>updatedAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the bill was last updated</td>
<tr>

<tr>
<td><b>status</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>Status of the bill. Either 'submitted', 'invoiced' or 'cancelled'.</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>AUD</td>
<td>The currency of the amount charged in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>units</b></td>
<td>read/write</td>
<td>Float</td>
<td>-</td>
<td>1.0</td>
<td>How many units are billed for the amount charged</td>
<tr>

<tr>
<td><b>periodStartedAt</b></td>
<td>read/write</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>If the bill relates to a specific period then specifies when the period started. Both period_started_at and period_ended_at need to be filled in order to appear on customer invoice.</td>
<tr>

<tr>
<td><b>periodEndedAt</b></td>
<td>read/write</td>
<td>Date</td>
<td>-</td>
<td>false</td>
<td>If the bill relates to a specific period then specifies when the period ended. Both period_started_at and period_ended_at need to be filled in order to appear on customer invoice.</td>
<tr>

<tr>
<td><b>thirdParty</b></td>
<td>read/write</td>
<td>Boolean</td>
<td>-</td>
<td>-</td>
<td>Whether this bill is related to a third party cost or not. External expenses engaged for customers - such as paying a  provider for sending SMS on behalf of customers - should be flagged as third party.</td>
<tr>

</table>

##### Actions

List all bills you have created and iterate through the list
```php
$bills = Maestrano_Account_Bill::with($SESSION['marketplace'])->all();
```

Access a single bill by id
```php
$bills = Maestrano_Account_Bill::with($SESSION['marketplace'])->retrieve("bill-f1d2s54");
```

Create a new bill
```php
$bill = Maestrano_Account_Bill::with($SESSION['marketplace'])->create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase"
));
```

Cancel a bill
```php
$bills = Maestrano_Account_Bill::with($SESSION['marketplace'])->retrieve("bill-f1d2s54");
$bill->cancel();
```

#### Recurring Bill
A recurring bill charges a given customer at a regular interval without you having to do anything.

```php
Maestrano_Account_RecurringBill
```

##### Attributes
All attributes are available via their getter/setter counterpart. E.g:
```php
// for priceCents field
$bill->getPriceCents();
$bill->setPriceCents(2000);
```

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the recurring bill</td>
<tr>

<tr>
<td><b>groupId</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The id of the group you are charging</td>
<tr>

<tr>
<td><b>priceCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The amount in cents to charge to the customer</td>
<tr>

<tr>
<td><b>description</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>A description of the product billed as it should appear on customer invoice</td>
<tr>

<tr>
<td><b>period</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>Month</td>
<td>The unit of measure for the billing cycle. Must be one of the following: 'Day', 'Week', 'SemiMonth', 'Month', 'Year'</td>
<tr>

<tr>
<td><b>frequency</b></td>
<td>read/write</td>
<td>Integer</td>
<td>-</td>
<td>1</td>
<td>The number of billing periods that make up one billing cycle. The combination of billing frequency and billing period must be less than or equal to one year. If the billing period is SemiMonth, the billing frequency must be 1.</td>
<tr>

<tr>
<td><b>cycles</b></td>
<td>read/write</td>
<td>Integer</td>
<td>-</td>
<td>nil</td>
<td>The number of cycles this bill should be active for. In other words it's the number of times this recurring bill should charge the customer.</td>
<tr>

<tr>
<td><b>startDate</b></td>
<td>read/write</td>
<td>DateTime</td>
<td>-</td>
<td>Now</td>
<td>The date when this recurring bill should start billing the customer</td>
<tr>

<tr>
<td><b>createdAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the the bill was created</td>
<tr>

<tr>
<td><b>updatedAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the recurring bill was last updated</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>AUD</td>
<td>The currency of the amount charged in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>status</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>Status of the recurring bill. Either 'submitted', 'active', 'expired' or 'cancelled'.</td>
<tr>

<tr>
<td><b>initialCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>-</b></td>
<td>0</td>
<td>Initial non-recurring payment amount - in cents - due immediately upon creating the recurring bill</td>
<tr>

</table>

##### Actions

List all recurring bills you have created:
```php
$recBills = Maestrano_Account_RecurringBill::with($SESSION['marketplace'])->all();
```

Access a single bill by id
```php
$recBills = Maestrano_Account_RecurringBill::with($SESSION['marketplace'])->retrieve("rbill-f1d2s54");
```

Create a new recurring bill
```php
$recBill = Maestrano_Account_RecurringBill::with($SESSION['marketplace'])->create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase",
  'period' => 'Month',
  'startDate' => (new DateTime('NOW'))
));
```

Cancel a bill
```php
$recBill = Maestrano_Account_RecurringBill::with($SESSION['marketplace'])->retrieve("bill-f1d2s54");
$recBill->cancel();
```

### Membership API

#### User
A user is a member of a group having access to your application. Users are currently readonly.

```php
Maestrano_Account_User
```

##### Attributes

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the user</td>
<tr>

<tr>
<td><b>first_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user first name</td>
<tr>

<tr>
<td><b>last_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user last name</td>
<tr>

<tr>
<td><b>email</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user real email address</td>
<tr>

<tr>
<td><b>company_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user company name as it was entered when they signed up. Nothing related to the user group name.</td>
<tr>

<tr>
<td><b>country</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The country of the user in <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2 format</a> (2 letter code). E.g: 'US' for USA, 'AU' for Australia.</td>
<tr>

<tr>
<td><b>created_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the user was created</td>
<tr>

<tr>
<td><b>updated_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the user was last updated</td>
<tr>

</table>

##### Actions

List all users having access to your application
```php
$users = Maestrano_Account_User::with($SESSION['marketplace'])->all();
```

Access a single user by id
```php
// With configuration preset
$user = Maestrano_Account_User::with($SESSION['marketplace'])->retrieve("usr-f1d2s54");
$user->getFirstName();
```

#### Group
A group represents a customer account and is composed of members (users) having access to your application. A group also represents a chargeable account (see Bill/RecurringBill). Typically you can remotely check if a group has entered a credit card on Maestrano.

Groups are currently readonly.

```php
Maestrano_Account_Group
```

##### Attributes

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the group</td>
<tr>

<tr>
<td><b>name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The group name</td>
<tr>

<tr>
<td><b>email</b></td>
<td>readonly</td>
<td>string</td>
<td><b>-</b></td>
<td>-</td>
<td>The principal email address for this group (admin email address)</td>
<tr>

<tr>
<td><b>has_credit_card</b></td>
<td>readonly</td>
<td>Boolean</td>
<td><b>-</b></td>
<td>-</td>
<td>Whether the group has entered a credit card on Maestrano or not</td>
<tr>

<tr>
<td><b>free_trial_end_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td><b>-</b></td>
<td>-</td>
<td>When the group free trial will be finishing on Maestrano. You may optionally consider this date for your own free trial (optional)</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The currency used by this Group in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>country</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The country of the group in <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2 format</a> (2 letter code). E.g: 'US' for USA, 'AU' for Australia.</td>
<tr>

<tr>
<td><b>city</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The city of the group</td>
<tr>

<tr>
<td><b>main_accounting</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>Main accounting package used by this group. Possible values: 'quickbooks', 'xero', 'myob'</td>
<tr>

<tr>
<td><b>timezone</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The group timezone in <a href="http://en.wikipedia.org/wiki/List_of_tz_database_time_zones">Olson format</a></td>
<tr>

<tr>
<td><b>created_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the group was created</td>
<tr>

<tr>
<td><b>updated_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the group was last updated</td>
<tr>

</table>

##### Actions

List all groups having access to your application
```php
$groups = Maestrano_Account_Group::with($SESSION['marketplace'])->all();
```

Access a single group by id
```php
$group = Maestrano_Account_Group::with($SESSION['marketplace'])->retrieve("usr-f1d2s54");
$group->getName();
```

## Connec!™ Data Sharing
Maestrano offers the capability to share actual business data between applications via its data sharing platform Connec!™.

The platform exposes a set of RESTful JSON APIs allowing your application to receive data generated by other applications and update data in other applications as well!

Connec!™ also offers the ability to create webhooks on your side to get automatically notified of changes happening in other systems.

Connec!™ enables seamless data sharing between the Maestrano applications as well as popular apps such as QuickBooks and Xero. One connector - tens of integrations!

### Making Requests

Connec!™ REST API documentation can be found here: http://maestrano.github.io/connec

The Maestrano API provides a built-in client - based on CURL - for connecting to Connec!™. Things like connection and authentication are automatically managed by the Connec!™ client.


```php
# Pass the customer group id as argument or use the default one specified in the json configuration
$client = Maestrano_Connec_Client::with($SESSION['marketplace'])->new("cld-f7f5g4")

# Retrieve all organizations (customers and suppliers) created in other applications
$resp = $client->get('/organizations')
$resp['body'] # returns the raw response "{\"organizations\":[ ... ]}"
$resp['code'] # returns the response code. E.g. "200"

# Create a new organization
$client->post('/organizations', array('organizations' => array('name' => "DoeCorp Inc.")) )

# Update an organization
$client->put('/organizations/e32303c1-5102-0132-661e-600308937d74', array('organizations' => array('is_customer_' => true)))

# Retrieve a report
$client->getReport('/profit_and_loss', array('from' => '2015-01-01', 'to' => '2015-01-01', 'period' => 'MONTHLY'))
```

### Webhook Notifications
If you have configured the Maestrano API to receive update notifications (see 'subscriptions' configuration at the top) from Connec!™ then you can expect to receive regular POST requests on the notification_path you have configured.

Notifications are JSON messages containing the list of entities that have recently changed in other systems. You will only receive notifications for entities you have subscribed to.

Example of notification message:
```ruby
{
  "organizations": [
    { "id": "e32303c1-5102-0132-661e-600308937d74", name: "DoeCorp Inc.", ... }
  ],
  "people": [
    { "id": "a34303d1-4142-0152-362e-610408337d74", first_name: "John", last_name: "Doe", ... }
  ]
}
```

Entities sent via notifications follow the same data structure as the one described in our REST API documentation (available at http://maestrano.github.io/connec)

## Support
This README is still in the process of being written and improved. As such it might not cover some of the questions you might have.

So if you have any question or need help integrating with us just let us know at support@maestrano.com

## License

MIT License. Copyright 2014 Maestrano Pty Ltd. https://maestrano.com

You are not granted rights or licenses to the trademarks of Maestrano.

<p align="center">
<img src="https://raw.github.com/maestrano/maestrano-php/master/maestrano.png" alt="Maestrano Logo">
<br/>
<br/>
</p>

Maestrano Cloud Integration is currently in closed beta. Want to know more? Send us an email to <contact@maestrano.com>.
  
  
  
- - -

1.  [Getting Setup](#getting-setup)
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
  * [Bill](#bill)
  * [Recurring Bill](#recurring-bill)

- - -

## Getting Setup
Before integrating with us you will need an App ID and API Key. Maestrano Cloud Integration being still in closed beta you will need to contact us beforehand to gain production access.

For testing purpose we provide an API Sandbox where you can freely obtain an App ID and API Key. The sandbox is great to test single sign-on and API integration (e.g: billing API).

To get started just go to: http://api-sandbox.maestrano.io

A **php demo application** is also available: https://github.com/maestrano/demoapp-php

## Getting Started

### Installation

To install maestrano-php using Composer, add this dependency to your project's composer.json:
```
{
  "require": {
    "maestrano/maestrano-php": ">=0.4"
  }
}
```

Then install via:
```
composer install
```

To use the bindings, either user Composer's autoload[https://getcomposer.org/doc/00-intro.md#autoloading]:
```php
require_once('vendor/autoload.php');
```

Or manually:
```php
require_once('/path/to/vendor/maestrano/maestrano-php/lib/Maestrano.php');
```

### Configuration
#### Via config file

You can configure maestrano via json using configuration file like "maestrano.json" that you load using:
```php
Maestrano::configure('/path/to/maestrano.json')
```

The json file may look like this:
```ruby
{
  # ===> App Configuration
  #
  # => environment
  # The environment to connect to. If set to 'production' then all Single Sign-On (SSO) and API requests will be made to maestrano.com. If set to 'test' then requests will be made to api-sandbox.maestrano.io. 
  # The api-sandbox allows you to easily test integration scenarios.
  app.environment=test
  "environment": "test",
  
  # => host
  # This is your application host (e.g: my-app.com) which is ultimately used to redirect users to the right SAML url during SSO handshake.
  "app": {
    "host": "http://localhost:8888"
  },
  
  # ===> Api Configuration
  #
  # => id and key
  # Your application App ID and API key which you can retrieve on http://maestrano.com via your cloud partner dashboard. 
  # For testing you can retrieve/generate an api.id and api.key from the API Sandbox directly on http://api-sandbox.maestrano.io
  "api": {
    "id": "prod_or_sandbox_app_id",
    "key": "prod_or_sandbox_api_key"
  },
  
  # ===> SSO Configuration
  #
  "sso": {
    
    # => enabled
    # Enable/Disable single sign-on. When troubleshooting authentication issues you might want to disable SSO temporarily
    "enabled": "true",
    
    # => sloEnabled
    # Enable/Disable single logout. When troubleshooting authentication issues you might want to disable SLO temporarily. 
    # If set to false then MnoSession#isValid - which should be used in a controller action filter to check user session - always return true
    "sloEnabled": "true",
    
    # => idm
    # By default we consider that the domain managing user identification is the same as your application host (see above config.app.host parameter). 
    # If you have a dedicated domain managing user identification and therefore responsible for the single sign-on handshake (e.g: https://idp.my-app.com) then you can specify it below
    "idm": "https://idp.myapp.com",
    
    # => initPath
    # This is your application path to the SAML endpoint that allows users to initialize SSO authentication. 
    # Upon reaching this endpoint users your application will automatically create a SAML request and redirect the user to Maestrano. Maestrano will then authenticate and authorize the user. 
    # Upon authorization the user gets redirected to your application consumer endpoint (see below) for initial setup and/or login.
    "initPath": "/maestrano/auth/saml/init.php"
    
    # => consumePath
    #This is your application path to the SAML endpoint that allows users to finalize SSO authentication. 
    # During the 'consume' action your application sets users (and associated group) up and/or log them in.
    "consumePath": "/maestrano/auth/saml/consume.php"
    
    # => creationMode
    # !IMPORTANT
    # On Maestrano users can take several "instances" of your service. You can consider
    # each "instance" as 1) a billing entity and 2) a collaboration group (this is
    # equivalent to a 'customer account' in a commercial world). When users login to
    # your application via single sign-on they actually login via a specific group which
    # is then supposed to determine which data they have access to inside your application.
    # 
    # E.g: John and Jack are part of group 1. They should see the same data when they login to
    # your application (employee info, analytics, sales etc..). John is also part of group 2 
    # but not Jack. Therefore only John should be able to see the data belonging to group 2.
    # 
    # In most application this is done via collaboration/sharing/permission groups which is
    # why a group is required to be created when a new user logs in via a new group (and 
    # also for billing purpose - you charge a group, not a user directly). 
    # 
    # - mode: 'real'
    # In an ideal world a user should be able to belong to several groups in your application.
    # In this case you would set the 'sso.creation_mode' to 'real' which means that the uid
    # and email we pass to you are the actual user email and maestrano universal id.
    # 
    # - mode: 'virtual'
    # Now let's say that due to technical constraints your application cannot authorize a user
    # to belong to several groups. Well next time John logs in via a different group there will
    # be a problem: the user already exists (based on uid or email) and cannot be assigned 
    # to a second group. To fix this you can set the 'sso.creation_mode' to 'virtual'. In this
    # mode users get assigned a truly unique uid and email across groups. So next time John logs
    # in a whole new user account can be created for him without any validation problem. In this
    # mode the email we assign to him looks like "usr-sdf54.cld-45aa2@mail.maestrano.com". But don't
    # worry we take care of forwarding any email you would send to this address
    "creationMode": "virtual",
  },
    
  # ===> Account Webhooks
  # Single sign on has been setup into your app and Maestrano users are now able
  # to use your service. Great! Wait what happens when a business (group) decides to 
  # stop using your service? Also what happens when a user gets removed from a business?
  # Well the endpoints below are for Maestrano to be able to notify you of such
  # events.
  #
  # Even if the routes look restful we issue only issue DELETE requests for the moment
  # to notify you of any service cancellation (group deletion) or any user being
  # removed from a group.
  "webhook": {
    "account": {
      "groupsPath": "/maestrano/account/groups/:id",
      "groupUsersPath": "/maestrano/account/groups/:group_id/users/:id"
    }
  }
}

```

#### At runtime

You can configure maestrano using an associative array if you prefer. The structure is the same as for the json above:

```php
Maestrano::configure(array(
  'environment' => 'production', 
  'sso' => array(
    'creation_mode' => 'real'
  )
));
```

### Metadata Endpoint
Your configuration initializer is now all setup and shiny. Great! But need to know about it. Of course
we could propose a long and boring form on maestrano.com for you to fill all these details (especially the webhooks) but we thought it would be more convenient to fetch that automatically.

For that we expect you to create a metadata endpoint that we can fetch regularly (or when you press 'refresh metadata' in your maestrano cloud partner dashboard). By default we assume that it will be located at
YOUR_WEBSITE/maestrano/metadata(.json or .php)

Of course if you prefer a different url you can always change that endpoint in your maestrano cloud partner dashboard.

What would the controller action look like? First let's talk about authentication. You don't want that endpoint to be visible to anyone. Maestrano always uses http basic authentication to contact your service remotely. The login/password used for this authentication are your actual api.id and api.key.

So here is an example of page to adapt depending on the framework you're using:

```php
header('Content-Type: application/json');

// Authenticate using http basic
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  echo Maestrano::toMetadata();
} else {
  echo "Sorry! I'm not giving you my API metadata";
}
```

## Single Sign-On Setup
In order to get setup with single sign-on you will need a user model and a group model. It will also require you to write a controller for the init phase and consume phase of the single sign-on handshake.

You might wonder why we need a 'group' on top of a user. Well Maestrano works with businesses and as such expects your service to be able to manage groups of users. A group represents 1) a billing entity 2) a collaboration group. During the first single sign-on handshake both a user and a group should be created. Additional users logging in via the same group should then be added to this existing group (see controller setup below)

### User Setup
Let's assume that your user model is called 'User'. The best way to get started with SSO is to define a class method on this model called 'findOrCreateForMaestrano' accepting a Maestrano.Sso.User and aiming at either finding an existing maestrano user in your database or creating a new one. Your user model should also have a 'Provider' property and a 'Uid' property used to identify the source of the user - Maestrano, LinkedIn, AngelList etc..

### Group Setup
The group setup is similar to the user one. The mapping is a little easier though. Your model should also have the 'Provider' property and a 'Uid' properties. Also your group model could have a AddMember method and also a hasMember method (see controller below)

### Controller Setup
You will need two controller action init and consume. The init action will initiate the single sign-on request and redirect the user to Maestrano. The consume action will receive the single sign-on response, process it and match/create the user and the group.

The init action is all handled via Maestrano methods and should look like this:
```php
// Build SSO request - Make sure GET parameters gets passed
// to the constructor
$req = new Maestrano_Saml_Request($_GET);

// Redirect the user to Maestrano Identity Provider
header('Location: ' . $req->getRedirectUrl());
%>
```

Based on your application requirements the consume action might look like this:
```jsp
session_start();

// Build SSO Response using SAMLResponse parameter value sent via
// POST request
$resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);

if ($resp->isValid()) {
  
  // Get the user as well as the user group
  $user = new Maestrano_Sso_User($resp);
  $group = new Maestrano_Sso_Group($resp);
  
  //-----------------------------------
	// For the sake of simplicity we store everything in session. This
  // step should actually involve link the Maestrano user/group to actual
  // models in your application
  //-----------------------------------
  $_SESSION["loggedIn"] = true;
  $_SESSION["firstName"] = $user->getFirstName();
  $_SESSION["lastName"] = $user->getLastName();
  
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
  
  
  // Set Maestrano Session (used for single logout - see below)
  $mnoSession = new Maestrano_Sso_Session($_SESSION,$user);
  $mnoSession->save();
  
  // Redirect the user to home page
  header('Location: /');
  
} else {
  echo "Holy Banana! Saml Response does not seem to be valid";
}
%>
```

Note that for the consume action you should disable CSRF authenticity if your framework is using it by default. If CSRF authenticity is enabled then your app will complain on the fact that it is receiving a form without CSRF token.

### Other Controllers
If you want your users to benefit from single logout then you should define the following filter in a module and include it in all your controllers except the one handling single sign-on authentication.

```php
$mnoSession = new Maestrano_Sso_Session(request.getSession());

// Trigger SSO handshake if session not valid anymore
if (!$mnoSession->isValid()) {
  header('Location: ' . Maestrano::sso()->getInitUrl());
}
```

The above piece of code makes at most one request every 3 minutes (standard session duration) to the Maestrano website to check whether the user is still logged in Maestrano. Therefore it should not impact your application from a performance point of view.

If you start seing session check requests on every page load it means something is going wrong at the http session level. In this case feel free to send us an email and we'll have a look with you.

### Redirecting on logout
When Maestrano users sign out of your application you can redirect them to the Maestrano logout page. You can get the url of this page by calling:

```php
Maestrano::sso()->getLogoutUrl()
```

### Redirecting on error
If any error happens during the SSO handshake, you can redirect users to the following URL:

```php
Maestrano::sso()->getUnauthorizedUrl()
```

## Account Webhooks
Single sign on has been setup into your app and Maestrano users are now able to use your service. Great! Wait what happens when a business (group) decides to stop using your service? Also what happens when a user gets removed from a business? Well the controllers describes in this section are for Maestrano to be able to notify you of such events.

### Groups Controller (service cancellation)
Sad as it is a business might decide to stop using your service at some point. On Maestrano billing entities are represented by groups (used for collaboration & billing). So when a business decides to stop using your service we will issue a DELETE request to the webhook.account.groups_path endpoint (typically /maestrano/account/groups/:id).

Maestrano only uses this controller for service cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

The controller example below reimplements the authenticate_maestrano! method seen in the [metadata section](#metadata) for completeness. Utimately you should move this method to a helper if you can.

The example below needs to be adapted depending on your application:

```php
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  $someGroup = MyGroupModel::findByMnoId(restfulIdFromUrl);
  $someGroup.disableAccess();
}
```

### Group Users Controller (business member removal)
A business might decide at some point to revoke access to your services for one of its member. In such case we will issue a DELETE request to the webhook.account.group_users_path endpoint (typically /maestrano/account/groups/:group_id/users/:id).

Maestrano only uses this controller for user membership cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

The controller example below reimplements the authenticate_maestrano! method seen in the [metadata section](#metadata) for completeness. Utimately you should move this method to a helper if you can.

The example below needs to be adapted depending on your application:

```php
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  $someGroup = MyGroupModel::findByMnoId(restfulGroupIdFromUrl);
  $someGroup->removeUserById(restfulIdFromUrl);
}
```

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
// for groupId field
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
<td>-</td>
<td>If the bill relates to a specific period then specifies when the period ended. Both period_started_at and period_ended_at need to be filled in order to appear on customer invoice.</td>
<tr>

</table>

##### Actions

List all bills you have created and iterate through the list
```php
$bills = Maestrano_Account_Bill::all();
```

Access a single bill by id
```php
$bill = Maestrano_Account_Bill::retrieve("bill-f1d2s54");
```

Create a new bill
```php
$bill = Maestrano_Account_Bill::create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase"
));
```

Cancel a bill
```php
$bill = Maestrano_Account_Bill::retrieve("bill-f1d2s54");
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
// for groupId field
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
$recBills = Maestrano_Account_RecurringBill::all();
```

Access a single bill by id
```php
$recBill = Maestrano_Account_RecurringBill::retrieve("rbill-f1d2s54");
```

Create a new recurring bill
```php
$recBill = Maestrano_Account_RecurringBill::create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase",
  'period' => 'Month',
  'startDate' => (new DateTime('NOW'))
));
```

Cancel a bill
```php
$recBill = Maestrano_Account_RecurringBill::retrieve("bill-f1d2s54");
$recBill->cancel();
```

## Support
This README is still in the process of being written and improved. As such it might not cover some of the questions you might have.

So if you have any question or need help integrating with us just let us know at support@maestrano.com

## License

MIT License. Copyright 2014 Maestrano Pty Ltd. https://maestrano.com

You are not granted rights or licenses to the trademarks of Maestrano.


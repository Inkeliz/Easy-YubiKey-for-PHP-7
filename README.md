# Easy YubiKey for PHP 7

<p align="center">
<img src="http://i.imgur.com/BMQY78Z.png" width="320">
</p>

Start
-----

To include:

```php
include('src/yubikey.php');
```

Basic
-----

Configure and start the Yubikey:

```php
$yubikey = newYubikey('CLIENT_ID', 'SECRET_ID', $_POST['YUBIKEY']);
```

Check with the Yubikey from input is valid:

```php
$yubikey_valid = checkYubikey($yubikey);
```

Get the Yubikey id (needed to store it in database):

```php
$yubikey_id = getYubikey($yubikey);
```

Compare the id of input and the database (needed when the user login to verify the user):

```php
$yubikey_id = compareYubikey($yubikey, $database_response['yubikey_id']);
```

Examples
-----

Adding a Yubikey into account:

```php
$yubikey = newYubikey('CLIENT_ID', 'SECRET_ID', $_POST['YUBIKEY']);

if( checkYubikey($yubikey) ){
  
  $yubikey_id = getYubikey($yubikey);
  
  // Store the `yubikey_id` in database...
  $query = mysqli_query($con, 'UPDATE user_table SET yubikey_id = ? WHERE id = 1');
  mysqli_stmt_bind_param($query, 's', $yubikey_id);
  mysqli_stmt_execute($query);
  // 
  
  echo 'Success';
  
}else{

  echo 'This is not valid Yubikey';
  
}

```

When the user login you need to check with the `Yubikey` match with the `yubikey_id` stored and if the Yubikey is valid:

```php
$yubikey = newYubikey('CLIENT_ID', 'SECRET_ID', $_POST['YUBIKEY']);

// Get the `yubikey_id` from database...
$query = mysqli_query($con, 'SELECT yubikey_id FROM user_table WHERE id = 1');
list($yubikey_id) = mysqli_fetch_row($query);
//

if(compareYubikey($yubikey, $yubikey_id) && checkYubikey($yubikey) ){
  
  echo 'Success';
  
}else{

  echo 'This is not valid Yubikey';
  
}

```



Details
-----


###`$ykey` = newYubikey(`$client_id`, `$secret_key`, `$otp`)###

| Parameter      | Description                                          |   
| ----------- | ------------------------------------------------------- | 
| `$client_id` | This is the "Client ID" from your app (get it on https://upgrade.yubico.com/getapikey/)
| `$secret_key`  | This is the "Secret key" from your app (get it on https://upgrade.yubico.com/getapikey/) 
| `$otp`   | This is the OTP, the string when the end user press the button of Yubikey.                     

###checkYubikey(`$ykey`, `$protocol`, `$hosts`)###

####Usage#####

| Parameter      | Description                                            
| ----------- | ------------------------------------------------------- |
| `$ykey` | Array from newYubikey() |
| `$protocol` | Set the server protocol (default: `https://`) |
| `$hosts` | Set the server hosts (default: `api.yubico.com/wsapi/2.0/verify`) |

####Response#####

| Value      | Description                                            
| ----------- | ------------------------------------------------------- |
| `true` | The Yubikey is valid |
| `false` | The Yubikey is not valid |

###getYubikey(`$ykey`)###

####Usage#####

| Parameter      | Description                                            
| ----------- | ------------------------------------------------------- |
| `$ykey` | Array from newYubikey() |

####Response#####

| Value      | Description                                            
| ----------- | ------------------------------------------------------- |
| `id` | The id of the Yubikey |


###compareYubikey(`$ykey`, `$idCompare`)###

####Usage#####

| Parameter      | Description                                            
| ----------- | ------------------------------------------------------- |
| `$ykey` | Array from newYubikey() |
| `$idCompare` | The string stored in database (to check with the Yubikey have the same id) |

####Response#####

| Value      | Description                                            
| ----------- | ------------------------------------------------------- |
| `true` | The id is the same |
| `false` | The id is not the same |






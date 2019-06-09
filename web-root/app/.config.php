<?php
return array (
  'profile' => 
  array (
    'oj-name' => 'Horse OJ',
    'oj-name-short' => 'HOJ',
    'ICP-license' => ''
  ),
  'database' => 
  array (
    'database' => 'app_uoj233',
    'username' => 'root',
    'password' => '',
    'host' => '127.0.0.1'
  ),
  'web' => 
  array (
    'domain' => NULL,
    'main' => 
    array (
      'protocol' => 'http',
      'host' => isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
      'port' => 80,
    ),
    'blog' => 
    array (
      'protocol' => 'http',
      'host' => isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
      'port' => 80,
    ),
  ),
  'security' => 
  array (
    'user' => 
    array (
      'client_salt' => 'JImSNiHMwcWN77iLRcgsfSHXM7wQotAC',
    ),
    'cookie' => 
    array (
      'checksum_salt' => 
      array (
        0 => 'WKDtDfXBfX4wb5Ic',
        1 => 'ZtCDAXQ1SXpxMRcc',
        2 => 'VzrbhNobiNGP6sKD',
      ),
    ),
  ),
  'switch' => 
  array (
    'blog-use-subdomain' => false
  )
);
<?php
namespace Fatturatutto\Security;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * Test Security Address
 *
 * @author Matteo
 *        
 */
class Security {
    //http control status code 
    const FORBIDDEN = 403;
    const UNAUTHORIZED = 401;
    const MOVED = 301;
    
//     $urls = [$FORBIDDEN => array(
//         "www.fatturatutto.it/app/logs",
//         "app.fatturatutto.it/logs",
//         "www.fatturatutto.it/app/vendor",
//         "app.fatturatutto.it/vendor"
//     ),
//         [$UNAUTHORIZED > array(
//             "www.dataslang.com/wp-login.php"
//         )
//         ];

// foreach ($urls as $error_code => $url) {
//     $status_code = null;
//     while ($status_code == null || $status_code == $MOVED) {
//                 $response = $client->...;
//                 $status-code = $response->get.....;
//         if ($status_code == $MOVED) {
//                     $url = $response->get.....;
//         }
//     }
    
//     assertEqual($error_code, $status_code);
// }
}

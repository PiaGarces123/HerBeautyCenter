<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Devuelve de forma segura los datos enviados tanto por POST (FormData / x-www-form-urlencoded)
     * como por JSON, sin arrojar excepciones de parseo cuando la petición es multipart/form-data.
     */
    protected function getRequestData(): array
    {
        $postData = $this->request->getPost();
        $data = is_array($postData) ? $postData : [];

        $cType = $this->request->getHeaderLine('Content-Type');
        if (stripos($cType, 'application/json') !== false) {
            $body = $this->request->getBody();
            if (!empty($body)) {
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
        }

        return $data;
    }
}

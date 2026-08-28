<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * Collects non-empty filter values from the request query string.
     *
     * @param list<string> $allowed Allowed filter field names.
     *
     * @return array<string, string> The non-empty filter values.
     */
    protected function collectFilters(array $allowed): array
    {
        $filters = [];
        foreach ($allowed as $key) {
            $value = $this->request->getGet($key);
            if ($value !== null && $value !== '') {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * Builds a SQL WHERE-string from allowed filter fields.
     *
     * @param list<string>          $allowed Allowed filter field names.
     * @param array<string, string> $filters Non-empty filter values.
     *
     * @return string SQL WHERE fragment (empty when no filters are present).
     */
    protected function buildFilterSql(array $allowed, array $filters): string
    {
        $parts = [];
        foreach ($allowed as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $val = str_replace("'", "\'", $filters[$key]);
                $parts[] = "{$key}='{$val}'";
            }
        }

        return implode(' AND ', $parts);
    }

    /**
     * Resolves the requested page size from the query string.
     *
     * @return int One of 10, 20, 50, 100 (defaults to 20).
     */
    protected function resolvePerPage(): int
    {
        $allowed = [10, 20, 50, 100];
        $val = (int) $this->request->getGet('per_page');

        return in_array($val, $allowed, true) ? $val : 20;
    }

    /**
     * Parses a count endpoint response into a total, or null on failure.
     *
     * @return int|null
     */
    protected function parseCount(array $response)
    {
        if (($response['error_code'] ?? -1) === 0 && isset($response['data'])) {
            return (int) $response['data'];
        }

        return null;
    }

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }
}

<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Octopy\DataTable;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Response as InertiaResponse;
use RuntimeException;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\DataTables;

/**
 * Class DataTable
 *
 * @package OctopyID\DataTable
 */
abstract class DataTable
{
    /**
     * @var bool
     */
    protected bool $debug = false;

    /**
     * @var string
     */
    protected string $compiler = 'blade';

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * DataTable constructor.
     * @param  Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param  string        $view
     * @param  array|Closure $data
     * @return mixed
     * @throws DataTableException
     */
    public function render(string $view, $data = [])
    {
        if (! $this->isDebugActive() && ! $this->request->ajax()) {
            if ($this->compiler === 'blade') {
                return view($view, $this->data($data));
            } else if ($this->compiler === 'inertia' || $this->compiler === 'vue') {
                return $this->inertia($view, $data);
            }

            throw new RuntimeException('We currently only support Blade and Vue/Inertia.');
        }

        if ($this->request->ajax() && $this->request->hasHeader('X-Inertia')) {
            return $this->inertia($view, $data);
        }

        try {
            return $this->json();
        } catch (Exception $exception) {
            throw new DataTableException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @return mixed
     * @throws Exception
     * @noinspection PhpUndefinedMethodInspection
     */
    public function json()
    {
        $source = App::call([$this, 'query']);

        if (is_string($source)) {
            return $source;
        }

        $datatable = DataTables::make($source);

        if (method_exists($this, 'option')) {
            $this->option($datatable);
        }

        return $datatable->make(true);
    }

    /**
     * @param  Request $request
     * @return mixed
     */
    abstract public function query(Request $request);

    /**
     * @param  DataTableAbstract $table
     * @return void
     */
    abstract public function option(DataTableAbstract $table) : void;

    /**
     * @return bool
     */
    protected function isDebugActive() : bool
    {
        return $this->debug && $this->request->has('debug') && (
                $this->request->debug === 'true' || (int) $this->request->debug === 1
            );
    }

    /**
     * @param  array|Closure $data
     * @return array
     */
    protected function data($data) : array
    {
        if ($data instanceof Closure) {
            $data = $data($this->request);
        }

        return $data;
    }

    /**
     * @param  string|Response $view
     * @param  array           $data
     * @return InertiaResponse
     */
    private function inertia($view, array $data) : InertiaResponse
    {
        if (is_string($view)) {
            if (! function_exists('inertia')) {
                throw new RuntimeException('Please make sure Inertia libraries are installed.');
            }

            return inertia($view, $this->data($data));
        }

        return $view;
    }
}

<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Octopy\DataTable;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Response as InertiaResponse;
use Octopy\DataTable\Exceptions\DataTableException;
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
            return view($view, $this->data($data));
        }

        try {
            return $this->json();
        } catch (Exception $exception) {
            throw new DataTableException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param  string|Response $view
     * @param  array           $data
     * @return InertiaResponse
     */
    public function inertia($view, array $data) : InertiaResponse
    {
        return inertia($view, $this->data($data));
    }

    /**
     * @return mixed
     */
    abstract protected function query();

    /**
     * @param  DataTableAbstract $table
     * @return void
     */
    abstract protected function option(DataTableAbstract $table) : void;

    /**
     * @return mixed
     * @throws Exception
     * @noinspection PhpUndefinedMethodInspection
     */
    private function json()
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
     * @return bool
     */
    private function isDebugActive() : bool
    {
        return config('app.debug', false);
    }

    /**
     * @param  array|Closure $data
     * @return array
     */
    private function data($data) : array
    {
        if ($data instanceof Closure) {
            $data = $data($this->request);
        }

        return $data;
    }
}

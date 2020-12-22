<?php

namespace Octopy\DataTable;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\App;
use App\Exceptions\DataTableException;
use Yajra\DataTables\DataTableAbstract;

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
     * @param  string $view
     * @param  array  $data
     * @return mixed
     * @throws DataTableException
     */
    public function render(string $view, $data = [])
    {
        if (! $this->isDebugActive() && ! $this->request->ajax()) {
            return view($view, $this->data($data));
        }

        try {
            $source = App::call([$this, 'query']);

            if (is_string($source)) {
                return $source;
            }

            $datatable = DataTables::make($source);

            if (method_exists($this, 'option')) {
                $this->option($datatable);
            }

            return $datatable->make(true);
        } catch (Exception $exception) {
            throw new DataTableException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param  array $data
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
     * @return bool
     */
    protected function isDebugActive()
    {
        return $this->request->has('debug') && ($this->request->debug === 'true' || $this->request->debug === 1);
    }

    /**
     * @param  DataTableAbstract $table
     * @return void
     */
    abstract public function option(DataTableAbstract $table) : void;
}

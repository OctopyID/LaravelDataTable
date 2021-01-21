<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Octopy\DataTable;

use Closure;
use Exception;
use RuntimeException;
use Inertia\Response;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\App;
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
		
		if ($this->request->ajax() && $this->request->hasHeader('X-Inertia')) {
			return $this->inertia($view, $data);
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
	 * @return bool
	 */
	protected function isDebugActive() : bool
	{
		return $this->request->has('debug') && ($this->request->debug === 'true' || $this->request->debug === 1);
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
	 * @param  string|Response $view
	 * @param  array           $data
	 * @return Response
	 */
	private function inertia($view, array $data) : Response
	{
		if (is_string($view)) {
			
			if (! function_exists('inertia')) {
				throw new RuntimeException('Please make sure Inertia libraries are installed.');
			}
			
			return inertia($view, $this->data($data));
		}
		
		return $view;
	}
	
	/**
	 * @param  DataTableAbstract $table
	 * @return void
	 */
	abstract public function option(DataTableAbstract $table) : void;
}

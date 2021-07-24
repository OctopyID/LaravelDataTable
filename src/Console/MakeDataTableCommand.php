<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Octopy\DataTable\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Class MakeDataTableCommand
 * @package Octopy\DataTable\Console
 */
class MakeDataTableCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:datatable';

    /**
     * @var string
     */
    protected $signature = 'make:datatable {name : The name of the class}
                            {--m|model= : The model that the datatable applies}
                            {--f|force : Create the class even if the datatable already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new DataTable class';

    /**
     * @var string
     */
    protected $type = 'DataTable';

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return;
        }

        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        $this->makeDirectory($path);

        $stub = $this->buildClass($name);

        if ($this->hasOption('model') && $this->option('model')) {
            $stub = $this->replaceStub($stub);
        }

        $this->files->put($path, $this->sortImports($stub));

        $this->info($this->type . ' created successfully.');
    }

    /**
     * @param  string $stub
     * @return string
     */
    protected function replaceStub(string $stub) : string
    {
        $replace = [
            '{{ model }}'           => $this->option('model'),
            '{{ namespacedModel }}' => $this->qualifyModel($this->option('model')),
        ];

        return str_replace(array_keys($replace), array_values($replace), $stub);
    }

    /**
     * @return string
     */
    protected function getStub() : string
    {
        return $this->option('model') ? $this->resolveStub('datatable.stub') : $this->resolveStub('datatable.plain.stub');
    }

    /**
     * @param  string $stub
     * @return string
     */
    protected function resolveStub(string $stub) : string
    {
        return __DIR__ . '/../../stubs/' . $stub;
    }

    /**
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace) : string
    {
        return $rootNamespace . '\\DataTables';
    }
}
